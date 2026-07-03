<?php

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
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CommissionTriggerController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;

// ============================================================
// ROUTES PUBLIQUES
// ============================================================

// Page d'accueil
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
Route::get('/api/products/search', [ProductController::class, 'search'])->name('api.products.search');

// ============================================================
// AUTHENTIFICATION SOCIALE
// ============================================================
Route::prefix('auth')->name('social.')->group(function () {
    Route::get('{provider}', [SocialiteController::class, 'redirect'])->name('redirect');
    Route::get('{provider}/callback', [SocialiteController::class, 'callback'])->name('callback');
});

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
// ROUTES AUTHENTIFIÉES (Utilisateur)
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
    
    // ============================================================
    // ABONNEMENTS (SUBSCRIPTIONS)
    // ============================================================
    Route::get('/subscriptions', [UserPackageController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/buy', [UserPackageController::class, 'buy'])->name('subscriptions.buy');
    Route::post('/subscriptions/upgrade', [UserPackageController::class, 'upgrade'])->name('subscriptions.upgrade');
    
    // ============================================================
    // RÉSEAU
    // ============================================================
    Route::get('/network', [NetworkController::class, 'index'])->name('network.index');
    Route::get('/network/tree', [NetworkController::class, 'treeData'])->name('network.tree');
    Route::get('/network/downlines', [NetworkController::class, 'downlines'])->name('network.downlines');
    Route::get('/network/stats', [NetworkController::class, 'stats'])->name('network.stats');
    
    // ============================================================
    // GRADES
    // ============================================================
    Route::get('/rank', [RankController::class, 'index'])->name('rank.index');
    Route::get('/rank/history', [RankController::class, 'history'])->name('rank.history');
    
    // ============================================================
    // ÉQUIPE (GARDE POUR COMPATIBILITÉ)
    // ============================================================
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    
    // ============================================================
    // FINANCES
    // ============================================================
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');
    Route::get('/wallet/export', [WalletController::class, 'export'])->name('wallet.export');
    
    Route::get('/withdrawal', [WithdrawalController::class, 'index'])->name('withdrawal.index');
    Route::post('/withdrawal', [WithdrawalController::class, 'store'])->name('withdrawal.store');
    Route::get('/withdrawal/{id}', [WithdrawalController::class, 'show'])->name('withdrawal.show');
    Route::post('/withdrawal/{id}/cancel', [WithdrawalController::class, 'cancel'])->name('withdrawal.cancel');
    
    // ============================================================
    // KYC (VÉRIFICATION D'IDENTITÉ)
    // ============================================================
    Route::get('/kyc', [KycController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/create', [KycController::class, 'create'])->name('kyc.create');
    Route::post('/kyc', [KycController::class, 'store'])->name('kyc.store');
    Route::get('/kyc/status', [KycController::class, 'getStatus'])->name('kyc.status');
    
    // ============================================================
    // RAPPORTS
    // ============================================================
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    Route::get('/report/earnings', [ReportController::class, 'earnings'])->name('report.earnings');
    Route::get('/report/network', [ReportController::class, 'network'])->name('report.network');
    Route::get('/report/export', [ReportController::class, 'export'])->name('report.export');
    
    // ============================================================
    // SERVICES
    // ============================================================
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
    
    Route::get('/ticket', [TicketController::class, 'index'])->name('ticket.index');
    Route::get('/ticket/create', [TicketController::class, 'create'])->name('ticket.create');
    Route::post('/ticket', [TicketController::class, 'store'])->name('ticket.store');
    Route::get('/ticket/{id}', [TicketController::class, 'show'])->name('ticket.show');
    
    Route::get('/message', [MessageController::class, 'index'])->name('message.index');
    Route::get('/message/{id}', [MessageController::class, 'show'])->name('message.show');
    Route::post('/message/{id}/read', [MessageController::class, 'markAsRead'])->name('message.read');
    
    // ============================================================
    // PANIER
    // ============================================================
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/add-package', [CartController::class, 'addPackage'])->name('cart.add-package');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::put('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    
    // ============================================================
    // COMMANDES
    // ============================================================
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/invoice/{order}', [OrderController::class, 'invoice'])->name('orders.invoice');
    
    // ============================================================
    // COMMISSIONS
    // ============================================================
    Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::get('/commissions/stats', [CommissionController::class, 'stats'])->name('commissions.stats');
    Route::get('/commissions/{id}', [CommissionController::class, 'show'])->name('commissions.show');
    Route::get('/commissions/export', [CommissionController::class, 'export'])->name('commissions.export');
    Route::get('/commissions/levels', [CommissionController::class, 'getLevelCommissions'])->name('commissions.levels');
    Route::get('/commissions/pdf', [CommissionController::class, 'pdf'])->name('commissions.pdf');
    
    // API
    Route::get('/api/commissions', [CommissionController::class, 'apiIndex'])->name('api.commissions.index');
    Route::get('/api/commissions/stats', [CommissionController::class, 'apiStats'])->name('api.commissions.stats');
    Route::post('/commissions/trigger', [CommissionTriggerController::class, 'triggerPackageCommission'])->name('commissions.trigger');
});

// ============================================================
// ROUTES ADMIN
// ============================================================
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    
    // Dashboard Admin
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // ============================================================
    // Gestion des utilisateurs
    // ============================================================
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');
    Route::get('/users/search', [App\Http\Controllers\Admin\UserController::class, 'search'])->name('users.search');
    
    // ============================================================
    // Gestion des packages (Admin)
    // ============================================================
    Route::get('/packages', [App\Http\Controllers\Admin\PackageController::class, 'index'])->name('packages');
    Route::get('/packages/create', [App\Http\Controllers\Admin\PackageController::class, 'create'])->name('packages.create');
    Route::post('/packages', [App\Http\Controllers\Admin\PackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/{package}/edit', [App\Http\Controllers\Admin\PackageController::class, 'edit'])->name('packages.edit');
    Route::put('/packages/{package}', [App\Http\Controllers\Admin\PackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [App\Http\Controllers\Admin\PackageController::class, 'destroy'])->name('packages.destroy');
    Route::get('/packages/{package}/toggle-status', [App\Http\Controllers\Admin\PackageController::class, 'toggleStatus'])->name('packages.toggle-status');
    
    // ============================================================
    // Gestion des produits
    // ============================================================
    Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products');
    Route::get('/products/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/products/{id}/toggle-status', [App\Http\Controllers\Admin\ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::post('/products/import', [App\Http\Controllers\Admin\ProductController::class, 'import'])->name('products.import');
    Route::get('/products/export', [App\Http\Controllers\Admin\ProductController::class, 'export'])->name('products.export');
    
    // ============================================================
    // Gestion des commissions (Admin)
    // ============================================================
    Route::get('/commissions', [App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commissions');
    Route::get('/commissions/{id}', [App\Http\Controllers\Admin\CommissionController::class, 'show'])->name('commissions.show');
    Route::post('/commissions/{id}/approve', [App\Http\Controllers\Admin\CommissionController::class, 'approve'])->name('commissions.approve');
    Route::post('/commissions/{id}/reject', [App\Http\Controllers\Admin\CommissionController::class, 'reject'])->name('commissions.reject');
    Route::post('/commissions/recalculate', [CommissionTriggerController::class, 'recalculateAll'])->name('commissions.recalculate');
    Route::get('/commissions/export', [App\Http\Controllers\Admin\CommissionController::class, 'export'])->name('commissions.export');
    Route::get('/commissions/stats', [App\Http\Controllers\Admin\CommissionController::class, 'stats'])->name('commissions.stats');
    
    // ============================================================
    // Gestion des portefeuilles
    // ============================================================
    Route::get('/wallets', [App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallets');
    Route::get('/wallets/{id}', [App\Http\Controllers\Admin\WalletController::class, 'show'])->name('wallets.show');
    Route::post('/wallets/{id}/adjust', [App\Http\Controllers\Admin\WalletController::class, 'adjust'])->name('wallets.adjust');
    Route::get('/wallets/export', [App\Http\Controllers\Admin\WalletController::class, 'export'])->name('wallets.export');
    
    // ============================================================
    // Gestion des retraits (Admin)
    // ============================================================
    Route::get('/withdrawals', [App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals');
    Route::get('/withdrawals/{id}', [App\Http\Controllers\Admin\WithdrawalController::class, 'show'])->name('withdrawals.show');
    Route::post('/withdrawals/{id}/approve', [App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{id}/reject', [App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('withdrawals.reject');
    Route::get('/withdrawals/export', [App\Http\Controllers\Admin\WithdrawalController::class, 'export'])->name('withdrawals.export');
    
    // ============================================================
    // Gestion des rangs (Admin)
    // ============================================================
    Route::get('/ranks', [App\Http\Controllers\Admin\RankController::class, 'index'])->name('ranks');
    Route::get('/ranks/create', [App\Http\Controllers\Admin\RankController::class, 'create'])->name('ranks.create');
    Route::post('/ranks', [App\Http\Controllers\Admin\RankController::class, 'store'])->name('ranks.store');
    Route::get('/ranks/{rank}/edit', [App\Http\Controllers\Admin\RankController::class, 'edit'])->name('ranks.edit');
    Route::put('/ranks/{rank}', [App\Http\Controllers\Admin\RankController::class, 'update'])->name('ranks.update');
    Route::delete('/ranks/{rank}', [App\Http\Controllers\Admin\RankController::class, 'destroy'])->name('ranks.destroy');
    Route::get('/ranks/{rank}/toggle-status', [App\Http\Controllers\Admin\RankController::class, 'toggleStatus'])->name('ranks.toggle-status');
    
    // ============================================================
    // KYC Admin
    // ============================================================
    Route::get('/kyc', [KycController::class, 'adminIndex'])->name('kyc');
    Route::post('/kyc/{id}/verify', [KycController::class, 'verify'])->name('kyc.verify');
    
    // ============================================================
    // Rapports Admin
    // ============================================================
    Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports');
    Route::get('/reports/sales', [App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/commissions', [App\Http\Controllers\Admin\ReportController::class, 'commissions'])->name('reports.commissions');
    Route::get('/reports/users', [App\Http\Controllers\Admin\ReportController::class, 'users'])->name('reports.users');
    Route::get('/reports/export', [App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');
    
    // ============================================================
    // Paramètres Admin
    // ============================================================
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
// ROUTES API (AJOUT)
// ============================================================
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'apiStats'])->name('api.dashboard.stats');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('api.dashboard.chart');
    
    Route::get('/network/tree', [NetworkController::class, 'apiTree'])->name('api.network.tree');
    Route::get('/network/stats', [NetworkController::class, 'apiStats'])->name('api.network.stats');
    Route::get('/network/search', [NetworkController::class, 'apiSearch'])->name('api.network.search');
    
    Route::get('/wallet/balance', [WalletController::class, 'apiBalance'])->name('api.wallet.balance');
    Route::post('/wallet/withdraw', [WithdrawalController::class, 'apiStore'])->name('api.wallet.withdraw');
    
    Route::get('/products/featured', [ProductController::class, 'apiFeatured'])->name('api.products.featured');
    Route::get('/products/categories', [ProductController::class, 'apiCategories'])->name('api.products.categories');
    
    Route::get('/kyc/status', [KycController::class, 'getStatus'])->name('api.kyc.status');
});

// ============================================================
// WEBHOOKS (PUBLIC)
// ============================================================
Route::prefix('webhook')->group(function () {
    Route::post('/crypto', [App\Http\Controllers\WebhookController::class, 'crypto'])->name('webhook.crypto');
    Route::post('/mobile-money', [App\Http\Controllers\WebhookController::class, 'mobileMoney'])->name('webhook.mobile-money');
    Route::post('/payment', [App\Http\Controllers\WebhookController::class, 'payment'])->name('webhook.payment');
});