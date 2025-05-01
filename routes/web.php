<?php

use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    abort(404);
});

Route::get(
    '/auth/{provider}/redirect',
    [SocialiteController::class, 'redirectToProvider']
)->name('oauth.redirect');

Route::get(
    '/auth/{provider}/callback',
    [SocialiteController::class, 'handleProvideCallbackToLogin']
)->name('oauth.callback');

Route::fallback(function () {
    abort(404);
});
