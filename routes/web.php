<?php
// routes/web.php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\EmailCheckController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\UserPackageController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\RankController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CommissionTriggerController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// ============================================================
// ROUTES PUBLIQUES
// ============================================================

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/check-email', [EmailCheckController::class, 'check'])->name('check.email');

// Onboarding
Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
Route::get('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');

// Produits (publics)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// ============================================================
// AUTHENTIFICATION SOCIALE
// ============================================================
Route::prefix('auth')->name('social.')->group(function () {
    Route::get('{provider}', [SocialiteController::class, 'redirect'])->name('redirect');
    Route::get('{provider}/callback', [SocialiteController::class, 'callback'])->name('callback');
});

Route::post('/social/store-sponsor', [SocialiteController::class, 'storeSponsor'])->name('social.store-sponsor');

// ============================================================
// PAGES LEGALES
// ============================================================
Route::view('/privacy-policy', 'pages.privacy-policy')->name('privacy-policy');
Route::view('/terms-of-service', 'pages.terms-of-service')->name('terms-of-service');
Route::view('/cookie-policy', 'pages.cookie-policy')->name('cookie-policy');

// ============================================================
// WEBHOOKS (PUBLIC - sans CSRF)
// ============================================================
Route::prefix('webhook')->name('webhook.')->group(function () {
    Route::post('/crypto', [WebhookController::class, 'crypto'])->name('crypto');
    Route::post('/mobile-money', [WebhookController::class, 'mobileMoney'])->name('mobile-money');
    Route::post('/payment', [WebhookController::class, 'payment'])->name('payment');
    Route::post('/stripe', [WebhookController::class, 'stripe'])->name('stripe');
    Route::post('/coinbase', [WebhookController::class, 'coinbase'])->name('coinbase');
});

// ============================================================
// ROUTES AUTHENTIFIEES (Utilisateur)
// ============================================================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profil
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('update-avatar');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('delete-avatar');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('update-password');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
    
    // Abonnements
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [UserPackageController::class, 'index'])->name('index');
        Route::post('/buy', [UserPackageController::class, 'buy'])->name('buy');
        Route::post('/upgrade', [UserPackageController::class, 'upgrade'])->name('upgrade');
    });
    
    // Réseau
    Route::prefix('network')->name('network.')->group(function () {
        Route::get('/', [NetworkController::class, 'index'])->name('index');
        Route::get('/tree', [NetworkController::class, 'treeData'])->name('tree');
        Route::get('/downlines', [NetworkController::class, 'downlines'])->name('downlines');
    });
    
    // Grades
    Route::prefix('rank')->name('rank.')->group(function () {
        Route::get('/', [RankController::class, 'index'])->name('index');
        Route::get('/history', [RankController::class, 'history'])->name('history');
    });
    
    // Finances
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
        Route::get('/export', [WalletController::class, 'export'])->name('export');
        Route::get('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/deposit', [WalletController::class, 'storeDeposit'])->name('deposit.store');
    });
    
    // Retraits
    Route::prefix('withdrawal')->name('withdrawal.')->group(function () {
        Route::get('/', [WithdrawalController::class, 'index'])->name('index');
        Route::post('/', [WithdrawalController::class, 'store'])->name('store');
        Route::get('/{id}', [WithdrawalController::class, 'show'])->name('show');
        Route::post('/{id}/cancel', [WithdrawalController::class, 'cancel'])->name('cancel');
    });
    
    // KYC
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', [KycController::class, 'index'])->name('index');
        Route::get('/create', [KycController::class, 'create'])->name('create');
        Route::post('/', [KycController::class, 'store'])->name('store');
        Route::get('/status', [KycController::class, 'getStatus'])->name('status');
    });
    
    // Rapports
    Route::prefix('report')->name('report.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/earnings', [ReportController::class, 'earnings'])->name('earnings');
        Route::get('/network', [ReportController::class, 'network'])->name('network');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });
    
    // Panier
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::get('/count', [CartController::class, 'count'])->name('count');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::post('/add-package', [CartController::class, 'addPackage'])->name('add-package');
        Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('remove');
        Route::put('/update', [CartController::class, 'update'])->name('update');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
        Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');
    });
    
    // Commandes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::get('/invoice/{order}', [OrderController::class, 'invoice'])->name('invoice');
    });
    
    // Commissions
    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', [CommissionController::class, 'index'])->name('index');
        Route::get('/stats', [CommissionController::class, 'stats'])->name('stats');
        Route::get('/{id}', [CommissionController::class, 'show'])->name('show');
        Route::get('/export', [CommissionController::class, 'export'])->name('export');
        Route::get('/levels', [CommissionController::class, 'getLevelCommissions'])->name('levels');
        Route::get('/pdf', [CommissionController::class, 'pdf'])->name('pdf');
        Route::post('/trigger', [CommissionTriggerController::class, 'triggerPackageCommission'])->name('trigger');
    });
});

// ============================================================
// ROUTES ADMIN
// ============================================================
Route::prefix('admin')
    ->middleware(['auth', 'admin', 'verified'])
    ->name('admin.')
    ->group(function () {
    
    // Dashboard
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats', [App\Http\Controllers\Admin\DashboardController::class, 'stats'])->name('stats');
    
    // Users
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::get('users/{id}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{id}/assign-package', [App\Http\Controllers\Admin\UserController::class, 'assignPackage'])->name('users.assign-package');
    Route::get('users/{id}/network', [App\Http\Controllers\Admin\UserController::class, 'network'])->name('users.network');
    
    // Packages
    Route::resource('packages', App\Http\Controllers\Admin\PackageController::class);
    Route::get('packages/{id}/toggle-status', [App\Http\Controllers\Admin\PackageController::class, 'toggleStatus'])->name('packages.toggle-status');
    
    // Products
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    Route::get('products/{id}/toggle-status', [App\Http\Controllers\Admin\ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::get('products/{id}/toggle-featured', [App\Http\Controllers\Admin\ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    
    // Commissions Admin
    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\CommissionController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\CommissionController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [App\Http\Controllers\Admin\CommissionController::class, 'reject'])->name('reject');
        Route::post('/recalculate', [CommissionTriggerController::class, 'recalculateAll'])->name('recalculate');
        Route::get('/export', [App\Http\Controllers\Admin\CommissionController::class, 'export'])->name('export');
        Route::get('/stats', [App\Http\Controllers\Admin\CommissionController::class, 'stats'])->name('stats');
    });
    
    // Wallets
    Route::prefix('wallets')->name('wallets.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\WalletController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\WalletController::class, 'show'])->name('show');
        Route::post('/{id}/adjust', [App\Http\Controllers\Admin\WalletController::class, 'adjust'])->name('adjust');
        Route::get('/export', [App\Http\Controllers\Admin\WalletController::class, 'export'])->name('export');
    });

    // Withdrawals
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\WithdrawalController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('reject');
        Route::get('/export', [App\Http\Controllers\Admin\WithdrawalController::class, 'export'])->name('export');
    });
    
    // Ranks
    Route::resource('ranks', App\Http\Controllers\Admin\RankController::class);
    Route::get('ranks/{id}/toggle-status', [App\Http\Controllers\Admin\RankController::class, 'toggleStatus'])->name('ranks.toggle-status');
    Route::post('ranks/reassign-all', [App\Http\Controllers\Admin\RankController::class, 'reassignAll'])->name('ranks.reassign-all');
    Route::get('ranks/history', [App\Http\Controllers\Admin\RankController::class, 'history'])->name('ranks.history');
    
    // KYC Admin
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', [KycController::class, 'adminIndex'])->name('index');
        Route::post('/{id}/verify', [KycController::class, 'verify'])->name('verify');
        Route::post('/{id}/reject', [KycController::class, 'reject'])->name('reject');
    });
    
    // Reports Admin
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/sales', [App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('sales');
        Route::get('/commissions', [App\Http\Controllers\Admin\ReportController::class, 'commissions'])->name('commissions');
        Route::get('/users', [App\Http\Controllers\Admin\ReportController::class, 'users'])->name('users');
        Route::get('/export', [App\Http\Controllers\Admin\ReportController::class, 'export'])->name('export');
    });
    
    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('index');
        Route::put('/', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('update');
        Route::get('/commission', [App\Http\Controllers\Admin\SettingController::class, 'commission'])->name('commission');
        Route::put('/commission', [App\Http\Controllers\Admin\SettingController::class, 'updateCommission'])->name('update-commission');
        Route::get('/payment', [App\Http\Controllers\Admin\SettingController::class, 'payment'])->name('payment');
        Route::put('/payment', [App\Http\Controllers\Admin\SettingController::class, 'updatePayment'])->name('update-payment');
        Route::post('/clear-cache', [App\Http\Controllers\Admin\SettingController::class, 'clearCache'])->name('clear-cache');
        Route::post('/optimize', [App\Http\Controllers\Admin\SettingController::class, 'optimize'])->name('optimize');
    });
});

// ============================================================
// AUTHENTIFICATION
// ============================================================
require __DIR__.'/auth.php';

// ============================================================
// ROUTES API (avec throttling)
// ============================================================
Route::prefix('api')
    ->middleware(['auth', 'throttle:api'])
    ->name('api.')
    ->group(function () {
    
    Route::get('dashboard/stats', [DashboardController::class, 'apiStats'])->name('dashboard.stats');
    Route::get('dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart');
    
    Route::get('network/tree', [NetworkController::class, 'apiTree'])->name('network.tree');
    Route::get('network/stats', [NetworkController::class, 'apiStats'])->name('network.stats');
    Route::get('network/search', [NetworkController::class, 'apiSearch'])->name('network.search');
    
    Route::get('wallet/balance', [WalletController::class, 'apiBalance'])->name('wallet.balance');
    Route::post('wallet/withdraw', [WithdrawalController::class, 'apiStore'])->name('wallet.withdraw');
    
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('products/featured', [ProductController::class, 'apiFeatured'])->name('products.featured');
    Route::get('products/categories', [ProductController::class, 'apiCategories'])->name('products.categories');
    
    Route::get('kyc/status', [KycController::class, 'getStatus'])->name('kyc.status');
    
    Route::get('commissions', [CommissionController::class, 'apiIndex'])->name('commissions.index');
    Route::get('commissions/stats', [CommissionController::class, 'apiStats'])->name('commissions.stats');
});