<?php

use App\Modules\API\v1\Controllers\AdmissionApiController;
use App\Modules\API\v1\Controllers\FeeApiController;
use App\Modules\API\v1\Controllers\InstituteApiController;
use App\Modules\API\v1\Controllers\LocationApiController;
use App\Modules\API\v1\Controllers\SearchApiController;
use App\Modules\API\v1\Controllers\TaxonomyApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('api')->group(function () {
    // Institutes
    Route::get('institutes', [InstituteApiController::class, 'index'])->name('api.v1.institutes.index');
    Route::get('institutes/{institute}', [InstituteApiController::class, 'show'])->name('api.v1.institutes.show');

    // Fees
    Route::get('institutes/{institute}/fees', [FeeApiController::class, 'index'])->name('api.v1.institutes.fees');
    Route::get('institutes/{institute}/fees/history', [FeeApiController::class, 'history'])->name('api.v1.institutes.fees.history');

    // Admissions
    Route::get('institutes/{institute}/admissions', [AdmissionApiController::class, 'forInstitute'])->name('api.v1.institutes.admissions');
    Route::get('admissions', [AdmissionApiController::class, 'index'])->name('api.v1.admissions.index');

    // Search
    Route::get('search', [SearchApiController::class, 'index'])->name('api.v1.search.index');
    Route::get('search/autocomplete', [SearchApiController::class, 'autocomplete'])->name('api.v1.search.autocomplete');

    // Locations
    Route::get('locations/divisions', [LocationApiController::class, 'divisions'])->name('api.v1.locations.divisions');
    Route::get('locations/districts', [LocationApiController::class, 'districts'])->name('api.v1.locations.districts');
    Route::get('locations/upazilas', [LocationApiController::class, 'upazilas'])->name('api.v1.locations.upazilas');

    // Taxonomies
    Route::get('taxonomies/types', [TaxonomyApiController::class, 'types'])->name('api.v1.taxonomies.types');
    Route::get('taxonomies/categories', [TaxonomyApiController::class, 'categories'])->name('api.v1.taxonomies.categories');
    Route::get('taxonomies/curriculums', [TaxonomyApiController::class, 'curriculums'])->name('api.v1.taxonomies.curriculums');
    Route::get('taxonomies/boards', [TaxonomyApiController::class, 'boards'])->name('api.v1.taxonomies.boards');
    Route::get('taxonomies/programs', [TaxonomyApiController::class, 'programs'])->name('api.v1.taxonomies.programs');
    Route::get('taxonomies/facilities', [TaxonomyApiController::class, 'facilities'])->name('api.v1.taxonomies.facilities');
    Route::get('taxonomies/fee-types', [TaxonomyApiController::class, 'feeTypes'])->name('api.v1.taxonomies.fee-types');
});
