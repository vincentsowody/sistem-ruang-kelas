<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    /*
    |------------------------------------------------------------------
    | Registrasi publik DINONAKTIFKAN.
    | Akun hanya bisa dibuat oleh admin melalui menu Manajemen Pengguna.
    | Jika diakses langsung, redirect ke login dengan pesan penjelasan.
    |------------------------------------------------------------------
    */
    Route::get('register', function () {
        return redirect()->route('login')
            ->with('info', 'Pendaftaran akun hanya dapat dilakukan oleh administrator. Hubungi admin kampus untuk mendapatkan akun.');
    })->name('register');

    Route::post('register', function () {
        abort(403, 'Registrasi publik tidak diizinkan.');
    });

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('throttle:5,1'); // maks 5 percobaan per menit per IP

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');
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
