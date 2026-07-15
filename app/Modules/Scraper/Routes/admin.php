<?php

use App\Modules\Scraper\Http\Controllers\Admin\ScraperRunController;
use App\Modules\Scraper\Http\Controllers\Admin\ScraperSourceController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['web', 'auth', 'admin'])->group(function () {
    Route::get('scrapers/sources', [ScraperSourceController::class, 'index'])->name('admin.scrapers.sources.index');
    Route::get('scrapers/sources/create', [ScraperSourceController::class, 'create'])->name('admin.scrapers.sources.create');
    Route::post('scrapers/sources', [ScraperSourceController::class, 'store'])->name('admin.scrapers.sources.store');
    Route::get('scrapers/sources/{source}/edit', [ScraperSourceController::class, 'edit'])->name('admin.scrapers.sources.edit');
    Route::put('scrapers/sources/{source}', [ScraperSourceController::class, 'update'])->name('admin.scrapers.sources.update');
    Route::post('scrapers/sources/{source}/toggle', [ScraperSourceController::class, 'toggle'])->name('admin.scrapers.sources.toggle');

    Route::get('scrapers/runs', [ScraperRunController::class, 'index'])->name('admin.scrapers.runs.index');
    Route::get('scrapers/runs/{run}', [ScraperRunController::class, 'show'])->name('admin.scrapers.runs.show');
    Route::get('scrapers/runs/{run}/logs', [ScraperRunController::class, 'log'])->name('admin.scrapers.runs.logs');
});
