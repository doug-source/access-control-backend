<?php

use App\Http\Controllers\EmailVerifyController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

Route::get('/', function () {
    abort(404);
});

Route::get(
    '/auth/{provider}/redirect/{type}',
    [SocialiteController::class, 'redirectToProvider']
)->name('oauth.redirect');

Route::get(
    '/auth/{provider}/callback',
    [SocialiteController::class, 'handleProvideCallback']
)->name('oauth.callback');

Route::get(
    '/users/create',
    [UserController::class, 'create']
)->name('user.create')->middleware('signed');

Route::get(
    '/login',
    [EmailVerifyController::class, 'redirectLogin']
)->name('login');

Route::get('/storage/app/{folder}/{filename}', [ImageController::class, 'find'])->name('user.photo.show');

Route::fallback(function () {
    abort(404);
});
