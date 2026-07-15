<?php

use App\Modules\Comparison\Http\Controllers\ComparisonController;
use Illuminate\Support\Facades\Route;

Route::get('/compare/{slugs?}', [ComparisonController::class, 'show'])->name('compare.show');
