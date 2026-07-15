<?php

use App\Modules\Comparison\Http\Controllers\ComparisonController;
use Illuminate\Support\Facades\Route;

Route::get('/api/v1/compare', [ComparisonController::class, 'api'])->name('api.compare');
