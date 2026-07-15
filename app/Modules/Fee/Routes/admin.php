<?php

use App\Modules\Fee\Http\Controllers\Admin\FeeStructureController;
use App\Modules\Fee\Http\Controllers\Admin\FeeTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('fees/types', [FeeTypeController::class, 'index'])->name('admin.fees.types.index');
    Route::post('fees/types', [FeeTypeController::class, 'store'])->name('admin.fees.types.store');
    Route::put('fees/types/{type}', [FeeTypeController::class, 'update'])->name('admin.fees.types.update');
    Route::delete('fees/types/{type}', [FeeTypeController::class, 'destroy'])->name('admin.fees.types.destroy');

    Route::get('fees', [FeeStructureController::class, 'index'])->name('admin.fees.index');
    Route::get('fees/create', [FeeStructureController::class, 'create'])->name('admin.fees.create');
    Route::post('fees', [FeeStructureController::class, 'store'])->name('admin.fees.store');
    Route::get('fees/{fee}/edit', [FeeStructureController::class, 'edit'])->name('admin.fees.edit');
    Route::put('fees/{fee}', [FeeStructureController::class, 'update'])->name('admin.fees.update');
    Route::delete('fees/{fee}', [FeeStructureController::class, 'destroy'])->name('admin.fees.destroy');
    Route::post('fees/{fee}/moderate', [FeeStructureController::class, 'moderate'])->name('admin.fees.moderate');
    Route::get('fees/{fee}/history', [FeeStructureController::class, 'history'])->name('admin.fees.history');
});
