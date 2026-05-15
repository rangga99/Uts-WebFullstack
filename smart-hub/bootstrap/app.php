<?php

// File: bootstrap/app.php
// Laravel 13 — Application Bootstrap

use App\Http\Middleware\EnsureApiToken;
use App\Http\Middleware\EnsureUserRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        apiPrefix: 'api',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register the role middleware alias
        $middleware->alias([
            'role'             => EnsureUserRole::class,
            'ensure_api_token' => EnsureApiToken::class,
        ]);

        // Ensure API responses are always JSON
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Sanctum stateful middleware (for web/SPA if needed)
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Render all API exceptions as JSON
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }
        });
    })
    ->create();
