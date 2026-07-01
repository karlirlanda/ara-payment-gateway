<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate the frontend player portal behind a mock demo session.
 *
 * The player area is purely frontend (no DB / Fortify); a successful demo login
 * seeds `session('player.demo')`. Guests are redirected to the player login.
 */
class EnsurePlayerSession
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('player.demo')) {
            return redirect()->route('player.login');
        }

        return $next($request);
    }
}
