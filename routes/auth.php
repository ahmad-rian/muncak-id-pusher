<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->group(function () {
    Route::get('/sign-up', [UserController::class, 'signUp'])
        ->name('auth.sign-up')
        ->middleware(['guest']);

    Route::post('/sign-up-action', [UserController::class, 'signUpAction'])
        ->name('auth.sign-up-action')
        ->middleware(['guest']);

    Route::get('/sign-in', [UserController::class, 'signIn'])
        ->name('auth.sign-in')
        ->middleware(['guest']);

    Route::post('/sign-in-action', [UserController::class, 'signInAction'])
        ->name('auth.sign-in-action')
        ->middleware(['guest']);

    Route::post('/sign-out', [UserController::class, 'signOut'])
        ->name('auth.sign-out')
        ->middleware(['auth']);

    Route::get('/oauth/redirect', [UserController::class, 'oauthRedirect'])
        ->name('auth.oauth-redirect')
        ->middleware(['guest']);

    Route::get('/oauth/google/callback', [UserController::class, 'oauthGoogleCallback'])
        ->name('auth.oauth-google-callback')
        ->middleware(['guest']);

    Route::get('/oauth/facebook/callback', [UserController::class, 'oauthFacebookCallback'])
        ->name('auth.oauth-facebook-callback')
        ->middleware(['guest']);
});
