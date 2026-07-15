<?php

use App\Modules\Taxonomy\Http\Controllers\Admin\CategoryController;
use App\Modules\Taxonomy\Http\Controllers\Admin\TaxonomyController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['web', 'auth', 'admin'])->group(function () {
    Route::get('taxonomies', [TaxonomyController::class, 'index'])->name('admin.taxonomies.index');

    Route::post('taxonomies/types', [TaxonomyController::class, 'createType'])->name('admin.taxonomies.types.store');
    Route::delete('taxonomies/types/{type}', [TaxonomyController::class, 'destroyType'])->name('admin.taxonomies.types.destroy');

    Route::get('categories', [CategoryController::class, 'index'])->name('admin.taxonomies.categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('admin.taxonomies.categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->name('admin.taxonomies.categories.store');
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.taxonomies.categories.edit');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('admin.taxonomies.categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('admin.taxonomies.categories.destroy');
});
