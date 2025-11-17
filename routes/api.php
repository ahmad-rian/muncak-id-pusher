<?php

use App\Http\Controllers\API\GunungController;
use App\Http\Controllers\API\RuteController;
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
});
