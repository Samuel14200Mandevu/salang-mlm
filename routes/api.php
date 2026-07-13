<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WithdrawalController;
use App\Http\Controllers\Api\RankController;
use App\Http\Controllers\Api\NetworkController;
use App\Http\Controllers\Api\KycController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;

// ============================================================
// ROUTES PUBLIQUES API
// ============================================================

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('api.password.email');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('api.password.reset');
Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

// Products (public)
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/search', [ProductController::class, 'search'])->name('api.products.search');
Route::get('/products/featured', [ProductController::class, 'featured'])->name('api.products.featured');
Route::get('/products/categories', [ProductController::class, 'categories'])->name('api.products.categories');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('api.products.show');

// ============================================================
// ROUTES AUTHENTIFIEES API
// ============================================================

Route::middleware(['auth:sanctum', 'api.auth', 'active'])->group(function () {

    // User Profile
    Route::get('/user', [UserController::class, 'profile'])->name('api.user.profile');
    Route::put('/user', [UserController::class, 'update'])->name('api.user.update');
    Route::post('/user/avatar', [UserController::class, 'updateAvatar'])->name('api.user.avatar');
    Route::put('/user/password', [UserController::class, 'updatePassword'])->name('api.user.password');

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');
    Route::get('/dashboard/chart', [DashboardController::class, 'chart'])->name('api.dashboard.chart');

    // Network
    Route::get('/network', [NetworkController::class, 'index'])->name('api.network.index');
    Route::get('/network/tree', [NetworkController::class, 'tree'])->name('api.network.tree');
    Route::get('/network/downlines', [NetworkController::class, 'downlines'])->name('api.network.downlines');
    Route::get('/network/stats', [NetworkController::class, 'stats'])->name('api.network.stats');
    Route::get('/network/search', [NetworkController::class, 'search'])->name('api.network.search');

    // Rank
    Route::get('/rank', [RankController::class, 'current'])->name('api.rank.current');
    Route::get('/rank/progress', [RankController::class, 'progress'])->name('api.rank.progress');
    Route::get('/rank/history', [RankController::class, 'history'])->name('api.rank.history');
    Route::get('/rank/leaderboard', [RankController::class, 'leaderboard'])->name('api.rank.leaderboard');

    // Wallet
    Route::get('/wallet', [WalletController::class, 'balance'])->name('api.wallet.balance');
    Route::get('/wallet/transactions', [WalletController::class, 'transactions'])->name('api.wallet.transactions');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('api.wallet.deposit');
    Route::post('/wallet/withdraw', [WithdrawalController::class, 'store'])->name('api.wallet.withdraw');

    // Withdrawals
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('api.withdrawals.index');
    Route::get('/withdrawals/{id}', [WithdrawalController::class, 'show'])->name('api.withdrawals.show');
    Route::post('/withdrawals/{id}/cancel', [WithdrawalController::class, 'cancel'])->name('api.withdrawals.cancel');

    // Commissions
    Route::get('/commissions', [CommissionController::class, 'index'])->name('api.commissions.index');
    Route::get('/commissions/stats', [CommissionController::class, 'stats'])->name('api.commissions.stats');
    Route::get('/commissions/{id}', [CommissionController::class, 'show'])->name('api.commissions.show');
    Route::get('/commissions/export', [CommissionController::class, 'export'])->name('api.commissions.export');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('api.orders.show');
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('api.orders.cancel');
    Route::get('/orders/invoice/{id}', [OrderController::class, 'invoice'])->name('api.orders.invoice');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('api.cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('api.cart.add');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('api.cart.remove');
    Route::put('/cart/update', [CartController::class, 'update'])->name('api.cart.update');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('api.cart.clear');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('api.cart.checkout');

    // KYC
    Route::get('/kyc', [KycController::class, 'status'])->name('api.kyc.status');
    Route::get('/kyc/documents', [KycController::class, 'documents'])->name('api.kyc.documents');
    Route::post('/kyc/upload', [KycController::class, 'upload'])->name('api.kyc.upload');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('api.notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('api.notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('api.notifications.destroy-all');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('api.wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('api.wishlist.toggle');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'remove'])->name('api.wishlist.remove');

    // Reports
    Route::get('/reports/earnings', [ReportController::class, 'earnings'])->name('api.reports.earnings');
    Route::get('/reports/network', [ReportController::class, 'network'])->name('api.reports.network');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('api.reports.export');
});

// ============================================================
// ROUTES ADMIN API
// ============================================================

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'api.auth', 'active', 'admin'])
    ->name('api.admin.')
    ->group(function () {

        // Users
        Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
        Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Products
        Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');

        // Packages
        Route::get('/packages', [App\Http\Controllers\Admin\PackageController::class, 'index'])->name('packages.index');
        Route::post('/packages', [App\Http\Controllers\Admin\PackageController::class, 'store'])->name('packages.store');
        Route::put('/packages/{id}', [App\Http\Controllers\Admin\PackageController::class, 'update'])->name('packages.update');
        Route::delete('/packages/{id}', [App\Http\Controllers\Admin\PackageController::class, 'destroy'])->name('packages.destroy');

        // Commissions
        Route::get('/commissions', [App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commissions.index');
        Route::post('/commissions/{id}/approve', [App\Http\Controllers\Admin\CommissionController::class, 'approve'])->name('commissions.approve');
        Route::post('/commissions/{id}/reject', [App\Http\Controllers\Admin\CommissionController::class, 'reject'])->name('commissions.reject');
        Route::post('/commissions/recalculate', [CommissionTriggerController::class, 'recalculateAll'])->name('commissions.recalculate');

        // Withdrawals
        Route::get('/withdrawals', [App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals/{id}/approve', [App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{id}/reject', [App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('withdrawals.reject');

        // Ranks
        Route::get('/ranks', [App\Http\Controllers\Admin\RankController::class, 'index'])->name('ranks.index');
        Route::post('/ranks', [App\Http\Controllers\Admin\RankController::class, 'store'])->name('ranks.store');
        Route::put('/ranks/{id}', [App\Http\Controllers\Admin\RankController::class, 'update'])->name('ranks.update');
        Route::delete('/ranks/{id}', [App\Http\Controllers\Admin\RankController::class, 'destroy'])->name('ranks.destroy');
        Route::post('/ranks/reassign-all', [App\Http\Controllers\Admin\RankController::class, 'reassignAll'])->name('ranks.reassign-all');

        // Settings
        Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/clear-cache', [App\Http\Controllers\Admin\SettingController::class, 'clearCache'])->name('settings.clear-cache');

        // Dashboard Stats
        Route::get('/dashboard/stats', [App\Http\Controllers\Admin\DashboardController::class, 'apiStats'])->name('dashboard.stats');
        Route::get('/dashboard/chart-data', [App\Http\Controllers\Admin\DashboardController::class, 'chartData'])->name('dashboard.chart');
    });