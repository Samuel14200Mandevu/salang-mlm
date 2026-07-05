<?php
// routes/web.php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\UserPackageController;
use App\Http\Controllers\TeamController;
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
Route::get('/privacy-policy', function () {
    return view('pages.privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('pages.terms-of-service');
})->name('terms-of-service');

Route::get('/cookie-policy', function () {
    return view('pages.cookie-policy');
})->name('cookie-policy');

// ============================================================
// WEBHOOKS (PUBLIC)
// ============================================================
Route::prefix('webhook')->group(function () {
    Route::post('/crypto', [WebhookController::class, 'crypto'])->name('webhook.crypto');
    Route::post('/mobile-money', [WebhookController::class, 'mobileMoney'])->name('webhook.mobile-money');
    Route::post('/payment', [WebhookController::class, 'payment'])->name('webhook.payment');
});

// ============================================================
// ROUTES AUTHENTIFIEES (Utilisateur)
// ============================================================
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profil
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Abonnements
    Route::get('/subscriptions', [UserPackageController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/buy', [UserPackageController::class, 'buy'])->name('subscriptions.buy');
    Route::post('/subscriptions/upgrade', [UserPackageController::class, 'upgrade'])->name('subscriptions.upgrade');
    
    // Réseau
    Route::get('/network', [NetworkController::class, 'index'])->name('network.index');
    Route::get('/network/tree', [NetworkController::class, 'treeData'])->name('network.tree');
    Route::get('/network/downlines', [NetworkController::class, 'downlines'])->name('network.downlines');
    
    // Grades
    Route::get('/rank', [RankController::class, 'index'])->name('rank.index');
    Route::get('/rank/history', [RankController::class, 'history'])->name('rank.history');
    
    // Équipe
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    
    // Finances
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');
    Route::get('/wallet/export', [WalletController::class, 'export'])->name('wallet.export');
    Route::get('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/deposit', [WalletController::class, 'storeDeposit'])->name('wallet.deposit.store');
    
    Route::get('/withdrawal', [WithdrawalController::class, 'index'])->name('withdrawal.index');
    Route::post('/withdrawal', [WithdrawalController::class, 'store'])->name('withdrawal.store');
    Route::get('/withdrawal/{id}', [WithdrawalController::class, 'show'])->name('withdrawal.show');
    Route::post('/withdrawal/{id}/cancel', [WithdrawalController::class, 'cancel'])->name('withdrawal.cancel');
    
    // KYC
    Route::get('/kyc', [KycController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/create', [KycController::class, 'create'])->name('kyc.create');
    Route::post('/kyc', [KycController::class, 'store'])->name('kyc.store');
    Route::get('/kyc/status', [KycController::class, 'getStatus'])->name('kyc.status');
    
    // Rapports
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    Route::get('/report/earnings', [ReportController::class, 'earnings'])->name('report.earnings');
    Route::get('/report/network', [ReportController::class, 'network'])->name('report.network');
    Route::get('/report/export', [ReportController::class, 'export'])->name('report.export');
    
    // Panier
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/add-package', [CartController::class, 'addPackage'])->name('cart.add-package');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::put('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    
    // Commandes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/invoice/{order}', [OrderController::class, 'invoice'])->name('orders.invoice');
    
    // Commissions
    Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::get('/commissions/stats', [CommissionController::class, 'stats'])->name('commissions.stats');
    Route::get('/commissions/{id}', [CommissionController::class, 'show'])->name('commissions.show');
    Route::get('/commissions/export', [CommissionController::class, 'export'])->name('commissions.export');
    Route::get('/commissions/levels', [CommissionController::class, 'getLevelCommissions'])->name('commissions.levels');
    Route::get('/commissions/pdf', [CommissionController::class, 'pdf'])->name('commissions.pdf');
    Route::post('/commissions/trigger', [CommissionTriggerController::class, 'triggerPackageCommission'])->name('commissions.trigger');
});

// ============================================================
// ROUTES ADMIN
// ============================================================
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Users
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{id}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Packages
    Route::get('/packages', [App\Http\Controllers\Admin\PackageController::class, 'index'])->name('packages');
    Route::get('/packages/create', [App\Http\Controllers\Admin\PackageController::class, 'create'])->name('packages.create');
    Route::post('/packages', [App\Http\Controllers\Admin\PackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/{id}/edit', [App\Http\Controllers\Admin\PackageController::class, 'edit'])->name('packages.edit');
    Route::put('/packages/{id}', [App\Http\Controllers\Admin\PackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{id}', [App\Http\Controllers\Admin\PackageController::class, 'destroy'])->name('packages.destroy');
    
    // Products
    Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products');
    Route::get('/products/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/products/{id}/toggle-status', [App\Http\Controllers\Admin\ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    
    // Commissions Admin
    Route::get('/commissions', [App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commissions');
    Route::get('/commissions/{id}', [App\Http\Controllers\Admin\CommissionController::class, 'show'])->name('commissions.show');
    Route::post('/commissions/{id}/approve', [App\Http\Controllers\Admin\CommissionController::class, 'approve'])->name('commissions.approve');
    Route::post('/commissions/{id}/reject', [App\Http\Controllers\Admin\CommissionController::class, 'reject'])->name('commissions.reject');
    Route::post('/commissions/recalculate', [CommissionTriggerController::class, 'recalculateAll'])->name('commissions.recalculate');
    Route::get('/commissions/export', [App\Http\Controllers\Admin\CommissionController::class, 'export'])->name('commissions.export');
    Route::get('/commissions/stats', [App\Http\Controllers\Admin\CommissionController::class, 'stats'])->name('commissions.stats');
    
    // Wallets
    Route::get('/wallets', [App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallets');
    Route::get('/wallets/{id}', [App\Http\Controllers\Admin\WalletController::class, 'show'])->name('wallets.show');
    Route::post('/wallets/{id}/adjust', [App\Http\Controllers\Admin\WalletController::class, 'adjust'])->name('wallets.adjust');
    Route::get('/wallets/export', [App\Http\Controllers\Admin\WalletController::class, 'export'])->name('wallets.export');
    
    // Withdrawals
    Route::get('/withdrawals', [App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals');
    Route::get('/withdrawals/{id}', [App\Http\Controllers\Admin\WithdrawalController::class, 'show'])->name('withdrawals.show');
    Route::post('/withdrawals/{id}/approve', [App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{id}/reject', [App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('withdrawals.reject');
    Route::get('/withdrawals/export', [App\Http\Controllers\Admin\WithdrawalController::class, 'export'])->name('withdrawals.export');
    
    // Ranks
    Route::get('/ranks', [App\Http\Controllers\Admin\RankController::class, 'index'])->name('ranks');
    Route::get('/ranks/create', [App\Http\Controllers\Admin\RankController::class, 'create'])->name('ranks.create');
    Route::post('/ranks', [App\Http\Controllers\Admin\RankController::class, 'store'])->name('ranks.store');
    Route::get('/ranks/{id}/edit', [App\Http\Controllers\Admin\RankController::class, 'edit'])->name('ranks.edit');
    Route::put('/ranks/{id}', [App\Http\Controllers\Admin\RankController::class, 'update'])->name('ranks.update');
    Route::delete('/ranks/{id}', [App\Http\Controllers\Admin\RankController::class, 'destroy'])->name('ranks.destroy');
    Route::get('/ranks/{id}/toggle-status', [App\Http\Controllers\Admin\RankController::class, 'toggleStatus'])->name('ranks.toggle-status');
    Route::post('/ranks/reassign-all', [App\Http\Controllers\Admin\RankController::class, 'reassignAll'])->name('ranks.reassign-all');
    Route::get('/ranks/history', [App\Http\Controllers\Admin\RankController::class, 'history'])->name('ranks.history');
    
    // KYC Admin
    Route::get('/kyc', [KycController::class, 'adminIndex'])->name('kyc');
    Route::post('/kyc/{id}/verify', [KycController::class, 'verify'])->name('kyc.verify');
    
    // Reports Admin
    Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports');
    Route::get('/reports/sales', [App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/commissions', [App\Http\Controllers\Admin\ReportController::class, 'commissions'])->name('reports.commissions');
    Route::get('/reports/users', [App\Http\Controllers\Admin\ReportController::class, 'users'])->name('reports.users');
    Route::get('/reports/export', [App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');
    
    // Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings');
    Route::put('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::get('/settings/commission', [App\Http\Controllers\Admin\SettingController::class, 'commission'])->name('settings.commission');
    Route::put('/settings/commission', [App\Http\Controllers\Admin\SettingController::class, 'updateCommission'])->name('settings.update-commission');
    Route::get('/settings/payment', [App\Http\Controllers\Admin\SettingController::class, 'payment'])->name('settings.payment');
    Route::put('/settings/payment', [App\Http\Controllers\Admin\SettingController::class, 'updatePayment'])->name('settings.update-payment');
    Route::post('/settings/clear-cache', [App\Http\Controllers\Admin\SettingController::class, 'clearCache'])->name('settings.clear-cache');
    Route::post('/settings/optimize', [App\Http\Controllers\Admin\SettingController::class, 'optimize'])->name('settings.optimize');
});

// ============================================================
// AUTHENTIFICATION
// ============================================================
require __DIR__.'/auth.php';

// ============================================================
// ROUTES API
// ============================================================
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('dashboard/stats', [DashboardController::class, 'apiStats'])->name('api.dashboard.stats');
    Route::get('dashboard/chart-data', [DashboardController::class, 'chartData'])->name('api.dashboard.chart');
    
    Route::get('network/tree', [NetworkController::class, 'apiTree'])->name('api.network.tree');
    Route::get('network/stats', [NetworkController::class, 'apiStats'])->name('api.network.stats');
    Route::get('network/search', [NetworkController::class, 'apiSearch'])->name('api.network.search');
    
    Route::get('wallet/balance', [WalletController::class, 'apiBalance'])->name('api.wallet.balance');
    Route::post('wallet/withdraw', [WithdrawalController::class, 'apiStore'])->name('api.wallet.withdraw');
    
    Route::get('products/search', [ProductController::class, 'search'])->name('api.products.search');
    Route::get('products/featured', [ProductController::class, 'apiFeatured'])->name('api.products.featured');
    Route::get('products/categories', [ProductController::class, 'apiCategories'])->name('api.products.categories');
    
    Route::get('kyc/status', [KycController::class, 'getStatus'])->name('api.kyc.status');
    
    Route::get('commissions', [CommissionController::class, 'apiIndex'])->name('api.commissions.index');
    Route::get('commissions/stats', [CommissionController::class, 'apiStats'])->name('api.commissions.stats');
});