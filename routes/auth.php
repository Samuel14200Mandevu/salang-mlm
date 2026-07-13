<?php
// routes/auth.php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\SponsorCheckController;
use App\Http\Controllers\Auth\EmailCheckController;
use Illuminate\Support\Facades\Route;

// ============================================================
// ROUTES GUEST (Non authentifié)
// ============================================================

Route::middleware('guest')->group(function () {

    // Registration
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    // Login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Password Reset
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    // AJAX Checkers (Guest accessible)
    Route::get('check-email', [EmailCheckController::class, 'check'])
        ->name('check.email');

    Route::get('check-sponsor', [SponsorCheckController::class, 'check'])
        ->name('check.sponsor');
});

// ============================================================
// ROUTES AUTH (Authentifié)
// ============================================================

Route::middleware('auth')->group(function () {

    // Email Verification
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Password Confirmation
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Password Update
    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // ============================================================
    // ROUTES SOCIAL AUTH (AJAX)
    // ============================================================

    Route::post('social/store-sponsor', [App\Http\Controllers\Auth\SocialiteController::class, 'storeSponsor'])
        ->name('social.store-sponsor');
});

// ============================================================
// SOCIAL AUTH (Public)
// ============================================================

Route::middleware('guest')->prefix('auth')->name('social.')->group(function () {
    Route::get('{provider}', [App\Http\Controllers\Auth\SocialiteController::class, 'redirect'])
        ->name('redirect');

    Route::get('{provider}/callback', [App\Http\Controllers\Auth\SocialiteController::class, 'callback'])
        ->name('callback');
});