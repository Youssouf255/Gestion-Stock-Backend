<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::all();
        return response()->json($stores);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string',
            'pincode' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        $store = Store::create($validated);
        return response()->json($store, 201);
    }

    public function show($id)
    {
        $store = Store::findOrFail($id);
        return response()->json($store);
    }

    public function update(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        $validated = $request->validate([
            'store_name' => 'sometimes|string|max:255',
            'branch_name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string',
            'pincode' => 'sometimes|string',
            'phone_number' => 'sometimes|string',
        ]);

        $store->update($validated);
        return response()->json($store);
    }

    public function destroy($id)
    {
        $store = Store::findOrFail($id);
        $store->delete();
        return response()->json(['message' => 'Store deleted successfully']);
    }
}











