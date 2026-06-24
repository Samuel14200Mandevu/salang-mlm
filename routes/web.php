<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Onboarding
Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
Route::get('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profil
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
});

// Authentification
require __DIR__.'/auth.php';

// Routes de profil
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes des packages
Route::middleware(['auth'])->group(function () {
    Route::get('/packages', [App\Http\Controllers\PackageController::class, 'index'])->name('packages.index');
    Route::post('/packages/buy', [App\Http\Controllers\PackageController::class, 'buy'])->name('packages.buy');
    Route::post('/packages/upgrade', [App\Http\Controllers\PackageController::class, 'upgrade'])->name('packages.upgrade');
});
