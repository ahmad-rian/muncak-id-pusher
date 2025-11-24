<?php

use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DesaController;
use App\Http\Controllers\Admin\GunungController;
use App\Http\Controllers\Admin\KabupatenKotaController;
use App\Http\Controllers\Admin\KecamatanController;
use App\Http\Controllers\Admin\NegaraController;
use App\Http\Controllers\Admin\PointController;
use App\Http\Controllers\Admin\ProvinsiController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RuteController;
use App\Http\Controllers\Admin\RuteTingkatKesulitanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitorController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('', [DashboardController::class, 'index'])->name('admin.dashboard.index');

    Route::prefix('gunung')->group(function () {
        Route::get('', [GunungController::class, 'index'])->name('admin.gunung.index');
        Route::get('create', [GunungController::class, 'create'])->name('admin.gunung.create');
        Route::post('', [GunungController::class, 'store'])->name('admin.gunung.store');
        Route::get('{gunung}', [GunungController::class, 'show'])->name('admin.gunung.show');
        Route::get('{gunung}/edit', [GunungController::class, 'edit'])->name('admin.gunung.edit');
        Route::put('{gunung}', [GunungController::class, 'update'])->name('admin.gunung.update');
    });

    Route::prefix('rute')->group(function () {
        Route::get('', [RuteController::class, 'index'])->name('admin.rute.index');
        Route::get('create', [RuteController::class, 'create'])->name('admin.rute.create');
        Route::post('', [RuteController::class, 'store'])->name('admin.rute.store');
        Route::get('{rute}', [RuteController::class, 'show'])->name('admin.rute.show');
        Route::get('{rute}/edit', [RuteController::class, 'edit'])->name('admin.rute.edit');
        Route::put('{rute}', [RuteController::class, 'update'])->name('admin.rute.update');
        Route::post('{rute}/point', [RuteController::class, 'storePoint'])->name('admin.rute.store-point');
    });

    Route::prefix('point')->group(function () {
        Route::get('', [PointController::class, 'index'])->name('admin.point.index');
        Route::get('{point}/edit', [PointController::class, 'edit'])->name('admin.point.edit');
        Route::put('{point}', [PointController::class, 'update'])->name('admin.point.update');
    });

    Route::prefix('rute-tingkat-kesulitan')->group(function () {
        Route::get('', [RuteTingkatKesulitanController::class, 'index'])->name('admin.rute-tingkat-kesulitan.index');
        Route::get('create', [RuteTingkatKesulitanController::class, 'create'])->name('admin.rute-tingkat-kesulitan.create');
        Route::post('', [RuteTingkatKesulitanController::class, 'store'])->name('admin.rute-tingkat-kesulitan.store');
        Route::get('{ruteTingkatKesulitan}', [RuteTingkatKesulitanController::class, 'show'])->name('admin.rute-tingkat-kesulitan.show');
        Route::get('{ruteTingkatKesulitan}/edit', [RuteTingkatKesulitanController::class, 'edit'])->name('admin.rute-tingkat-kesulitan.edit');
        Route::put('{ruteTingkatKesulitan}', [RuteTingkatKesulitanController::class, 'update'])->name('admin.rute-tingkat-kesulitan.update');
    });

    Route::prefix('comment')->group(function () {
        Route::get('', [CommentController::class, 'index'])->name('admin.comment.index');
        Route::get('create', [CommentController::class, 'create'])->name('admin.comment.create');
        Route::post('', [CommentController::class, 'store'])->name('admin.comment.store');
        Route::get('{comment}', [CommentController::class, 'show'])->name('admin.comment.show');
        Route::get('{comment}/edit', [CommentController::class, 'edit'])->name('admin.comment.edit');
        Route::put('{comment}', [CommentController::class, 'update'])->name('admin.comment.update');
    });

    Route::prefix('negara')->group(function () {
        Route::get('', [NegaraController::class, 'index'])->name('admin.negara.index');
        Route::get('create', [NegaraController::class, 'create'])->name('admin.negara.create');
        Route::post('', [NegaraController::class, 'store'])->name('admin.negara.store');
        Route::get('{negara}', [NegaraController::class, 'show'])->name('admin.negara.show');
        Route::get('{negara}/edit', [NegaraController::class, 'edit'])->name('admin.negara.edit');
        Route::put('{negara}', [NegaraController::class, 'update'])->name('admin.negara.update');
    });

    Route::prefix('provinsi')->group(function () {
        Route::get('', [ProvinsiController::class, 'index'])->name('admin.provinsi.index');
        Route::get('create', [ProvinsiController::class, 'create'])->name('admin.provinsi.create');
        Route::post('', [ProvinsiController::class, 'store'])->name('admin.provinsi.store');
        Route::get('{provinsi:kode}', [ProvinsiController::class, 'show'])->name('admin.provinsi.show');
        Route::get('{provinsi:kode}/edit', [ProvinsiController::class, 'edit'])->name('admin.provinsi.edit');
        Route::put('{provinsi:kode}', [ProvinsiController::class, 'update'])->name('admin.provinsi.update');
    });

    Route::prefix('kabupaten-kota')->group(function () {
        Route::get('', [KabupatenKotaController::class, 'index'])->name('admin.kabupaten-kota.index');
        Route::get('create', [KabupatenKotaController::class, 'create'])->name('admin.kabupaten-kota.create');
        Route::post('', [KabupatenKotaController::class, 'store'])->name('admin.kabupaten-kota.store');
        Route::get('{kabupatenKota:kode}', [KabupatenKotaController::class, 'show'])->name('admin.kabupaten-kota.show');
        Route::get('{kabupatenKota:kode}/edit', [KabupatenKotaController::class, 'edit'])->name('admin.kabupaten-kota.edit');
        Route::put('{kabupatenKota:kode}', [KabupatenKotaController::class, 'update'])->name('admin.kabupaten-kota.update');
    });

    Route::prefix('kecamatan')->group(function () {
        Route::get('', [KecamatanController::class, 'index'])->name('admin.kecamatan.index');
        Route::get('create', [KecamatanController::class, 'create'])->name('admin.kecamatan.create');
        Route::post('', [KecamatanController::class, 'store'])->name('admin.kecamatan.store');
        Route::get('{kecamatan:kode}', [KecamatanController::class, 'show'])->name('admin.kecamatan.show');
        Route::get('{kecamatan:kode}/edit', [KecamatanController::class, 'edit'])->name('admin.kecamatan.edit');
        Route::put('{kecamatan:kode}', [KecamatanController::class, 'update'])->name('admin.kecamatan.update');
    });

    Route::prefix('desa')->group(function () {
        Route::get('', [DesaController::class, 'index'])->name('admin.desa.index');
        Route::get('create', [DesaController::class, 'create'])->name('admin.desa.create');
        Route::post('', [DesaController::class, 'store'])->name('admin.desa.store');
        Route::get('{desa:kode}', [DesaController::class, 'show'])->name('admin.desa.show');
        Route::get('{desa:kode}/edit', [DesaController::class, 'edit'])->name('admin.desa.edit');
        Route::put('{desa:kode}', [DesaController::class, 'update'])->name('admin.desa.update');
    });

    Route::prefix('user')->group(function () {
        Route::get('', [UserController::class, 'index'])->name('admin.user.index');
        Route::get('create', [UserController::class, 'create'])->name('admin.user.create');
        Route::post('', [UserController::class, 'store'])->name('admin.user.store');
        Route::get('{user}', [UserController::class, 'show'])->name('admin.user.show');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
        Route::put('{user}', [UserController::class, 'update'])->name('admin.user.update');
    });

    Route::prefix('role')->group(function () {
        Route::get('', [RoleController::class, 'index'])->name('admin.role.index');
        Route::get('create', [RoleController::class, 'create'])->name('admin.role.create');
        Route::post('', [RoleController::class, 'store'])->name('admin.role.store');
        Route::get('{role}', [RoleController::class, 'show'])->name('admin.role.show');
        Route::get('{role}/edit', [RoleController::class, 'edit'])->name('admin.role.edit');
        Route::put('{role}', [RoleController::class, 'update'])->name('admin.role.update');
    });

    Route::prefix('visitor')->group(function () {
        Route::get('', [VisitorController::class, 'index'])->name('admin.visitor.index');
        Route::post('cleanup', [VisitorController::class, 'cleanup'])->name('admin.visitor.cleanup');
    });

    Route::prefix('blog')->group(function () {
        Route::get('', [BlogController::class, 'index'])->name('admin.blog.index');
        Route::get('create', [BlogController::class, 'create'])->name('admin.blog.create');
        Route::post('', [BlogController::class, 'store'])->name('admin.blog.store');
        Route::get('{blog}', [BlogController::class, 'show'])->name('admin.blog.show');
        Route::get('{blog}/edit', [BlogController::class, 'edit'])->name('admin.blog.edit');
        Route::put('{blog}', [BlogController::class, 'update'])->name('admin.blog.update');
    });

    Route::prefix('live-stream')->group(function () {
        Route::get('', [\App\Http\Controllers\LiveCamController::class, 'index'])->name('admin.live-stream.index');
        Route::get('create', [\App\Http\Controllers\LiveCamController::class, 'create'])->name('admin.live-stream.create');
        Route::post('', [\App\Http\Controllers\LiveCamController::class, 'store'])->name('admin.live-stream.store');
        Route::get('broadcast/{stream:slug}', [\App\Http\Controllers\LiveCamController::class, 'broadcast'])->name('admin.live-stream.broadcast');
        Route::post('{stream:slug}/start', [\App\Http\Controllers\LiveCamController::class, 'startStream'])->name('admin.live-stream.start');
        Route::post('{stream:slug}/stop', [\App\Http\Controllers\LiveCamController::class, 'stopStream'])->name('admin.live-stream.stop');
        Route::post('{stream:slug}/change-quality', [\App\Http\Controllers\LiveCamController::class, 'changeQuality'])->name('admin.live-stream.change-quality');
        Route::delete('{stream:slug}', [\App\Http\Controllers\LiveCamController::class, 'destroy'])->name('admin.live-stream.destroy');

        // WebRTC Signaling routes (deprecated - kept for backward compatibility)
        Route::post('{stream:slug}/viewer-ready', [\App\Http\Controllers\LiveCamController::class, 'viewerReady'])->name('admin.live-stream.viewer-ready');
        Route::post('{stream:slug}/send-signal', [\App\Http\Controllers\LiveCamController::class, 'sendSignal'])->name('admin.live-stream.send-signal');
        Route::post('{stream:slug}/end', [\App\Http\Controllers\LiveCamController::class, 'stopStream'])->name('admin.live-stream.end');

        // MSE Chunked Streaming routes (NEW - scalable live streaming)
        Route::post('{stream:slug}/upload-chunk', [\App\Http\Controllers\LiveCamController::class, 'uploadChunk'])->name('admin.live-stream.upload-chunk');
        Route::get('{stream:slug}/chunk/{index}', [\App\Http\Controllers\LiveCamController::class, 'getChunk'])->name('admin.live-stream.get-chunk');
        Route::get('{stream:slug}/status', [\App\Http\Controllers\LiveCamController::class, 'getStatus'])->name('admin.live-stream.status');

        // HLS Streaming routes (NEW - for basecamp livestreaming)
        Route::post('{stream:slug}/upload-hls-chunk', [\App\Http\Controllers\LiveCamController::class, 'uploadHLSChunk'])->name('admin.live-stream.upload-hls-chunk');
        Route::get('{stream:slug}/playlist.m3u8', [\App\Http\Controllers\LiveCamController::class, 'getHLSPlaylist'])->name('admin.live-stream.hls-playlist');

        // Chat history for broadcaster (MUST be before wildcard segment route!)
        Route::get('{stream:slug}/chat-history', [\App\Http\Controllers\LiveCamController::class, 'getChatHistory'])->name('admin.live-stream.chat-history');

        // Mirror state broadcast (MUST be before wildcard!)
        Route::post('{stream:slug}/mirror', [\App\Http\Controllers\LiveCamController::class, 'updateMirrorState'])->name('admin.live-stream.mirror');

        // Thumbnail upload (MUST be before wildcard!)
        Route::post('{stream:slug}/thumbnail', [\App\Http\Controllers\LiveCamController::class, 'uploadThumbnail'])->name('admin.live-stream.thumbnail');

        // LiveKit token for broadcaster (MUST be before wildcard!)
        Route::get('{stream:slug}/livekit/token', [\App\Http\Controllers\LiveCamController::class, 'getLiveKitBroadcasterToken'])->name('admin.live-stream.livekit-token');

        // HLS segment route (WILDCARD - must be last!)
        Route::get('{stream:slug}/{segmentName}', [\App\Http\Controllers\LiveCamController::class, 'getHLSSegment'])->name('admin.live-stream.hls-segment');
    });

    /**
     * API --- START
     */
    Route::prefix('api')->group(function () {
        Route::post('toggle-sidebar', [DashboardController::class, 'apiToggleSidebar'])->name('admin.api.toggle-sidebar');

        Route::prefix('gunung')->group(function () {
            Route::post('', [GunungController::class, 'apiIndex'])->name('admin.api.gunung.index');
            Route::delete('{gunung}', [GunungController::class, 'apiDelete'])->name(name: 'admin.api.gunung.delete');
            Route::post('select', [GunungController::class, 'apiSelect'])->name('admin.api.gunung.select');
        });

        Route::prefix('rute')->group(function () {
            Route::post('', [RuteController::class, 'apiIndex'])->name('admin.api.rute.index');
            Route::delete('{rute}', [RuteController::class, 'apiDelete'])->name('admin.api.rute.delete');
            Route::put('{rute}', [RuteController::class, 'apiUpdate'])->name('admin.api.rute.update');
            Route::post('{rute}/point', [RuteController::class, 'apiPoint'])->name('admin.api.rute.point');
            Route::get('{rute}/points', [RuteController::class, 'apiPoints'])->name('admin.api.rute.points');
            Route::post('select', [RuteController::class, 'apiSelect'])->name('admin.api.rute.select');
        });

        Route::prefix('point')->group(function () {
            Route::post('', [PointController::class, 'apiIndex'])->name('admin.api.point.index');
            Route::put('{point}', [PointController::class, 'apiUpdate'])->name('admin.api.point.update');
        });

        Route::prefix('rute-tingkat-kesulitan')->group(function () {
            Route::post('', [RuteTingkatKesulitanController::class, 'apiIndex'])->name('admin.api.rute-tingkat-kesulitan.index');
            Route::delete('{ruteTingkatKesulitan}', [RuteTingkatKesulitanController::class, 'apiDelete'])->name('admin.api.rute-tingkat-kesulitan.delete');
            Route::post('select', [RuteTingkatKesulitanController::class, 'apiSelect'])->name('admin.api.rute-tingkat-kesulitan.select');
        });

        Route::prefix('comment')->group(function () {
            Route::post('', [CommentController::class, 'apiIndex'])->name('admin.api.comment.index');
            Route::delete('{comment}', [CommentController::class, 'apiDelete'])->name('admin.api.comment.delete');
        });

        Route::prefix('negara')->group(function () {
            Route::post('', [NegaraController::class, 'apiIndex'])->name('admin.api.negara.index');
            Route::delete('{negara}', [NegaraController::class, 'apiDelete'])->name('admin.api.negara.delete');
            Route::post('select', [NegaraController::class, 'apiSelect'])->name('admin.api.negara.select');
        });

        Route::prefix('provinsi')->group(function () {
            Route::post('', [ProvinsiController::class, 'apiIndex'])->name('admin.api.provinsi.index');
            Route::delete('{provinsi:kode}', [ProvinsiController::class, 'apiDelete'])->name('admin.api.provinsi.delete');
            Route::post('select', [ProvinsiController::class, 'apiSelect'])->name('admin.api.provinsi.select');
        });

        Route::prefix('kabupaten-kota')->group(function () {
            Route::post('', [KabupatenKotaController::class, 'apiIndex'])->name('admin.api.kabupaten-kota.index');
            Route::delete('{kabupatenKota:kode}', [KabupatenKotaController::class, 'apiDelete'])->name('admin.api.kabupaten-kota.delete');
            Route::post('select', [KabupatenKotaController::class, 'apiSelect'])->name('admin.api.kabupaten-kota.select');
        });

        Route::prefix('kecamatan')->group(function () {
            Route::post('', [KecamatanController::class, 'apiIndex'])->name('admin.api.kecamatan.index');
            Route::delete('{kecamatan:kode}', [KecamatanController::class, 'apiDelete'])->name('admin.api.kecamatan.delete');
            Route::post('select', [KecamatanController::class, 'apiSelect'])->name('admin.api.kecamatan.select');
        });

        Route::prefix('desa')->group(function () {
            Route::post('', [DesaController::class, 'apiIndex'])->name('admin.api.desa.index');
            Route::delete('{desa:kode}', [DesaController::class, 'apiDelete'])->name('admin.api.desa.delete');
            Route::post('select', [DesaController::class, 'apiSelect'])->name('admin.api.desa.select');
        });

        Route::prefix('user')->group(function () {
            Route::post('', [UserController::class, 'apiIndex'])->name('admin.api.user.index');
            Route::delete('{user}', [UserController::class, 'apiDelete'])->name('admin.api.user.delete');
            Route::post('select', [UserController::class, 'apiSelect'])->name('admin.api.user.select');
        });

        Route::prefix('role')->group(function () {
            Route::post('', [RoleController::class, 'apiIndex'])->name('admin.api.role.index');
            Route::delete('{role}', [RoleController::class, 'apiDelete'])->name('admin.api.role.delete');
            Route::post('select', [RoleController::class, 'apiSelect'])->name('admin.api.role.select');
        });

        Route::prefix('visitor')->group(function () {
            Route::post('', [VisitorController::class, 'apiIndex'])->name('admin.api.visitor.index');
        });

        Route::prefix('blog')->group(function () {
            Route::post('', [BlogController::class, 'apiIndex'])->name('admin.api.blog.index');
            Route::put('{blog}', [BlogController::class, 'apiUpdate'])->name('admin.api.blog.update');
            Route::delete('{blog}', [BlogController::class, 'apiDelete'])->name(name: 'admin.api.blog.delete');
        });
    });
    /**
     * API --- END
     */
});
