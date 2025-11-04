<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        // Calculate overview stats - Aperçu des Ventes
        $totalOrders = Order::count();
        $totalSalesValue = Order::where('status', '!=', 'returned')->sum('order_value');
        $totalPurchaseValue = Product::sum(DB::raw('buying_price * quantity'));
        $totalProfit = $totalSalesValue - $totalPurchaseValue;
        
        // Calculate sales (total quantity sold from confirmed orders)
        $sales = Order::where('status', 'confirmed')->sum('quantity');
        
        // Calculate revenue (total sales value from confirmed orders)
        $revenue = Order::where('status', 'confirmed')->sum('order_value');
        
        // Calculate cost (total buying_price * quantity from confirmed orders)
        // This represents the actual cost of products sold
        $cost = Order::where('status', 'confirmed')->sum(DB::raw('buying_price * quantity'));

        // Inventory stats - Résumé de l'Inventaire
        $totalProducts = Product::count();
        $quantityOnHand = Product::sum('remaining_stock');
        $toReceive = Product::where('on_the_way', '>', 0)->sum('on_the_way');

        // Purchases stats - Aperçu des Achats
        $purchaseCount = Order::whereIn('status', ['confirmed', 'out_for_delivery'])->count();
        $purchaseCost = Order::whereIn('status', ['confirmed', 'out_for_delivery'])->sum(DB::raw('buying_price * quantity'));
        $cancelledOrders = Order::where('status', 'returned')->count();
        $returnValue = Order::where('status', 'returned')->sum('order_value');

        // Product Summary - Résumé des Produits
        $categories = Category::count();
        $totalSuppliers = Supplier::count();

        // Best selling products - calculer la quantité réellement vendue depuis les commandes confirmées
        $bestSellingProducts = Product::with(['orders' => function($query) {
            $query->where('status', 'confirmed');
        }])
        ->get()
        ->map(function($product) {
            // Calculer la quantité totale vendue depuis les commandes confirmées (status = 'confirmed')
            $quantitySold = $product->orders()
                ->where('status', 'confirmed')
                ->sum('quantity');
            
            // Calculer le stock restant réel = remaining_stock initial - quantité vendue
            // Si remaining_stock n'est pas encore mis à jour, on le calcule dynamiquement
            $remainingQuantity = max(0, $product->remaining_stock - $quantitySold);
            
            return [
                'name' => $product->name,
                'quantity_sold' => $quantitySold,
                'remaining_quantity' => $remainingQuantity,
                'price' => $product->selling_price ?? $product->buying_price * 1.2,
            ];
        })
        ->filter(function($product) {
            // Ne garder que les produits qui ont été vendus (quantity_sold > 0)
            return $product['quantity_sold'] > 0;
        })
        ->sortByDesc('quantity_sold')
        ->take(3)
        ->values();

        // Low stock products
        $lowStockProducts = Product::whereRaw('remaining_stock <= threshold_value AND remaining_stock > 0')
            ->orderBy('remaining_stock', 'asc')
            ->limit(3)
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'remaining_quantity' => $product->remaining_stock,
                    'unit' => $product->unit,
                ];
            });

        return response()->json([
            'sales_overview' => [
                'sales' => round($sales, 2),
                'revenue' => round($revenue, 2),
                'profit' => round($totalProfit, 2),
                'cost' => round($cost, 2),
            ],
            'inventory_summary' => [
                'quantity_on_hand' => $quantityOnHand,
                'to_receive' => $toReceive,
            ],
            'purchases_overview' => [
                'purchase_count' => $purchaseCount,
                'purchase_cost' => round($purchaseCost, 2),
                'cancelled' => $cancelledOrders,
                'return_value' => round($returnValue, 2),
            ],
            'product_summary' => [
                'suppliers_count' => $totalSuppliers,
                'categories_count' => $categories,
            ],
            'best_selling_products' => $bestSellingProducts,
            'low_stock_products' => $lowStockProducts,
        ]);
    }

    public function ordersStats()
    {
        // Calculate orders stats - use all orders, not just last 7 days
        // This ensures that old orders are also counted in the statistics
        
        // Total orders (all time, but prefer last 7 days if available)
        $sevenDaysAgo = now()->subDays(7);
        $totalOrders7Days = Order::where('created_at', '>=', $sevenDaysAgo)->count();
        $totalOrdersAll = Order::count();
        
        // Use 7 days if > 0, otherwise use all
        $totalOrders = $totalOrders7Days > 0 ? $totalOrders7Days : $totalOrdersAll;

        // Total received orders (confirmed)
        $totalReceived7Days = Order::where('status', 'confirmed')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->count();
        $totalReceivedAll = Order::where('status', 'confirmed')->count();
        $totalReceived = $totalReceived7Days > 0 ? $totalReceived7Days : $totalReceivedAll;

        // Revenue from received orders
        $revenue7Days = Order::where('status', 'confirmed')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->sum('order_value');
        $revenueAll = Order::where('status', 'confirmed')->sum('order_value');
        $revenue = $revenue7Days > 0 ? $revenue7Days : $revenueAll;

        // Total returned orders
        $totalReturned7Days = Order::where('status', 'returned')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->count();
        $totalReturnedAll = Order::where('status', 'returned')->count();
        $totalReturned = $totalReturned7Days > 0 ? $totalReturned7Days : $totalReturnedAll;

        // Cost from returned orders
        $returnCost7Days = Order::where('status', 'returned')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->sum(DB::raw('buying_price * quantity'));
        $returnCostAll = Order::where('status', 'returned')->sum(DB::raw('buying_price * quantity'));
        $returnCost = $returnCost7Days > 0 ? $returnCost7Days : $returnCostAll;

        // Orders on the way (out_for_delivery)
        $onTheWay7Days = Order::where('status', 'out_for_delivery')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->count();
        $onTheWayAll = Order::where('status', 'out_for_delivery')->count();
        $onTheWay = $onTheWay7Days > 0 ? $onTheWay7Days : $onTheWayAll;

        // Cost of orders on the way
        $onTheWayCost7Days = Order::where('status', 'out_for_delivery')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->sum(DB::raw('buying_price * quantity'));
        $onTheWayCostAll = Order::where('status', 'out_for_delivery')->sum(DB::raw('buying_price * quantity'));
        $onTheWayCost = $onTheWayCost7Days > 0 ? $onTheWayCost7Days : $onTheWayCostAll;

        return response()->json([
            'totalCommandes' => $totalOrders,
            'totalReceived' => $totalReceived,
            'revenue' => round($revenue, 2),
            'totalReturned' => $totalReturned,
            'cost' => round($returnCost, 2),
            'onTheWay' => $onTheWay,
            'onTheWayCost' => round($onTheWayCost, 2),
        ]);
    }

    public function bestSellingCategories()
    {
        $categories = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json($categories);
    }

    public function bestSellingProducts()
    {
        $products = Product::with(['category', 'supplier'])
            ->orderBy('quantity', 'desc')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function salesChart()
    {
        // Mock data for sales chart
        $salesData = [
            'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            'datasets' => [
                [
                    'label' => 'Ventes',
                    'data' => [12000, 19000, 15000, 25000, 22000, 30000],
                ],
            ],
        ];

        return response()->json($salesData);
    }

    public function inventoryStats()
    {
        // Calculate categories count
        $categories = Category::count();

        // Calculate total products
        $totalProducts = Product::count();

        // Calculate revenue and cost from confirmed orders in last 7 days
        $sevenDaysAgo = now()->subDays(7);
        
        // Revenue = total order_value from confirmed orders in last 7 days
        $revenue = Order::where('status', 'confirmed')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->sum('order_value');
        
        // Cost = total buying_price * quantity from confirmed orders in last 7 days
        // This represents the cost of products sold (confirmed orders)
        $cost = Order::where('status', 'confirmed')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->sum(DB::raw('buying_price * quantity'));

        // Calculate top selling products (products with most orders in last 7 days)
        $topProducts = Product::whereHas('orders', function($query) use ($sevenDaysAgo) {
            $query->where('created_at', '>=', $sevenDaysAgo)
                  ->where('status', 'confirmed');
        })
        ->withCount(['orders' => function($query) use ($sevenDaysAgo) {
            $query->where('created_at', '>=', $sevenDaysAgo)
                  ->where('status', 'confirmed');
        }])
        ->orderBy('orders_count', 'desc')
        ->limit(5)
        ->get();
        
        $topSelling = $topProducts->count();

        // Calculate low stocks (products where remaining_stock <= threshold_value)
        $lowStocks = Product::whereRaw('remaining_stock <= threshold_value AND remaining_stock > 0')->count();

        // Calculate ordered products (products with confirmed or out_for_delivery orders)
        $ordered = Product::whereHas('orders', function($query) {
            $query->whereIn('status', ['confirmed', 'out_for_delivery']);
        })->count();

        // Calculate out of stock products (remaining_stock = 0)
        $notInStock = Product::where('remaining_stock', '<=', 0)->count();

        return response()->json([
            'categories' => $categories,
            'totalProduits' => $totalProducts,
            'revenue' => round($revenue, 2),
            'topSelling' => $topSelling,
            'cost' => round($cost, 2),
            'lowStocks' => $lowStocks,
            'ordered' => $ordered,
            'notInStock' => $notInStock,
        ]);
    }
}








