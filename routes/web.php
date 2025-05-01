<?php

use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\UserController;
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

Route::get(
    '/users/create',
    [UserController::class, 'create']
)->name('users.create')->middleware('signed');

Route::fallback(function () {
    abort(404);
});
