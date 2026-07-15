<?php

use App\Modules\Institute\Http\Controllers\Admin\InstituteController;
use App\Modules\Institute\Http\Controllers\Admin\ReviewModerationController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['web', 'auth', 'admin'])->group(function () {
    Route::resource('institutes', InstituteController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->names([
            'index' => 'admin.institutes.index',
            'create' => 'admin.institutes.create',
            'store' => 'admin.institutes.store',
            'edit' => 'admin.institutes.edit',
            'update' => 'admin.institutes.update',
        ]);

    Route::post('institutes/{institute}/publish', [InstituteController::class, 'publish'])
        ->name('admin.institutes.publish');
    Route::post('institutes/{institute}/archive', [InstituteController::class, 'archive'])
        ->name('admin.institutes.archive');
    Route::post('institutes/bulk/publish', [InstituteController::class, 'bulkPublish'])
        ->name('admin.institutes.bulk-publish');
    Route::post('institutes/bulk/archive', [InstituteController::class, 'bulkArchive'])
        ->name('admin.institutes.bulk-archive');

    // Review Moderation Queue
    Route::get('reviews', [ReviewModerationController::class, 'index'])
        ->name('admin.reviews.index');
    Route::post('reviews/{review}/approve', [ReviewModerationController::class, 'approve'])
        ->name('admin.reviews.approve');
    Route::post('reviews/{review}/reject', [ReviewModerationController::class, 'reject'])
        ->name('admin.reviews.reject');
});
