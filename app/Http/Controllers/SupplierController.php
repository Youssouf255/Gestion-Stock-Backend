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
        
        // Ajouter la catégorie et s'assurer que toutes les colonnes sont présentes
        $suppliers->getCollection()->transform(function ($supplier) {
            // S'assurer que toutes les colonnes sont présentes avec des valeurs par défaut
            $supplierData = [
                'id' => $supplier->id,
                'name' => $supplier->name ?? '',
                'email' => $supplier->email ?? '',
                'phone' => $supplier->phone ?? '',
                'product' => $supplier->product ?? '',
                'category_id' => $supplier->category_id ?? null,
                'return_policy' => $supplier->return_policy ?? 'not_taking_return',
                'on_the_way' => $supplier->on_the_way ?? 0,
                'image' => $supplier->image ?? null,
            ];
            
            // Priorité 1: Catégorie directe du fournisseur (via category_id)
            if ($supplier->category) {
                $supplierData['category_name'] = $supplier->category->name;
                $supplierData['category_id'] = $supplier->category->id;
                $supplierData['category'] = [
                    'id' => $supplier->category->id,
                    'name' => $supplier->category->name
                ];
            }
            // Priorité 2: Catégorie depuis le premier produit associé
            else if ($supplier->products && $supplier->products->count() > 0) {
                $firstProduct = $supplier->products->first();
                if ($firstProduct->category) {
                    $supplierData['category_name'] = $firstProduct->category->name;
                    $supplierData['category_id'] = $firstProduct->category->id;
                    $supplierData['category'] = [
                        'id' => $firstProduct->category->id,
                        'name' => $firstProduct->category->name
                    ];
                }
            }
            // Priorité 3: Chercher dans tous les produits
            else {
                $product = Product::where('supplier_id', $supplier->id)
                    ->with('category')
                    ->first();
                
                if ($product && $product->category) {
                    $supplierData['category_name'] = $product->category->name;
                    $supplierData['category_id'] = $product->category->id;
                    $supplierData['category'] = [
                        'id' => $product->category->id,
                        'name' => $product->category->name
                    ];
                } else {
                    $supplierData['category_name'] = null;
                    $supplierData['category'] = null;
                }
            }
            
            // Ajouter les produits associés
            $supplierData['products'] = $supplier->products ? $supplier->products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name
                    ] : null
                ];
            })->toArray() : [];
            
            // Mettre à jour le supplier avec toutes les données
            foreach ($supplierData as $key => $value) {
                $supplier->$key = $value;
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









