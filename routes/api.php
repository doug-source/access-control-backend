<?php

use App\Http\Controllers\Api\{
    AuthController,
    EmailVerifyController,
    RegisterRequestsController,
    SocialiteController,
    UserController
};
use \Illuminate\Support\Facades\Route;

/**
 * Used by user authenticated - with email verification
 */
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    /**
     * Used only by admin role
     */
    Route::get(
        '/registers/requests',
        [RegisterRequestsController::class, 'index']
    )->name('register.request.index');
    Route::delete(
        '/registers/requests/{registerRequestID}',
        [RegisterRequestsController::class, 'destroy']
    )->name('register.request.destroy');
    Route::delete(
        '/registers/requests/{registerRequestID}/approval',
        [RegisterRequestsController::class, 'approve']
    )->name('register.request.approval');
});
/**
 * Used by user authenticated - no email verification
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/login/provide', [SocialiteController::class, 'releaseToken'])->name(
        'release.token'
    );
    Route::post('/logout', [AuthController::class, 'logout'])->name(
        'auth.logout'
    );
    Route::get(
        '/email/verify/{id}/{hash}',
        [EmailVerifyController::class, 'verify']
    )->middleware('signed')->name('verification.verify');
    Route::post(
        '/email/verification-notification',
        [EmailVerifyController::class, 'resend']
    )->middleware('throttle:6,1')->name('verification.send');
});

// ---------------------------------------------------------------------------------------

/**
 * Used by guest
 */
Route::post(
    '/login',
    [AuthController::class, 'login']
)->name('auth.login');
Route::post(
    '/registers/requests/store',
    [RegisterRequestsController::class, 'store']
)->name('register.request.store');
Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
