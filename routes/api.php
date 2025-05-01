<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterRequestsController;
use App\Http\Controllers\Api\SocialiteController;
use \Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name(
        'auth.logout'
    );
    Route::post('/provide', [SocialiteController::class, 'releaseToken'])->name(
        'release.token'
    );
});

Route::prefix('/registers/requests')->group(function () {
    // Used only by admin role
    Route::delete(
        '/{registerRequestID}',
        [RegisterRequestsController::class, 'destroy']
    )->name('register.request.destroy')->middleware('auth:sanctum');

    // Used by guest
    Route::post(
        '/store',
        [RegisterRequestsController::class, 'store']
    )->name('register.request.store');
});
