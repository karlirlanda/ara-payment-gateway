<?php

use App\Http\Middleware\EnsurePlayerSession;
use App\Http\Middleware\SetLocale;
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
        // Trust the TLS-terminating reverse proxy in front of the app (e.g. the dev
        // server / Laravel Cloud) so X-Forwarded-Proto is honoured. Without this the
        // request scheme is seen as HTTP behind an HTTPS proxy, and @vite() emits
        // http:// asset URLs that browsers block as mixed content.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            'player' => EnsurePlayerSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
