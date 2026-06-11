<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Field sensitif yang tidak boleh di-flash ke session saat validasi error.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'admin_reset_token',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
    {
        // Tampilkan 404 yang bersih saat model tidak ditemukan
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if (!$request->expectsJson()) {
                return response()->view('errors.404', [], 404);
            }
        });

        // Log error 500 tapi tampilkan halaman yang bersih (tanpa stacktrace)
        $this->reportable(function (Throwable $e) {
            // Di sini bisa tambahkan integrasi Sentry/Bugsnag di masa depan:
            // \Sentry\captureException($e);
        });
    }

    /**
     * Arahkan unauthenticated ke login, bukan ke /login default bawaan Laravel.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
