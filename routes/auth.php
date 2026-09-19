<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// ── Two-Factor Authentication ────────────────────────────────────────────────
// Challenge: shown after password login when 2FA is required (no auth yet)
Route::middleware('guest')->group(function () {
    Route::get( '2fa/challenge',        [TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    // A 6-digit TOTP code is brute-forceable without rate limiting (~1M
    // combinations). The route throttle is a cheap first line of defense;
    // verifyChallenge() itself adds a tighter per-user+IP lockout below.
    Route::post('2fa/challenge',        [TwoFactorController::class, 'verifyChallenge'])
        ->middleware('throttle:10,1')->name('2fa.verify');
});

// 2FA management: shown to already-authenticated users
Route::middleware('auth')->group(function () {
    Route::get( '2fa/security',         [TwoFactorController::class, 'security'])->name('2fa.security');
    Route::get( '2fa/setup',            [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('2fa/setup',            [TwoFactorController::class, 'confirmSetup'])
        ->middleware('throttle:5,1')->name('2fa.setup.confirm');
    Route::get( '2fa/recovery-codes',   [TwoFactorController::class, 'showRecoveryCodes'])->name('2fa.recovery-codes');
    Route::post('2fa/disable',          [TwoFactorController::class, 'disable'])->name('2fa.disable');
});

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:5,1');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    // Password::sendResetLink() only cools down repeats for the SAME email;
    // an IP throttle is needed to stop spraying reset-link emails across many
    // different addresses.
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
