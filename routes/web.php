<?php
// routes/web.php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\EmailCheckController;
use App\Http\Controllers\Auth\SponsorCheckController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingController;
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
use Illuminate\Support\Facades\Route;

// ============================================================
// ROUTES PUBLIQUES
// ============================================================

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Sponsor & Email check (AJAX)
Route::get('/check-email', [EmailCheckController::class, 'check'])->name('check.email');
Route::get('/check-sponsor', [SponsorCheckController::class, 'check'])->name('check.sponsor');

// Onboarding
Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
Route::get('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');

// Produits (publics)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// Paiements (publics)
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');

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
Route::prefix('webhook')->withoutMiddleware(['csrf'])->group(function () {
    Route::post('/crypto', [WebhookController::class, 'crypto'])->name('webhook.crypto');
    Route::post('/mobile-money', [WebhookController::class, 'mobileMoney'])->name('webhook.mobile-money');
    Route::post('/payment', [WebhookController::class, 'payment'])->name('webhook.payment');
});

// ============================================================
// WISHLIST
// ============================================================
Route::middleware(['auth', 'active'])->prefix('wishlist')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/add/{product}', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::delete('/remove/{product}', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::post('/toggle/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
});

// ============================================================
// NOTIFICATIONS
// ============================================================
Route::middleware(['auth', 'active'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
});

// ============================================================
// ROUTES AUTHENTIFIEES (Utilisateur)
// ============================================================
Route::middleware(['auth', 'active'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================================
    // PROFIL
    // ============================================================
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
        Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // ============================================================
    // ABONNEMENTS (PACKAGES)
    // ============================================================
    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [UserPackageController::class, 'index'])->name('subscriptions.index');
        Route::post('/buy', [UserPackageController::class, 'buy'])->name('subscriptions.buy');
        Route::post('/upgrade', [UserPackageController::class, 'upgrade'])->name('subscriptions.upgrade');
    });

    // ============================================================
    // RÉSEAU (NETWORK/GENEALOGY)
    // ============================================================
    Route::prefix('network')->group(function () {
        Route::get('/', [NetworkController::class, 'index'])->name('network.index');
        Route::get('/tree', [NetworkController::class, 'treeData'])->name('network.tree');
        Route::get('/downlines', [NetworkController::class, 'downlines'])->name('network.downlines');
        Route::get('/search', [NetworkController::class, 'search'])->name('network.search');
        Route::get('/stats', [NetworkController::class, 'apiStats'])->name('network.stats');
    });

    // ============================================================
    // GRADES (RANKS)
    // ============================================================
    Route::prefix('rank')->group(function () {
        Route::get('/', [RankController::class, 'index'])->name('rank.index');
        Route::get('/history', [RankController::class, 'history'])->name('rank.history');
        Route::get('/leaderboard', [RankController::class, 'leaderboard'])->name('rank.leaderboard');
        Route::get('/progress', [RankController::class, 'apiProgress'])->name('rank.progress');
    });

    // ============================================================
    // PORTEFEUILLE (WALLET)
    // ============================================================
    Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('wallet.index');
        Route::get('/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');
        Route::get('/export', [WalletController::class, 'export'])->name('wallet.export');
        Route::get('/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
        Route::post('/deposit', [WalletController::class, 'storeDeposit'])->name('wallet.deposit.store');
        Route::get('/balance', [WalletController::class, 'apiBalance'])->name('wallet.balance');
    });

    // ============================================================
    // RETRAITS (WITHDRAWALS)
    // ============================================================
    Route::prefix('withdrawal')->group(function () {
        Route::get('/', [WithdrawalController::class, 'index'])->name('withdrawal.index');
        Route::post('/', [WithdrawalController::class, 'store'])->name('withdrawal.store');
        Route::get('/{id}', [WithdrawalController::class, 'show'])->name('withdrawal.show');
        Route::post('/{id}/cancel', [WithdrawalController::class, 'cancel'])->name('withdrawal.cancel');
    });

    // ============================================================
    // KYC
    // ============================================================
    Route::prefix('kyc')->group(function () {
        Route::get('/', [KycController::class, 'index'])->name('kyc.index');
        Route::get('/create', [KycController::class, 'create'])->name('kyc.create');
        Route::post('/', [KycController::class, 'store'])->name('kyc.store');
        Route::get('/status', [KycController::class, 'getStatus'])->name('kyc.status');
    });

    // ============================================================
    // RAPPORTS
    // ============================================================
    Route::prefix('report')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('report.index');
        Route::get('/earnings', [ReportController::class, 'earnings'])->name('report.earnings');
        Route::get('/network', [ReportController::class, 'network'])->name('report.network');
        Route::get('/export', [ReportController::class, 'export'])->name('report.export');
    });

    // ============================================================
    // PANIER (CART)
    // ============================================================
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('cart.index');
        Route::get('/count', [CartController::class, 'count'])->name('cart.count');
        Route::post('/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/add-package', [CartController::class, 'addPackage'])->name('cart.add-package');
        Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
        Route::put('/update', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/clear', [CartController::class, 'clear'])->name('cart.clear');
        Route::post('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    });

    // ============================================================
    // PAIEMENTS
    // ============================================================
    Route::post('/payment/process', [PaymentController::class, 'process'])->name('payment.process');

    // ============================================================
    // COMMANDES (ORDERS)
    // ============================================================
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/invoice/{order}', [OrderController::class, 'invoice'])->name('orders.invoice');
    });

    // ============================================================
    // COMMISSIONS
    // ============================================================
    Route::prefix('commissions')->group(function () {
        Route::get('/', [CommissionController::class, 'index'])->name('commissions.index');
        Route::get('/stats', [CommissionController::class, 'stats'])->name('commissions.stats');
        Route::get('/dashboard', [CommissionDashboardController::class, 'index'])->name('commissions.dashboard');
        Route::get('/export', [CommissionController::class, 'export'])->name('commissions.export');
        Route::get('/levels', [CommissionController::class, 'getLevelCommissions'])->name('commissions.levels');
        Route::get('/pdf', [CommissionController::class, 'pdf'])->name('commissions.pdf');
        Route::get('/{id}', [CommissionController::class, 'show'])->name('commissions.show');
    });
});

// ============================================================
// ROUTES ADMIN
// ============================================================
Route::prefix('admin')
    ->middleware(['auth', 'active', 'admin'])
    ->name('admin.')
    ->group(function () {

        // Dashboard Admin
        Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [App\Http\Controllers\Admin\DashboardController::class, 'apiStats'])->name('dashboard.stats');
        Route::get('/dashboard/chart-data', [App\Http\Controllers\Admin\DashboardController::class, 'chartData'])->name('dashboard.chart');

        // ============================================================
        // USERS ADMIN
        // ============================================================
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{id}/assign-package', [App\Http\Controllers\Admin\UserController::class, 'assignPackage'])->name('assign-package');
            Route::get('/{id}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('reset-password');
            Route::get('/search', [App\Http\Controllers\Admin\UserController::class, 'search'])->name('search');
            Route::get('/export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('export');
            Route::post('/import', [App\Http\Controllers\Admin\UserController::class, 'import'])->name('import');
        });

        // ✅ ALIAS POUR LA ROUTE INDEX DES USERS
        Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users');

        // ============================================================
        // PACKAGES ADMIN
        // ============================================================
        Route::prefix('packages')->name('packages.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\PackageController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\PackageController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\PackageController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\PackageController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Admin\PackageController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\PackageController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/toggle-status', [App\Http\Controllers\Admin\PackageController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{id}/duplicate', [App\Http\Controllers\Admin\PackageController::class, 'duplicate'])->name('duplicate');
        });

        // ✅ ALIAS POUR LA ROUTE INDEX DES PACKAGES
        Route::get('/packages', [App\Http\Controllers\Admin\PackageController::class, 'index'])->name('packages');

        // ============================================================
        // PRODUCTS ADMIN
        // ============================================================
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/toggle-status', [App\Http\Controllers\Admin\ProductController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{id}/toggle-featured', [App\Http\Controllers\Admin\ProductController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::delete('/{id}/gallery', [App\Http\Controllers\Admin\ProductController::class, 'removeGalleryImage'])->name('remove-gallery');
        });

        // ✅ ALIAS POUR LA ROUTE INDEX DES PRODUCTS
        Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products');

        // ============================================================
        // COMMISSIONS ADMIN
        // ============================================================
        Route::prefix('commissions')->name('commissions.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('index');
            Route::get('/{id}', [App\Http\Controllers\Admin\CommissionController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [App\Http\Controllers\Admin\CommissionController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [App\Http\Controllers\Admin\CommissionController::class, 'reject'])->name('reject');
            Route::post('/batch-approve', [App\Http\Controllers\Admin\CommissionController::class, 'batchApprove'])->name('batch-approve');
            Route::post('/recalculate', [CommissionTriggerController::class, 'recalculateAll'])->name('recalculate');
            Route::get('/export', [App\Http\Controllers\Admin\CommissionController::class, 'export'])->name('export');
            Route::get('/stats', [App\Http\Controllers\Admin\CommissionController::class, 'stats'])->name('stats');
            Route::get('/network/{id}', [App\Http\Controllers\Admin\CommissionController::class, 'viewNetwork'])->name('network');

            // Commission Periods
            Route::prefix('periods')->name('periods.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\CommissionPeriodController::class, 'index'])->name('index');
                Route::get('/{id}', [App\Http\Controllers\Admin\CommissionPeriodController::class, 'show'])->name('show');
                Route::post('/create', [App\Http\Controllers\Admin\CommissionPeriodController::class, 'create'])->name('create');
                Route::post('/{id}/process', [App\Http\Controllers\Admin\CommissionPeriodController::class, 'process'])->name('process');
                Route::post('/{id}/close', [App\Http\Controllers\Admin\CommissionPeriodController::class, 'close'])->name('close');
                Route::delete('/{id}', [App\Http\Controllers\Admin\CommissionPeriodController::class, 'destroy'])->name('destroy');
                Route::get('/{id}/export', [App\Http\Controllers\Admin\CommissionPeriodController::class, 'export'])->name('export');
                Route::delete('/{period}/clean', [CommissionTriggerController::class, 'cleanPeriod'])->name('clean');
            });

            // Commission Triggers
            Route::post('/force-monthly', [CommissionTriggerController::class, 'forceMonthlyProcessing'])->name('force-monthly');
            Route::get('/monthly-status', [CommissionTriggerController::class, 'getMonthlyStatus'])->name('monthly-status');
            Route::post('/trigger-package', [CommissionTriggerController::class, 'triggerPackageCommission'])->name('trigger-package');
            Route::post('/trigger-retail', [CommissionTriggerController::class, 'triggerRetailCommission'])->name('trigger-retail');
            Route::get('/periods', [CommissionTriggerController::class, 'getPeriods'])->name('periods.list');
            Route::post('/periods/{periodId}/step', [CommissionTriggerController::class, 'processPeriodStep'])->name('periods.step');
        });

        // ✅ ALIAS POUR LA ROUTE INDEX DES COMMISSIONS
        Route::get('/commissions', [App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commissions');

        // ============================================================
        // WALLETS ADMIN
        // ============================================================
        Route::prefix('wallets')->name('wallets.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\WalletController::class, 'index'])->name('index');
            Route::get('/{id}', [App\Http\Controllers\Admin\WalletController::class, 'show'])->name('show');
            Route::post('/{id}/adjust', [App\Http\Controllers\Admin\WalletController::class, 'adjust'])->name('adjust');
            Route::get('/{id}/toggle-status', [App\Http\Controllers\Admin\WalletController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/export', [App\Http\Controllers\Admin\WalletController::class, 'export'])->name('export');
            Route::get('/stats', [App\Http\Controllers\Admin\WalletController::class, 'stats'])->name('stats');
        });

        // ✅ ALIAS POUR LA ROUTE INDEX DES WALLETS
        Route::get('/wallets', [App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallets');

        // ============================================================
        // WITHDRAWALS ADMIN
        // ============================================================
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('index');
            Route::get('/{id}', [App\Http\Controllers\Admin\WithdrawalController::class, 'show'])->name('show');
            Route::post('/{id}/process', [App\Http\Controllers\Admin\WithdrawalController::class, 'process'])->name('process');
            Route::post('/{id}/approve', [App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('reject');
            Route::post('/batch-approve', [App\Http\Controllers\Admin\WithdrawalController::class, 'batchApprove'])->name('batch-approve');
            Route::get('/export', [App\Http\Controllers\Admin\WithdrawalController::class, 'export'])->name('export');
            Route::get('/stats', [App\Http\Controllers\Admin\WithdrawalController::class, 'stats'])->name('stats');
        });

        // ✅ ALIAS POUR LA ROUTE INDEX DES WITHDRAWALS
        Route::get('/withdrawals', [App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals');

        // ============================================================
        // RANKS ADMIN
        // ============================================================
        Route::prefix('ranks')->name('ranks.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\RankController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\RankController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\RankController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\RankController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Admin\RankController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\RankController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/toggle-status', [App\Http\Controllers\Admin\RankController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/reassign-all', [App\Http\Controllers\Admin\RankController::class, 'reassignAll'])->name('reassign-all');
            Route::post('/{id}/reassign-user', [App\Http\Controllers\Admin\RankController::class, 'reassignUser'])->name('reassign-user');

            // Rank History
            Route::prefix('history')->name('history.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\RankController::class, 'history'])->name('index');
                Route::get('/export', [App\Http\Controllers\Admin\RankController::class, 'exportHistory'])->name('export');
            });
        });

        // ✅ ALIAS POUR LA ROUTE INDEX DES RANKS
        Route::get('/ranks', [App\Http\Controllers\Admin\RankController::class, 'index'])->name('ranks');

        // ============================================================
        // KYC ADMIN
        // ============================================================
        Route::prefix('kyc')->name('kyc.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\KycController::class, 'index'])->name('index');
            Route::post('/{id}/verify', [App\Http\Controllers\Admin\KycController::class, 'verify'])->name('verify');
            Route::post('/{id}/reject', [App\Http\Controllers\Admin\KycController::class, 'reject'])->name('reject');
            Route::get('/export', [App\Http\Controllers\Admin\KycController::class, 'export'])->name('export');
        });

        // ✅ ALIAS POUR LA ROUTE INDEX DES KYC
        Route::get('/kyc', [App\Http\Controllers\Admin\KycController::class, 'index'])->name('kyc');

        // ============================================================
        // REPORTS ADMIN
        // ============================================================
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
            Route::get('/sales', [App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('sales');
            Route::get('/commissions', [App\Http\Controllers\Admin\ReportController::class, 'commissions'])->name('commissions');
            Route::get('/users', [App\Http\Controllers\Admin\ReportController::class, 'users'])->name('users');
            Route::get('/withdrawals', [App\Http\Controllers\Admin\ReportController::class, 'withdrawals'])->name('withdrawals');
            Route::get('/export', [App\Http\Controllers\Admin\ReportController::class, 'export'])->name('export');
        });

        // ✅ ALIAS POUR LA ROUTE INDEX DES REPORTS
        Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports');

        // ============================================================
        // SETTINGS ADMIN
        // ============================================================
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('index');
            Route::put('/', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('update');

            Route::prefix('commission')->name('commission.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\SettingController::class, 'commission'])->name('index');
                Route::put('/', [App\Http\Controllers\Admin\SettingController::class, 'updateCommission'])->name('update');
            });

            Route::prefix('payment')->name('payment.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\SettingController::class, 'payment'])->name('index');
                Route::put('/', [App\Http\Controllers\Admin\SettingController::class, 'updatePayment'])->name('update');
            });

            Route::post('/clear-cache', [App\Http\Controllers\Admin\SettingController::class, 'clearCache'])->name('clear-cache');
            Route::post('/optimize', [App\Http\Controllers\Admin\SettingController::class, 'optimize'])->name('optimize');
            Route::post('/toggle-maintenance', [App\Http\Controllers\Admin\SettingController::class, 'toggleMaintenance'])->name('toggle-maintenance');
            Route::get('/system-info', [App\Http\Controllers\Admin\SettingController::class, 'systemInfo'])->name('system-info');
        });

        // ✅ ALIAS POUR LA ROUTE INDEX DES SETTINGS
        Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings');
    });

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