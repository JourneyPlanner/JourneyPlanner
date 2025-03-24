<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisteredUserController::class, 'store'])->name(
    'register'
);

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

Route::post('/email/verify/{id}/{hash}', [
    VerifyEmailController::class,
    'verify',
])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/resend', [
    EmailVerificationNotificationController::class,
    'resend',
])
    ->middleware('throttle:6,1')
    ->name('verification.send');

Route::post('/email/update/verify/{token}', [
    VerifyEmailController::class,
    'verifyPending',
])
    ->middleware(['web', 'signed'])
    ->name('verification.update.verify');

Route::post('/email/update/resend', [
    EmailVerificationNotificationController::class,
    'resendPending',
])
    ->middleware(['web', 'throttle:6,1'])
    ->name('verification.update.send');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/auth/redirect/microsoft', [
    AuthenticatedSessionController::class,
    'redirectToProviderMicrosoft',
]);
Route::get('/auth/callback/microsoft', [
    AuthenticatedSessionController::class,
    'handleProviderCallbackMicrosoft',
]);
Route::post('/auth/callback/google', [
    AuthenticatedSessionController::class,
    'handleProviderCallbackGoogle',
]);
