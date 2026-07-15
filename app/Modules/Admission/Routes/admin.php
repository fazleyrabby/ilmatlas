<?php

use App\Modules\Admission\Http\Controllers\Admin\AdmissionCircularController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('admissions', [AdmissionCircularController::class, 'index'])->name('admin.admissions.index');
    Route::get('admissions/create', [AdmissionCircularController::class, 'create'])->name('admin.admissions.create');
    Route::post('admissions', [AdmissionCircularController::class, 'store'])->name('admin.admissions.store');
    Route::get('admissions/{circular}/edit', [AdmissionCircularController::class, 'edit'])->name('admin.admissions.edit');
    Route::put('admissions/{circular}', [AdmissionCircularController::class, 'update'])->name('admin.admissions.update');
    Route::delete('admissions/{circular}', [AdmissionCircularController::class, 'destroy'])->name('admin.admissions.destroy');
});
