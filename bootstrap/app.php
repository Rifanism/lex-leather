<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsCustomer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Vercel's edge proxies the PHP function: without trusting it,
        // isSecure() is false, so the session cookie ships without the
        // Secure flag and back()-redirects fall back to http://.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'customer' => EnsureUserIsCustomer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
