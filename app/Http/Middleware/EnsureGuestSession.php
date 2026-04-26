<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cookieName = 'tixkita_guest_id';
        $guestId = $request->cookie($cookieName);

        if (!$guestId) {
            $guestId = (string) Str::uuid();
            // Queue cookie for 1 year (minutes)
            // 60 * 24 * 365 = 525600
            Cookie::queue($cookieName, $guestId, 525600);
        } else {
             // Extend cookie lifetime if exists
             Cookie::queue($cookieName, $guestId, 525600);
        }

        // Make it available in request for easy access
        $request->merge(['guest_token' => $guestId]);

        return $next($request);
    }
}
