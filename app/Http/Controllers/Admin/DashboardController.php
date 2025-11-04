<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Order;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function stats()
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();
        $totalOrders = Order::count();
        $totalAdmins = Admin::count();

        // Product statistics
        $lowStockProducts = Product::whereRaw('remaining_stock <= threshold_value')->count();
        $outOfStockProducts = Product::where('remaining_stock', '<=', 0)->count();
        $totalProductValue = Product::sum(DB::raw('remaining_stock * selling_price'));

        // Order statistics
        $pendingOrders = Order::where('status', 'confirmed')->count();
        $deliveredOrders = Order::where('status', 'out_for_delivery')->count();
        $totalOrderValue = Order::sum('order_value');

        // Recent activities
        $recentProducts = Product::with(['category', 'supplier'])
            ->latest()
            ->limit(5)
            ->get();

        $recentOrders = Order::with(['product', 'supplier', 'store'])
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'overview' => [
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'total_suppliers' => $totalSuppliers,
                'total_orders' => $totalOrders,
                'total_admins' => $totalAdmins,
            ],
            'products' => [
                'total' => $totalProducts,
                'low_stock' => $lowStockProducts,
                'out_of_stock' => $outOfStockProducts,
                'total_value' => round($totalProductValue, 2),
            ],
            'orders' => [
                'total' => $totalOrders,
                'pending' => $pendingOrders,
                'delivered' => $deliveredOrders,
                'total_value' => round($totalOrderValue, 2),
            ],
            'recent' => [
                'products' => $recentProducts,
                'orders' => $recentOrders,
            ],
        ]);
    }

    /**
     * Get activity logs
     */
    public function activityLogs()
    {
        // In a real application, you would have an activity log table
        $activities = [
            [
                'id' => 1,
                'admin' => 'Admin User',
                'action' => 'Création de produit',
                'description' => 'Nouveau produit "Tomato" ajouté',
                'created_at' => now()->subHours(2),
            ],
            [
                'id' => 2,
                'admin' => 'Admin User',
                'action' => 'Mise à jour de stock',
                'description' => 'Stock mis à jour pour "Red Bull"',
                'created_at' => now()->subHours(5),
            ],
        ];

        return response()->json($activities);
    }
}











