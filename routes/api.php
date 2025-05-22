<?php

use App\Http\Controllers\Api\{
    AbilityController,
    AbilityRoleController,
    AuthController,
    EmailVerifyController,
    ForgotPasswordController,
    RegisterPermissionController,
    RegisterRequestsController,
    ResetPasswordController,
    RoleController,
    RoleUserController,
    SocialiteController,
    UserController,
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
    Route::get(
        '/registers/requests/{registerRequestID}',
        [RegisterRequestsController::class, 'show']
    )->name('register.request.show');
    Route::delete(
        '/registers/requests/{registerRequestID}',
        [RegisterRequestsController::class, 'destroy']
    )->name('register.request.destroy');
    Route::delete(
        '/registers/requests/{registerRequestID}/approval',
        [RegisterRequestsController::class, 'approve']
    )->name('register.request.approval');

    Route::get(
        '/registers/permissions',
        [RegisterPermissionController::class, 'index']
    )->name('register.permission.index');
    Route::get(
        '/registers/permissions/{registerPermissionID}',
        [RegisterPermissionController::class, 'show']
    )->name('register.permission.show');
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

    Route::get('/abilities', [AbilityController::class, 'index'])->name('ability.index');
    Route::get('/abilities/{ability}', [AbilityController::class, 'show'])->name('ability.show');
    Route::post('/abilities', [AbilityController::class, 'store'])->name('ability.store');
    Route::patch('/abilities/{ability}', [AbilityController::class, 'update'])->name('ability.update');
    Route::delete('/abilities/{ability}', [AbilityController::class, 'destroy'])->name('ability.destroy');

    Route::get('/roles', [RoleController::class, 'index'])->name('role.index');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('role.show');
    Route::post('/roles', [RoleController::class, 'store'])->name('role.store');
    Route::patch('/roles/{role}', [RoleController::class, 'update'])->name('role.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('role.destroy');
    Route::get('/roles/{role}/abilities', [AbilityRoleController::class, 'index'])->name('role.ability.index');

    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::get('/users/{user}/roles', [RoleUserController::class, 'index'])->name('user.role.index');
});

// ---------------------------------------------------------------------------------------

/**
 * Used by guest
 */
Route::middleware('guest')->group(function () {
    Route::post(
        '/login',
        [AuthController::class, 'login']
    )->name('auth.login');
    Route::post(
        '/registers/requests/store',
        [RegisterRequestsController::class, 'store']
    )->name('register.request.store');
    Route::post(
        '/users/store',
        [UserController::class, 'store']
    )->name('users.store');
    Route::get(
        '/forgot-password',
        [ForgotPasswordController::class, 'askForgotPass']
    )->name('password.request');

    Route::post(
        '/reset-password',
        [ResetPasswordController::class, 'reset']
    )->name('password.update');
});
