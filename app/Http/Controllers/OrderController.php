<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['product', 'supplier', 'store']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('order_id', 'like', "%{$search}%");
        }

        $orders = $query->paginate($request->get('per_page', 15));
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'store_id' => 'required|exists:stores,id',
            'order_id' => 'required|string|unique:orders',
            'order_value' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string',
            'buying_price' => 'required|numeric|min:0',
            'expected_delivery' => 'required|date',
            'status' => 'required|in:confirmed,delayed,out_for_delivery,returned',
            'notify_on_delivery' => 'nullable|boolean',
        ]);

        $order = Order::create($validated);
        $order->load(['product', 'supplier', 'store']);

        // Si la commande est confirmée, mettre à jour le stock restant du produit
        if ($order->status === 'confirmed') {
            $product = Product::find($order->product_id);
            if ($product) {
                $newRemainingStock = max(0, $product->remaining_stock - $order->quantity);
                $product->update(['remaining_stock' => $newRemainingStock]);
            }
        }

        return response()->json($order, 201);
    }

    public function show($id)
    {
        $order = Order::with(['product', 'supplier', 'store'])->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'store_id' => 'sometimes|exists:stores,id',
            'order_id' => 'sometimes|string|unique:orders,order_id,' . $id,
            'order_value' => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|integer|min:1',
            'unit' => 'sometimes|string',
            'buying_price' => 'sometimes|numeric|min:0',
            'expected_delivery' => 'sometimes|date',
            'status' => 'sometimes|in:confirmed,delayed,out_for_delivery,returned',
            'notify_on_delivery' => 'nullable|boolean',
        ]);

        // Sauvegarder l'ancien status et la quantité pour gérer les changements de stock
        $oldStatus = $order->status;
        $oldQuantity = $order->quantity;
        $productId = $order->product_id;
        $newStatus = isset($validated['status']) ? $validated['status'] : $oldStatus;
        $newQuantity = isset($validated['quantity']) ? $validated['quantity'] : $oldQuantity;

        $order->update($validated);
        $order->load(['product', 'supplier', 'store']);

        // Gérer la mise à jour du stock selon le changement de status
        $product = Product::find($productId);
        if ($product) {
            // Si le status change vers 'confirmed', décrémenter le stock
            if ($oldStatus !== 'confirmed' && $newStatus === 'confirmed') {
                $newRemainingStock = max(0, $product->remaining_stock - $newQuantity);
                $product->update(['remaining_stock' => $newRemainingStock]);
            }
            // Si le status change de 'confirmed' vers autre chose, restaurer le stock
            elseif ($oldStatus === 'confirmed' && $newStatus !== 'confirmed') {
                $product->increment('remaining_stock', $oldQuantity);
            }
            // Si la quantité change et le status est 'confirmed', ajuster le stock
            elseif ($oldStatus === 'confirmed' && $newStatus === 'confirmed' && $newQuantity != $oldQuantity) {
                $quantityDiff = $oldQuantity - $newQuantity;
                $newRemainingStock = max(0, $product->remaining_stock + $quantityDiff);
                $product->update(['remaining_stock' => $newRemainingStock]);
            }
        }

        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        
        // Si la commande était confirmée, restaurer le stock
        if ($order->status === 'confirmed') {
            $product = Product::find($order->product_id);
            if ($product) {
                $product->increment('remaining_stock', $order->quantity);
            }
        }
        
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }
}









