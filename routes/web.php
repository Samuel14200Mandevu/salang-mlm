<?php
// routes/web.php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\EmailCheckController;
use App\Http\Controllers\Auth\SponsorCheckController;
use App\Http\Controllers\Auth\RegisteredUserController; 
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserPackageController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\RankController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CommissionTriggerController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CommissionDashboardController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ActivationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CommissionController as AdminCommissionController;
use App\Http\Controllers\Admin\CommissionPeriodController as AdminCommissionPeriodController;
use App\Http\Controllers\Admin\WalletController as AdminWalletController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Admin\RankController as AdminRankController;
use App\Http\Controllers\Admin\KycController as AdminKycController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\ActivationController as AdminActivationController;

use App\Http\Controllers\LegalController;

use App\Http\Controllers\Admin\AdminPVController;
use App\Http\Controllers\Admin\AdminPVImportController;

// Controllers Cashier
use App\Http\Controllers\Cashier\CashierController;
use App\Http\Controllers\Admin\AdminCashierController;

// Admin POS Reports Controller
use App\Http\Controllers\Admin\AdminPOSReportController;

use Illuminate\Support\Facades\Route;

// ============================================================
// ROUTES PUBLIQUES
// ============================================================

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/contact', 'pages.contact')->name('contact');

// Sponsor & Email check (AJAX)
Route::get('/check-email', [EmailCheckController::class, 'check'])->name('check.email');
Route::get('/check-sponsor', [SponsorCheckController::class, 'check'])->name('check.sponsor');

// ✅ NOUVELLE ROUTE : Vérification du code sponsor au format 51XXXX
Route::post('/check-sponsor-code', [RegisteredUserController::class, 'checkSponsorCode'])
    ->name('check.sponsor.code');

// Produits (publics)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// Paiements (publics)
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');

// ============================================================
// ACTIVATION DE COMPTE (PUBLIC)
// ============================================================
Route::get('/activate/{code}', [ActivationController::class, 'activateWithLink'])->name('activate.account');

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
// WEBHOOKS (PUBLIC) - Sans CSRF
// ============================================================
Route::prefix('webhook')->group(function () {
    Route::post('/flexpay', [WebhookController::class, 'flexpay'])
        ->name('webhook.flexpay')
        ->withoutMiddleware(['csrf']);
    
    Route::post('/crypto', [WebhookController::class, 'crypto'])
        ->name('webhook.crypto')
        ->withoutMiddleware(['csrf']);
    
    Route::post('/mobile-money', [WebhookController::class, 'mobileMoney'])
        ->name('webhook.mobile-money')
        ->withoutMiddleware(['csrf']);
    
    Route::post('/payment', [WebhookController::class, 'payment'])
        ->name('webhook.payment')
        ->withoutMiddleware(['csrf']);
});

// ============================================================
// WISHLIST
// ============================================================
Route::middleware(['auth', 'active'])->prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/add/{product}', [WishlistController::class, 'add'])->name('add');
    Route::delete('/remove/{product}', [WishlistController::class, 'remove'])->name('remove');
    Route::post('/toggle/{product}', [WishlistController::class, 'toggle'])->name('toggle');
});

// ============================================================
// NOTIFICATIONS
// ============================================================
Route::middleware(['auth', 'active'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroy-all');
});

// ============================================================
// ROUTES AUTHENTIFIEES (Utilisateur)
// ============================================================
Route::middleware(['auth', 'active'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'apiStats'])->name('dashboard.stats');
    Route::get('/dashboard/chart', [DashboardController::class, 'chartData'])->name('dashboard.chart');

    // ============================================================
    // PROFIL
    // ============================================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('update-avatar');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('delete-avatar');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('update-password');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // ABONNEMENTS (PACKAGES)
    // ============================================================
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [UserPackageController::class, 'index'])->name('index');
        Route::post('/buy', [UserPackageController::class, 'buy'])->name('buy');
        Route::post('/upgrade', [UserPackageController::class, 'upgrade'])->name('upgrade');
    });

    // ============================================================
    // RESEAU (NETWORK/GENEALOGY)
    // ============================================================
    Route::prefix('network')->name('network.')->group(function () {
        Route::get('/', [NetworkController::class, 'index'])->name('index');
        Route::get('/tree', [NetworkController::class, 'treeData'])->name('tree');
        Route::get('/downlines', [NetworkController::class, 'downlines'])->name('downlines');
        Route::get('/search', [NetworkController::class, 'search'])->name('search');
        Route::get('/stats', [NetworkController::class, 'apiStats'])->name('stats');
    });

    // ============================================================
    // GRADES (RANKS)
    // ============================================================
    Route::prefix('rank')->name('rank.')->group(function () {
        Route::get('/', [RankController::class, 'index'])->name('index');
        Route::get('/history', [RankController::class, 'history'])->name('history');
        Route::get('/leaderboard', [RankController::class, 'leaderboard'])->name('leaderboard');
        Route::get('/progress', [RankController::class, 'apiProgress'])->name('progress');
    });

    // ============================================================
    // PORTEFEUILLE (WALLET)
    // ============================================================
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
        Route::get('/export', [WalletController::class, 'export'])->name('export');
        Route::get('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/deposit', [WalletController::class, 'storeDeposit'])->name('deposit.store');
        Route::get('/balance', [WalletController::class, 'apiBalance'])->name('balance');
    });

    // ============================================================
    // RETRAITS (WITHDRAWALS)
    // ============================================================
    Route::prefix('withdrawal')->name('withdrawal.')->group(function () {
        Route::get('/', [WithdrawalController::class, 'index'])->name('index');
        Route::post('/', [WithdrawalController::class, 'store'])->name('store');
        Route::get('/{id}', [WithdrawalController::class, 'show'])->name('show');
        Route::post('/{id}/cancel', [WithdrawalController::class, 'cancel'])->name('cancel');
    });

    // ============================================================
    // KYC
    // ============================================================
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', [KycController::class, 'index'])->name('index');
        Route::get('/create', [KycController::class, 'create'])->name('create');
        Route::post('/', [KycController::class, 'store'])->name('store');
        Route::get('/status', [KycController::class, 'getStatus'])->name('status');
    });

    // ============================================================
    // RAPPORTS
    // ============================================================
    Route::prefix('report')->name('report.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/earnings', [ReportController::class, 'earnings'])->name('earnings');
        Route::get('/network', [ReportController::class, 'network'])->name('network');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });

    // ============================================================
    // PANIER (CART) - Utilisateur
    // ============================================================
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

    // ============================================================
    // PAIEMENTS
    // ============================================================
    Route::post('/payment/process', [PaymentController::class, 'process'])->name('payment.process');

    // ============================================================
    // COMMANDES (ORDERS) - Routes utilisateur
    // ============================================================
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::get('/invoice/{order}', [OrderController::class, 'invoice'])->name('invoice');
        Route::get('/invoice/{order}/download', [OrderController::class, 'downloadInvoice'])->name('invoice.download');
    });

    // ============================================================
    // COMMISSIONS
    // ============================================================
    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', [CommissionController::class, 'index'])->name('index');
        Route::get('/stats', [CommissionController::class, 'stats'])->name('stats');
        Route::get('/dashboard', [CommissionDashboardController::class, 'index'])->name('dashboard');
        Route::get('/export', [CommissionController::class, 'export'])->name('export');
        Route::get('/levels', [CommissionController::class, 'getLevelCommissions'])->name('levels');
        Route::get('/{id}/json', [CommissionController::class, 'getJsonDetails'])->name('json.details');
        Route::get('/export-pdf', [CommissionController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{id}', [CommissionController::class, 'show'])->name('show');
    });

    // ============================================================
    // ACTIVATION DE COMPTE (AUTHENTIFIE)
    // ============================================================
    Route::prefix('activate')->name('activate.')->group(function () {
        Route::get('/', [ActivationController::class, 'index'])->name('index');
        Route::post('/code', [ActivationController::class, 'activateWithCode'])->name('code');
        Route::post('/package', [ActivationController::class, 'activateWithPackage'])->name('package');
        Route::post('/resend', [ActivationController::class, 'resendCode'])->name('resend');
    });

}); // FIN DU GROUPE auth, active

// ============================================================
// ROUTES CASHIER
// ============================================================
Route::middleware(['auth', 'active'])->prefix('cashier')->name('cashier.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [CashierController::class, 'dashboard'])->name('dashboard');
    
    // POS - Vente directe (un seul produit)
    Route::get('/pos', [CashierController::class, 'pos'])->name('pos');
    Route::get('/pos/sale/{product}', [CashierController::class, 'posSale'])->name('pos.sale');
    Route::post('/pos/order', [CashierController::class, 'createOrder'])->name('pos.order');
    Route::get('/sponsor/check', [CashierController::class, 'checkSponsor'])->name('sponsor.check');
    Route::get('/product/find/{id}', [CashierController::class, 'findProduct'])->name('product.find');
    Route::get('/product/find-by-sku/{sku}', [CashierController::class, 'findProductBySku'])->name('product.find-by-sku');
    
    // Panier multi-produits
    Route::post('/cart/add', [CashierController::class, 'addToCart'])->name('cart.add');
    Route::get('/checkout', [CashierController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/order', [CashierController::class, 'createCheckoutOrder'])->name('checkout.order');
    
    // Commandes
    Route::get('/orders', [CashierController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [CashierController::class, 'showOrder'])->name('orders.show');
    Route::get('/orders/invoice/{id}', [CashierController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/invoice/{id}/print', [CashierController::class, 'printInvoice'])->name('orders.invoice.print');
    Route::post('/orders/{id}/cancel', [CashierController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/orders/{id}/request-cancellation', [CashierController::class, 'requestCancellation'])->name('orders.request-cancellation');
    
    // Clients
    Route::get('/customers', [CashierController::class, 'customers'])->name('customers');
    Route::get('/customers/search', [CashierController::class, 'searchCustomer'])->name('customers.search');
    Route::post('/customers', [CashierController::class, 'createCustomer'])->name('customers.store');
    Route::get('/customers/{id}', [CashierController::class, 'showCustomer'])->name('customers.show');
    
    // ============================================================
    // MEMBRES - Gestion complète AVEC création
    // ============================================================
    Route::get('/members', [CashierController::class, 'members'])->name('members');
    Route::get('/members/create', [CashierController::class, 'memberCreate'])->name('members.create');  
    Route::post('/members', [CashierController::class, 'memberStore'])->name('members.store'); 
    Route::get('/check-member-code', [CashierController::class, 'checkMemberCode'])->name('check-member-code');         
    Route::get('/search-sponsors', [CashierController::class, 'searchSponsors'])->name('search-sponsors');
    Route::get('/members/{member}', [CashierController::class, 'memberShow'])->name('members.show');
    Route::get('/members/{member}/commissions', [CashierController::class, 'memberCommissions'])->name('members.commissions');
    Route::get('/members/{member}/orders', [CashierController::class, 'memberOrders'])->name('members.orders');
    Route::put('/members/{member}/commissions/update', [CashierController::class, 'updateMemberCommissions'])->name('members.commissions.update');
    Route::put('/members/{member}/commissions/pay-all', [CashierController::class, 'payAllMemberCommissions'])->name('members.commissions.pay-all');
    
    // ============================================================
    // VÉRIFICATION DU PARRAIN (AJAX) - ✅ DÉPLACÉ ICI
    // ============================================================
    Route::get('/check-sponsor', [CashierController::class, 'checkSponsor'])->name('check-sponsor');
    
    // Commissions - Routes statiques avant paramètres
    Route::get('/commissions', [CashierController::class, 'commissions'])->name('commissions');
    Route::get('/commissions/export', [CashierController::class, 'exportCommissions'])->name('commissions.export');
    Route::get('/commissions/export-pdf', [CashierController::class, 'exportPdf'])->name('commissions.export-pdf');
    Route::get('/commissions/stats', [CashierController::class, 'commissionsStats'])->name('commissions.stats');
    Route::get('/commissions/network/{userId}', [CashierController::class, 'viewNetwork'])->name('commissions.network');
    
    // Routes avec paramètres (après les statiques)
    Route::get('/commissions/{id}', [CashierController::class, 'commissionShow'])->name('commissions.show');
    Route::post('/commissions/{id}/approve', [CashierController::class, 'approveCommission'])->name('commissions.approve');
    Route::post('/commissions/{id}/reject', [CashierController::class, 'rejectCommission'])->name('commissions.reject');
    Route::post('/commissions/{id}/pay', [CashierController::class, 'commissionPay'])->name('commissions.pay');
    Route::post('/commissions/{id}/cancel', [CashierController::class, 'commissionCancel'])->name('commissions.cancel');
    Route::post('/commissions/batch-approve', [CashierController::class, 'batchApproveCommissions'])->name('commissions.batch-approve');
    
    // Ventes du jour
    Route::get('/daily-sales', [CashierController::class, 'dailySales'])->name('daily-sales');
    
    // Historique complet
    Route::get('/history', [CashierController::class, 'history'])->name('history');
    
    // Statistiques
    Route::get('/stats', [CashierController::class, 'stats'])->name('stats');
    Route::get('/stats/export', [CashierController::class, 'exportStats'])->name('stats.export');
    
    // Profil caissier
    Route::get('/profile', [CashierController::class, 'profile'])->name('profile');
    Route::put('/profile', [CashierController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [CashierController::class, 'updatePassword'])->name('profile.update-password');
    Route::delete('/profile', [CashierController::class, 'destroyProfile'])->name('profile.destroy');
    Route::post('/profile/avatar', [CashierController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar', [CashierController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    
    // Factures
    Route::get('/invoice/{order}/pdf', [CashierController::class, 'downloadInvoice'])->name('invoice.download');
});

// ============================================================
// ROUTES ADMIN
// ============================================================
Route::prefix('admin')
    ->middleware(['auth', 'active', 'admin'])
    ->name('admin.')
    ->group(function () {

        // Dashboard Admin
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'apiStats'])->name('dashboard.stats');
        Route::get('/dashboard/chart-data', [AdminDashboardController::class, 'chartData'])->name('dashboard.chart');

        // ============================================================
        // USERS ADMIN
        // ============================================================
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminUserController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminUserController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{id}/assign-package', [AdminUserController::class, 'assignPackage'])->name('assign-package');
            Route::get('/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('reset-password');
            Route::get('/search', [AdminUserController::class, 'search'])->name('search');
            Route::get('/export', [AdminUserController::class, 'export'])->name('export');
            Route::post('/import', [AdminUserController::class, 'import'])->name('import');
        });
        Route::get('/users', [AdminUserController::class, 'index'])->name('users');

        // ============================================================
        // GESTION DES PV (Points de Volume) - ADMIN
        // ============================================================
        Route::prefix('pv')->name('pv.')->group(function () {
            
            // ============================================================
            // ROUTES STATIQUES (DOIVENT ÊTRE AVANT LES ROUTES DYNAMIQUES)
            // ============================================================
            
            // Page principale et recherche
            Route::get('/', [AdminPVController::class, 'index'])->name('index');
            Route::get('/search', [AdminPVController::class, 'search'])->name('search');
            Route::get('/export', [AdminPVController::class, 'export'])->name('export');
            
            // Statistiques
            Route::get('/stats', [AdminPVController::class, 'stats'])->name('stats');
            Route::get('/history/{user?}', [AdminPVController::class, 'history'])->name('history');
            
            // ✅ ROUTES D'IMPORT (STATIQUES)
            Route::prefix('import')->name('import.')->group(function () {
                Route::get('/', [AdminPVImportController::class, 'index'])->name('index');
                Route::post('/csv', [AdminPVImportController::class, 'importCSV'])->name('csv');
                Route::post('/manual', [AdminPVImportController::class, 'importFromOrder'])->name('manual');
                Route::post('/monthly', [AdminPVImportController::class, 'addMonthlyPV'])->name('monthly');
            });

            // ✅ ROUTES API STATIQUES
            Route::get('/search-user', [AdminPVImportController::class, 'searchUser'])->name('search-user');
            Route::get('/user-stats/{userId}', [AdminPVImportController::class, 'getUserStats'])->name('user-stats');

            // ============================================================
            // GESTION DES PÉRIODES (STATIQUES)
            // ============================================================
            Route::prefix('periods')->name('periods.')->group(function () {
                Route::get('/', [AdminPVController::class, 'periods'])->name('index');
                Route::post('/create', [AdminPVController::class, 'createPeriod'])->name('create');
                Route::post('/{period}/process', [AdminPVController::class, 'processPeriod'])->name('process');
                Route::post('/{period}/reset-monthly', [AdminPVController::class, 'resetMonthlyPV'])->name('reset-monthly');
                Route::delete('/{period}/clean', [AdminPVController::class, 'cleanPeriod'])->name('clean');
            });

            // ============================================================
            // ROUTES DYNAMIQUES (AVEC PARAMÈTRES) - APRÈS LES STATIQUES
            // ============================================================
            
            // Gestion individuelle
            Route::get('/{user}', [AdminPVController::class, 'show'])->name('show');
            Route::put('/{user}', [AdminPVController::class, 'update'])->name('update');
            Route::post('/{user}/monthly', [AdminPVController::class, 'addMonthly'])->name('monthly');
            Route::post('/{user}/add-historical', [AdminPVController::class, 'addHistorical'])->name('add-historical');
            Route::post('/{user}/add-monthly', [AdminPVImportController::class, 'addMonthlyPV'])->name('add-monthly');
            Route::get('/{user}/rank-info', [AdminPVController::class, 'getRankInfo'])->name('rank-info');
            Route::post('/{user}/recalculate-rank', [AdminPVController::class, 'recalculateRank'])->name('recalculate-rank');
            Route::put('/reset/{user}', [AdminPVController::class, 'reset'])->name('reset');
            
            // Routes avec paramètres dynamiques
            Route::delete('/history/{id}', [AdminPVController::class, 'deleteHistory'])->name('delete-history');
            
            // Attribution massive
            Route::post('/bulk', [AdminPVController::class, 'bulkAssign'])->name('bulk');

        }); // FIN GROUPE PV

        Route::get('/pv', [AdminPVController::class, 'index'])->name('pv');

        // ============================================================
        // GESTION DES CAISSIERS - ADMIN
        // ============================================================
        Route::prefix('cashiers')->name('cashiers.')->group(function () {
            Route::get('/', [AdminCashierController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminCashierController::class, 'show'])->name('show');
            Route::post('/{id}/toggle-status', [AdminCashierController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{id}', [AdminCashierController::class, 'destroy'])->name('destroy');
        });

        // ============================================================
        // PACKAGES ADMIN
        // ============================================================
        Route::prefix('packages')->name('packages.')->group(function () {
            Route::get('/', [AdminPackageController::class, 'index'])->name('index');
            Route::get('/create', [AdminPackageController::class, 'create'])->name('create');
            Route::post('/', [AdminPackageController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AdminPackageController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminPackageController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminPackageController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/toggle-status', [AdminPackageController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{id}/duplicate', [AdminPackageController::class, 'duplicate'])->name('duplicate');
        });
        Route::get('/packages', [AdminPackageController::class, 'index'])->name('packages');

        // ============================================================
        // PRODUCTS ADMIN
        // ============================================================
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [AdminProductController::class, 'index'])->name('index');
            Route::get('/create', [AdminProductController::class, 'create'])->name('create');
            Route::post('/', [AdminProductController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AdminProductController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminProductController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminProductController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{id}/toggle-featured', [AdminProductController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::delete('/{id}/gallery', [AdminProductController::class, 'removeGalleryImage'])->name('remove-gallery');
            
            // Routes QR Codes
            Route::get('/{id}/qr-code', [AdminProductController::class, 'showQrCode'])->name('qr-code');
            Route::get('/{id}/download-qr', [AdminProductController::class, 'downloadQrCode'])->name('download-qr');
            Route::post('/generate-qr-codes', [AdminProductController::class, 'generateQrCodesBatch'])->name('generate-qr-codes');
            Route::get('/generate-all-qr', [AdminProductController::class, 'generateAllQrCodes'])->name('generate-all-qr');
            Route::get('/qr-codes', [AdminProductController::class, 'showQrCodes'])->name('qr-codes');
            Route::get('/qr-codes/print-all', [AdminProductController::class, 'printAllQrCodes'])->name('print-all-qr');
        });
        Route::get('/products', [AdminProductController::class, 'index'])->name('products');

        // ============================================================
        // ORDERS ADMIN
        // ============================================================
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
            Route::get('/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('invoice');
            Route::put('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('update-status');
            Route::get('/export', [AdminOrderController::class, 'export'])->name('export');
            Route::put('/{order}/handle-cancellation', [AdminOrderController::class, 'handleCancellation'])->name('handle-cancellation');
        });

        // ============================================================
        // COMMISSIONS ADMIN
        // ============================================================
        Route::prefix('commissions')->name('commissions.')->group(function () {
            Route::get('/', [AdminCommissionController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminCommissionController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [AdminCommissionController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [AdminCommissionController::class, 'reject'])->name('reject');
            Route::post('/batch-approve', [AdminCommissionController::class, 'batchApprove'])->name('batch-approve');
            Route::post('/recalculate', [CommissionTriggerController::class, 'recalculateAll'])->name('recalculate');
            Route::get('/export', [AdminCommissionController::class, 'export'])->name('export');
            Route::get('/stats', [AdminCommissionController::class, 'stats'])->name('stats');
            Route::get('/network/{id}', [AdminCommissionController::class, 'viewNetwork'])->name('network');

            Route::prefix('periods')->name('periods.')->group(function () {
                Route::get('/', [AdminCommissionPeriodController::class, 'index'])->name('index');
                Route::get('/{id}', [AdminCommissionPeriodController::class, 'show'])->name('show');
                Route::post('/create', [AdminCommissionPeriodController::class, 'create'])->name('create');
                Route::post('/{id}/process', [AdminCommissionPeriodController::class, 'process'])->name('process');
                Route::post('/{id}/close', [AdminCommissionPeriodController::class, 'close'])->name('close');
                Route::delete('/{id}', [AdminCommissionPeriodController::class, 'destroy'])->name('destroy');
                Route::get('/{id}/export', [AdminCommissionPeriodController::class, 'export'])->name('export');
                Route::delete('/{period}/clean', [CommissionTriggerController::class, 'cleanPeriod'])->name('clean');
            });

            Route::post('/force-monthly', [CommissionTriggerController::class, 'forceMonthlyProcessing'])->name('force-monthly');
            Route::get('/monthly-status', [CommissionTriggerController::class, 'getMonthlyStatus'])->name('monthly-status');
            Route::post('/trigger-package', [CommissionTriggerController::class, 'triggerPackageCommission'])->name('trigger-package');
            Route::post('/trigger-retail', [CommissionTriggerController::class, 'triggerRetailCommission'])->name('trigger-retail');
            Route::get('/periods', [CommissionTriggerController::class, 'getPeriods'])->name('periods.list');
            Route::post('/periods/{periodId}/step', [CommissionTriggerController::class, 'processPeriodStep'])->name('periods.step');
        });
        Route::get('/commissions', [AdminCommissionController::class, 'index'])->name('commissions');

        // ============================================================
        // WALLETS ADMIN
        // ============================================================
        Route::prefix('wallets')->name('wallets.')->group(function () {
            Route::get('/', [AdminWalletController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminWalletController::class, 'show'])->name('show');
            Route::post('/{id}/adjust', [AdminWalletController::class, 'adjust'])->name('adjust');
            Route::get('/{id}/toggle-status', [AdminWalletController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/export', [AdminWalletController::class, 'export'])->name('export');
            Route::get('/stats', [AdminWalletController::class, 'stats'])->name('stats');
        });
        Route::get('/wallets', [AdminWalletController::class, 'index'])->name('wallets');

        // ============================================================
        // WITHDRAWALS ADMIN
        // ============================================================
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', [AdminWithdrawalController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminWithdrawalController::class, 'show'])->name('show');
            Route::post('/{id}/process', [AdminWithdrawalController::class, 'process'])->name('process');
            Route::post('/{id}/approve', [AdminWithdrawalController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [AdminWithdrawalController::class, 'reject'])->name('reject');
            Route::post('/batch-approve', [AdminWithdrawalController::class, 'batchApprove'])->name('batch-approve');
            Route::get('/export', [AdminWithdrawalController::class, 'export'])->name('export');
            Route::get('/stats', [AdminWithdrawalController::class, 'stats'])->name('stats');
        });
        Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals');

        // ============================================================
        // RANKS ADMIN
        // ============================================================
        Route::prefix('ranks')->name('ranks.')->group(function () {
            Route::get('/', [AdminRankController::class, 'index'])->name('index');
            Route::get('/create', [AdminRankController::class, 'create'])->name('create');
            Route::post('/', [AdminRankController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AdminRankController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminRankController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminRankController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/toggle-status', [AdminRankController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/reassign-all', [AdminRankController::class, 'reassignAll'])->name('reassign-all');
            Route::post('/{id}/reassign-user', [AdminRankController::class, 'reassignUser'])->name('reassign-user');

            Route::prefix('history')->name('history.')->group(function () {
                Route::get('/', [AdminRankController::class, 'history'])->name('index');
                Route::get('/export', [AdminRankController::class, 'exportHistory'])->name('export');
            });
            Route::get('/history', [AdminRankController::class, 'history'])->name('history');
        });
        Route::get('/ranks', [AdminRankController::class, 'index'])->name('ranks');

        // ============================================================
        // KYC ADMIN
        // ============================================================
        Route::prefix('kyc')->name('kyc.')->group(function () {
            Route::get('/', [AdminKycController::class, 'index'])->name('index');
            Route::post('/{id}/verify', [AdminKycController::class, 'verify'])->name('verify');
            Route::post('/{id}/reject', [AdminKycController::class, 'reject'])->name('reject');
            Route::get('/export', [AdminKycController::class, 'export'])->name('export');
        });
        Route::get('/kyc', [AdminKycController::class, 'index'])->name('kyc');

        // ============================================================
        // REPORTS ADMIN
        // ============================================================
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AdminReportController::class, 'index'])->name('index');
            Route::get('/sales', [AdminReportController::class, 'sales'])->name('sales');
            Route::get('/commissions', [AdminReportController::class, 'commissions'])->name('commissions');
            Route::get('/users', [AdminReportController::class, 'users'])->name('users');
            Route::get('/withdrawals', [AdminReportController::class, 'withdrawals'])->name('withdrawals');
            Route::get('/export', [AdminReportController::class, 'export'])->name('export');
            Route::get('/pdf/{type}', [AdminReportController::class, 'exportPdf'])->name('pdf');
        });
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports');

        // ============================================================
        // SETTINGS ADMIN
        // ============================================================
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [AdminSettingController::class, 'index'])->name('index');
            Route::put('/', [AdminSettingController::class, 'update'])->name('update');

            Route::prefix('commission')->name('commission.')->group(function () {
                Route::get('/', [AdminSettingController::class, 'commission'])->name('index');
                Route::put('/', [AdminSettingController::class, 'updateCommission'])->name('update');
            });

            Route::prefix('payment')->name('payment.')->group(function () {
                Route::get('/', [AdminSettingController::class, 'payment'])->name('index');
                Route::put('/', [AdminSettingController::class, 'updatePayment'])->name('update');
            });

            Route::get('/commission', [AdminSettingController::class, 'commission'])->name('commission');
            Route::get('/payment', [AdminSettingController::class, 'payment'])->name('payment');
            Route::put('/commission/update', [AdminSettingController::class, 'updateCommission'])->name('update-commission');
            Route::put('/payment/update', [AdminSettingController::class, 'updatePayment'])->name('update-payment');

            Route::post('/clear-cache', [AdminSettingController::class, 'clearCache'])->name('clear-cache');
            Route::post('/optimize', [AdminSettingController::class, 'optimize'])->name('optimize');
            Route::post('/toggle-maintenance', [AdminSettingController::class, 'toggleMaintenance'])->name('toggle-maintenance');
            Route::get('/system-info', [AdminSettingController::class, 'systemInfo'])->name('system-info');
        });
        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings');

        // ============================================================
        // ADMIN ACTIVATIONS
        // ============================================================
        Route::prefix('activations')->name('activations.')->group(function () {
            Route::get('/', [AdminActivationController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminActivationController::class, 'show'])->name('show');
            Route::post('/{id}/generate-code', [AdminActivationController::class, 'generateCodeWithPackage'])->name('generate-code');
            Route::post('/{id}/activate', [AdminActivationController::class, 'activateManually'])->name('activate');
            Route::post('/{id}/send-code', [AdminActivationController::class, 'sendCode'])->name('send-code');
            Route::post('/{id}/activate-with-package', [AdminActivationController::class, 'activateWithPackage'])->name('activate-with-package');
            Route::get('/{id}/check-balance', [AdminActivationController::class, 'checkUserBalance'])->name('check-balance');
        });

        // ============================================================
        // RAPPORTS POS ADMIN
        // ============================================================
        Route::prefix('pos-reports')->name('pos-reports.')->group(function () {
            Route::get('/', [AdminPOSReportController::class, 'index'])->name('index');
            Route::get('/sales', [AdminPOSReportController::class, 'sales'])->name('sales');
            Route::get('/commissions', [AdminPOSReportController::class, 'commissions'])->name('commissions');
            Route::get('/export', [AdminPOSReportController::class, 'export'])->name('export');
            Route::get('/export-pdf', [AdminPOSReportController::class, 'exportPdf'])->name('export-pdf');
        });

    }); // FIN DU GROUPE ADMIN

// ============================================================
// AUTHENTIFICATION
// ============================================================
require __DIR__ . '/auth.php';

// ============================================================
// FALLBACK ROUTE
// ============================================================
Route::fallback(function () {
    return redirect()->route('home');
})->name('fallback');

// ============================================================
// PAGES LEGALES (en dehors du groupe admin)
// ============================================================
Route::get('/mentions-legales', [LegalController::class, 'legal'])->name('legal.mentions');
Route::get('/confidentialite', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/conditions-generales', [LegalController::class, 'terms'])->name('legal.terms');