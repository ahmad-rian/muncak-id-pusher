<?php

use App\Http\Controllers\API\GunungController;
use App\Http\Controllers\API\RuteController;
use App\Http\Controllers\API\TrailClassificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->group(function () {
    Route::prefix('gunung')->group(function () {
        Route::get('', [GunungController::class, 'index'])->name('api.gunung.index');
        Route::get('{id}', [GunungController::class, 'show'])->name('api.gunung.show');
    });

    Route::prefix('rute')->group(function () {
        Route::get('', [RuteController::class, 'index'])->name('api.rute.index');
        Route::get('{id}.geojson', [RuteController::class, 'geojson'])->name('api.rute.geojson');
        Route::get('{id}', [RuteController::class, 'show'])->name('api.rute.show');
    });

    // Trail classification endpoints
    Route::prefix('classifications')->group(function () {
        Route::post('stream/{streamId}/process', [TrailClassificationController::class, 'processFrame'])
            ->name('api.classifications.process');
        Route::post('stream/{streamId}/classify', [TrailClassificationController::class, 'processFrame'])
            ->name('api.classifications.classify'); // Alias for easier triggering
        Route::get('stream/{streamId}/latest', [TrailClassificationController::class, 'getLatest'])
            ->name('api.classifications.latest');
        Route::get('trail/{trailId}', [TrailClassificationController::class, 'getByTrail'])
            ->name('api.classifications.by-trail');
    });
});
