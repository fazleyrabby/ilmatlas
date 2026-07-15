<?php

use App\Modules\Fee\Http\Controllers\CommunityFeeController;
use App\Modules\Institute\Http\Controllers\InstitutePublicController;
use App\Modules\Institute\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/institutes', [InstitutePublicController::class, 'index'])->name('institutes.index');
    Route::get('/institutes/type/{type}', [InstitutePublicController::class, 'byType'])->name('institutes.by.type');
    Route::get('/institutes/district/{district}', [InstitutePublicController::class, 'byDistrict'])->name('institutes.by.district');
    Route::get('/institutes/{type}/{district}', [InstitutePublicController::class, 'byTypeAndDistrict'])->name('institutes.pseo');
    Route::get('/institutes/{institute}', [InstitutePublicController::class, 'show'])->name('institutes.show');

    Route::middleware('auth')->group(function () {
        Route::post('/institutes/{institute}/reviews', [ReviewController::class, 'store'])->name('institutes.reviews.store');
        Route::post('/institutes/{institute}/fees/submit', [CommunityFeeController::class, 'store'])->name('institutes.fees.submit');
    });
});
