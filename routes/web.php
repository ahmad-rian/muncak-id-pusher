<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\JelajahController;
use App\Http\Controllers\RuteController;
use App\Http\Controllers\UserController;

require __DIR__ . '/auth.php';

// Broadcasting routes for Pusher
Broadcast::routes(['middleware' => ['web']]);

Route::get('/', [IndexController::class, 'index'])->name('index');

Route::prefix('jelajah')->group(function () {
    Route::get('', [JelajahController::class, 'index'])->name('jelajah.index');
});

Route::prefix('jalur-pendakian')->group(function () {
    Route::prefix('{slug}')->group(function () {
        Route::get('', [RuteController::class, 'slug'])->name('jalur-pendakian.slug');
        Route::get('prediksi-cuaca', [RuteController::class, 'prediksiCuaca'])->name('jalur-pendakian.slug.prediksi-cuaca');
        Route::get('segmentasi', [RuteController::class, 'segmentasi'])->name('jalur-pendakian.slug.segmentasi');
    });
});

Route::prefix('ulasan')->group(function () {
    Route::post('rute/{ruteSlug}', [CommentController::class, 'store'])
        ->name('ulasan.store')
        ->middleware(['auth']);
});

Route::prefix('profile')->middleware(['auth'])->group(function () {
    Route::get('', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('ulasan', [ProfileController::class, 'ulasan'])->name('profile.ulasan');
    Route::delete('ulasan/{id}/delete', [ProfileController::class, 'ulasanDelete'])->name('profile.ulasan.ulasan.delete');
});

Route::prefix('artikel')->group(function () {
    Route::get('', [BlogController::class, 'index'])->name('blog.index');
    Route::get('{slug}', [BlogController::class, 'slug'])->name('blog.slug');
});

// Public routes for viewing live streams
Route::prefix('live-cam')->group(function () {
    Route::get('', [\App\Http\Controllers\LiveCamController::class, 'index'])->name('live-cam.index');

    // SPECIFIC ROUTES FIRST (before wildcard!)
    Route::post('{stream:slug}/chat', [\App\Http\Controllers\LiveCamController::class, 'sendChat'])->name('live-cam.chat');
    Route::get('{stream:slug}/chat-history', [\App\Http\Controllers\LiveCamController::class, 'getChatHistory'])->name('live-cam.chat-history');
    Route::get('{stream:slug}/quality', [\App\Http\Controllers\LiveCamController::class, 'getQuality'])->name('live-cam.quality');
    Route::post('{stream:slug}/viewer-count', [\App\Http\Controllers\LiveCamController::class, 'updateViewerCount'])->name('live-cam.viewer-count');

    // WebRTC Signaling routes (deprecated - kept for backward compatibility)
    Route::post('{stream:slug}/viewer-ready', [\App\Http\Controllers\LiveCamController::class, 'viewerReady'])->name('live-cam.viewer-ready');
    Route::post('{stream:slug}/send-signal', [\App\Http\Controllers\LiveCamController::class, 'sendSignal'])->name('live-cam.send-signal');

    // MSE Chunked Streaming routes (NEW - scalable live streaming)
    Route::get('{stream:slug}/chunk/{index}', [\App\Http\Controllers\LiveCamController::class, 'getChunk'])->name('live-cam.get-chunk');
    Route::get('{stream:slug}/status', [\App\Http\Controllers\LiveCamController::class, 'getStatus'])->name('live-cam.status');

    // HLS Streaming routes (NEW - for basecamp livestreaming)
    Route::get('{stream:slug}/playlist.m3u8', [\App\Http\Controllers\LiveCamController::class, 'getHLSPlaylist'])->name('live-cam.hls-playlist');
    Route::get('{stream:slug}/{segmentName}', [\App\Http\Controllers\LiveCamController::class, 'getHLSSegment'])->name('live-cam.hls-segment');

    // Mirror state broadcast
    Route::post('{stream:slug}/mirror-state', [\App\Http\Controllers\LiveCamController::class, 'updateMirrorState'])->name('live-cam.mirror-state');

    // Thumbnail upload
    Route::post('{stream:slug}/thumbnail', [\App\Http\Controllers\LiveCamController::class, 'uploadThumbnail'])->name('live-cam.thumbnail');

    // LiveKit token for viewer
    Route::get('{stream:slug}/livekit/token', [\App\Http\Controllers\LiveCamController::class, 'getLiveKitViewerToken'])->name('live-cam.livekit-token');

    // WILDCARD ROUTE LAST! (matches everything else)
    Route::get('{stream:slug}', [\App\Http\Controllers\LiveCamController::class, 'show'])->name('live-cam.show');
});

Route::get('sitemap.xml', [IndexController::class, 'sitemap'])->name('sitemap');

/**
 * API --- START
 */
Route::prefix('api')->group(function () {
    Route::prefix('jelajah')->group(function () {
        Route::get('rute', [JelajahController::class, 'apiRute'])->name('api.jelajah.rute');
    });

    Route::prefix('rute')->group(function () {
        Route::prefix('{id}')->group(function () {
            Route::get('rute', [RuteController::class, 'apiRute'])->name('api.rute.rute');
            // Route::get('fitting-kalori', [RuteController::class, 'apiFittingKalori'])->name('api.rute.fitting-kalori');
            // Route::get('prediksi-cuaca', [RuteController::class, 'apiPrediksiCuaca'])->name('api.rute.prediksi-cuaca');
            Route::get('segmentasi', [RuteController::class, 'apiSegmentasi'])->name('api.rute.segmentasi');
        });
    });

    Route::prefix('ulasan')->group(function () {
        Route::get('rute/{ruteId}', [CommentController::class, 'apiIndex'])->name('api.rute.ulasan.index');
    });

    Route::prefix('profile')->middleware(['auth'])->group(function () {
        Route::get('ulasan', [ProfileController::class, 'apiUlasan'])->name('api.profile.ulasan');
        Route::delete('ulasan/{id}/delete', [ProfileController::class, 'apiUlasanDelete'])->name('api.profile.ulasan.delete');
    });

    Route::prefix('user')->group(function () {
        Route::post('toggle-theme', [UserController::class, 'apiToggleTheme'])->name('user.toggle-theme');
    });
});

require __DIR__ . '/api.php';

/**
 * API --- END
 */

require __DIR__ . '/admin.php';

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });
