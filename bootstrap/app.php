<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class, // <-- Add this line
            // You can add other aliases here, e.g.:
            // 'auth' => \App\Http\Middleware\Authenticate::class, // Though 'auth' is often globally available
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
