<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'verify_school_unit_token' => \App\Http\Middleware\VerifySchoolUnitToken::class,
            'verify_pkg_api_token' => \App\Http\Middleware\VerifyPkgApiToken::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'iclock/*',
            'api/*',
        ]);

        $middleware->encryptCookies(except: [
            'download_token',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
