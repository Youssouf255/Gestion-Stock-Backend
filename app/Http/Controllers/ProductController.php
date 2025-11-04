<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        // Apply filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('product_id', 'like', "%{$search}%");
            });
        }

        if ($request->has('availability')) {
            $availability = $request->availability;
            if ($availability === 'low-stock') {
                $query->whereRaw('remaining_stock <= threshold_value');
            } elseif ($availability === 'out-of-stock') {
                $query->where('remaining_stock', '<=', 0);
            } elseif ($availability === 'in-stock') {
                $query->whereRaw('remaining_stock > threshold_value');
            }
        }

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'nullable|string|unique:products',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string',
            'threshold_value' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable|image|max:2048',
            'opening_stock' => 'nullable|integer|min:0',
            'remaining_stock' => 'nullable|integer|min:0',
        ]);

        // Générer product_id automatiquement s'il n'est pas fourni
        if (empty($validated['product_id'])) {
            $lastProduct = Product::orderBy('id', 'desc')->first();
            if ($lastProduct && $lastProduct->product_id) {
                // Extraire le numéro du dernier product_id
                preg_match('/PROD-(\d+)/', $lastProduct->product_id, $matches);
                $nextNumber = isset($matches[1]) ? ((int)$matches[1]) + 1 : 1;
            } else {
                $nextNumber = 1;
            }
            $validated['product_id'] = 'PROD-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }

        // Calculer selling_price s'il n'est pas fourni (20% de marge)
        if (empty($validated['selling_price'])) {
            $validated['selling_price'] = $validated['buying_price'] * 1.2;
        }

        // Définir opening_stock et remaining_stock si non fournis
        if (empty($validated['opening_stock'])) {
            $validated['opening_stock'] = $validated['quantity'];
        }
        if (empty($validated['remaining_stock'])) {
            $validated['remaining_stock'] = $validated['quantity'];
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);
        $product->load(['category', 'supplier']);

        return response()->json($product, 201);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'supplier'])->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'product_id' => 'sometimes|string|unique:products,product_id,' . $id,
            'category_id' => 'sometimes|exists:categories,id',
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'buying_price' => 'sometimes|numeric|min:0',
            'selling_price' => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'unit' => 'sometimes|string',
            'threshold_value' => 'sometimes|integer|min:0',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);
        $product->load(['category', 'supplier']);

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function lowStock()
    {
        $products = Product::with(['category', 'supplier'])
            ->whereRaw('remaining_stock <= threshold_value')
            ->get();

        return response()->json($products);
    }
}








