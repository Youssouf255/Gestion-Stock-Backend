<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductManagementController extends Controller
{
    /**
     * Get all products with advanced filters
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('product_id', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by stock status
        if ($request->has('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->whereRaw('remaining_stock <= threshold_value');
                    break;
                case 'out':
                    $query->where('remaining_stock', '<=', 0);
                    break;
                case 'in_stock':
                    $query->whereRaw('remaining_stock > threshold_value');
                    break;
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->get('per_page', 20));

        return response()->json($products);
    }

    /**
     * Create new product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'product_id' => 'required|string|unique:products',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string',
            'threshold_value' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['opening_stock'] = $validated['quantity'];
        $validated['remaining_stock'] = $validated['quantity'];

        $product = Product::create($validated);
        $product->load(['category', 'supplier']);

        return response()->json([
            'message' => 'Produit créé avec succès',
            'product' => $product,
        ], 201);
    }

    /**
     * Update product
     */
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
            'remaining_stock' => 'sometimes|integer|min:0',
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

        return response()->json([
            'message' => 'Produit mis à jour avec succès',
            'product' => $product,
        ]);
    }

    /**
     * Delete product
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'message' => 'Produit supprimé avec succès',
        ]);
    }

    /**
     * Bulk update stock
     */
    public function bulkUpdateStock(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.remaining_stock' => 'required|integer|min:0',
        ]);

        foreach ($request->products as $productData) {
            Product::find($productData['id'])->update([
                'remaining_stock' => $productData['remaining_stock'],
            ]);
        }

        return response()->json([
            'message' => 'Stock mis à jour avec succès',
        ]);
    }

    /**
     * Get low stock products
     */
    public function lowStock()
    {
        $products = Product::with(['category', 'supplier'])
            ->whereRaw('remaining_stock <= threshold_value')
            ->orderBy('remaining_stock', 'asc')
            ->get();

        return response()->json($products);
    }

    /**
     * Get out of stock products
     */
    public function outOfStock()
    {
        $products = Product::with(['category', 'supplier'])
            ->where('remaining_stock', '<=', 0)
            ->get();

        return response()->json($products);
    }

    /**
     * Get products expiring soon
     */
    public function expiringSoon()
    {
        $products = Product::with(['category', 'supplier'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date', 'asc')
            ->get();

        return response()->json($products);
    }

    /**
     * Export products to CSV
     */
    public function export()
    {
        $products = Product::with(['category', 'supplier'])->get();

        $csv = "ID,Nom,Référence,Catégorie,Fournisseur,Prix d'achat,Prix de vente,Stock,Unité\n";

        foreach ($products as $product) {
            $csv .= implode(',', [
                $product->id,
                $product->name,
                $product->product_id,
                $product->category->name ?? '',
                $product->supplier->name ?? '',
                $product->buying_price,
                $product->selling_price,
                $product->remaining_stock,
                $product->unit,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_' . date('Y-m-d') . '.csv"',
        ]);
    }
}











