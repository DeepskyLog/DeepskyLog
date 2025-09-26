<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Capture CSRF token mismatch details so we can debug 419s from specific clients
        $this->renderable(function (TokenMismatchException $e, $request) {
            try {
                $data = [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'referer' => $request->header('Referer'),
                    'cookies' => array_keys($request->cookies->all()),
                    'has_session_cookie' => (bool) $request->cookie(config('session.cookie')),
                ];

                Log::warning('CSRF token mismatch (419) encountered', $data);
            } catch (\Throwable $logEx) {
                // If logging fails, don't break the request flow
                Log::error('Failed to log TokenMismatchException details: '.$logEx->getMessage());
            }

            // Let the framework continue to render the usual response (419 page)
            return null;
        });
    }
}
