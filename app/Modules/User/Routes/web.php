<?php

use App\Modules\User\Http\Controllers\AlertController;
use App\Modules\User\Http\Controllers\DashboardController;
use App\Modules\User\Http\Controllers\FavoriteController;
use App\Modules\User\Http\Controllers\LoginController;
use App\Modules\User\Http\Controllers\RegisterController;
use App\Modules\User\Http\Controllers\SavedComparisonController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    // Guest Auth Routes
    Route::middleware('guest')->group(function () {
        Route::get('register', [RegisterController::class, 'show'])->name('register');
        Route::post('register', [RegisterController::class, 'register']);

        Route::get('login', [LoginController::class, 'show'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
    });

    // Authenticated User Routes
    Route::middleware(['auth', 'not_admin'])->group(function () {
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');

        Route::get('dashboard', [DashboardController::class, 'show'])->name('dashboard');

        // Favorites
        Route::post('favorites', [FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('favorites/{id}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

        // Comparisons
        Route::post('comparisons', [SavedComparisonController::class, 'store'])->name('comparisons.store');
        Route::delete('comparisons/{id}', [SavedComparisonController::class, 'destroy'])->name('comparisons.destroy');

        // Alerts
        Route::post('alerts', [AlertController::class, 'store'])->name('alerts.store');
        Route::delete('alerts/{id}', [AlertController::class, 'destroy'])->name('alerts.destroy');
    });
});
