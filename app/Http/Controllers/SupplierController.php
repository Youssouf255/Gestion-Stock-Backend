<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with(['products.category', 'category']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('product', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate($request->get('per_page', 15));
        
        // Ajouter la catégorie au format de réponse
        $suppliers->getCollection()->transform(function ($supplier) {
            // Priorité 1: Catégorie directe du fournisseur (via category_id)
            if ($supplier->category) {
                $supplier->category_name = $supplier->category->name;
                $supplier->category_id = $supplier->category->id;
            }
            // Priorité 2: Catégorie depuis le premier produit associé
            else if ($supplier->products && $supplier->products->count() > 0) {
                $firstProduct = $supplier->products->first();
                if ($firstProduct->category) {
                    $supplier->category_name = $firstProduct->category->name;
                }
            }
            // Priorité 3: Chercher dans tous les produits
            else {
                $product = Product::where('supplier_id', $supplier->id)
                    ->with('category')
                    ->first();
                
                if ($product && $product->category) {
                    $supplier->category_name = $product->category->name;
                } else {
                    $supplier->category_name = null;
                }
            }
            return $supplier;
        });
        
        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:suppliers',
            'phone' => 'required|string',
            'product' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'return_policy' => 'required|in:taking_return,not_taking_return',
            'on_the_way' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('suppliers', 'public');
        }

        $supplier = Supplier::create($validated);
        $supplier->load('category');
        return response()->json($supplier, 201);
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:suppliers,email,' . $id,
            'phone' => 'sometimes|string',
            'product' => 'sometimes|string',
            'category_id' => 'nullable|exists:categories,id',
            'return_policy' => 'sometimes|in:taking_return,not_taking_return',
            'on_the_way' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($supplier->image) {
                Storage::disk('public')->delete($supplier->image);
            }
            $validated['image'] = $request->file('image')->store('suppliers', 'public');
        }

        $supplier->update($validated);
        $supplier->load('category');
        return response()->json($supplier);
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        if ($supplier->image) {
            Storage::disk('public')->delete($supplier->image);
        }

        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted successfully']);
    }
}









