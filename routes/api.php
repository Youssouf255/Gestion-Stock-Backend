<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\Admin\AdminManagementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User Authentication Routes (Public)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// User Protected Routes
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Dashboard routes
Route::prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/best-selling-categories', [DashboardController::class, 'bestSellingCategories']);
    Route::get('/best-selling-products', [DashboardController::class, 'bestSellingProducts']);
    Route::get('/sales-chart', [DashboardController::class, 'salesChart']);
    Route::get('/inventory-stats', [DashboardController::class, 'inventoryStats']);
    Route::get('/orders-stats', [DashboardController::class, 'ordersStats']);
});

// Products routes
Route::apiResource('products', ProductController::class);
Route::get('products/low-stock', [ProductController::class, 'lowStock']);

// Categories routes
Route::apiResource('categories', CategoryController::class);

// Suppliers routes
Route::apiResource('suppliers', SupplierController::class);

// Stores routes
Route::apiResource('stores', StoreController::class);

// Orders routes
Route::apiResource('orders', OrderController::class);

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// Admin Authentication Routes (Public)
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
});

// Admin Protected Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    
    // Auth routes
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::get('/me', [AdminAuthController::class, 'me']);
    Route::post('/change-password', [AdminAuthController::class, 'changePassword']);
    
    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/activity-logs', [AdminDashboardController::class, 'activityLogs']);
    });
    
    // Product Management
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductManagementController::class, 'index']);
        Route::post('/', [ProductManagementController::class, 'store']);
        Route::put('/{id}', [ProductManagementController::class, 'update']);
        Route::delete('/{id}', [ProductManagementController::class, 'destroy']);
        Route::post('/bulk-update-stock', [ProductManagementController::class, 'bulkUpdateStock']);
        Route::get('/low-stock', [ProductManagementController::class, 'lowStock']);
        Route::get('/out-of-stock', [ProductManagementController::class, 'outOfStock']);
        Route::get('/expiring-soon', [ProductManagementController::class, 'expiringSoon']);
        Route::get('/export', [ProductManagementController::class, 'export']);
    });
    
    // Admin Management (Super Admin Only)
    Route::prefix('admins')->middleware('super_admin')->group(function () {
        Route::get('/', [AdminManagementController::class, 'index']);
        Route::post('/', [AdminManagementController::class, 'store']);
        Route::put('/{id}', [AdminManagementController::class, 'update']);
        Route::delete('/{id}', [AdminManagementController::class, 'destroy']);
        Route::post('/{id}/toggle-status', [AdminManagementController::class, 'toggleStatus']);
    });
});
