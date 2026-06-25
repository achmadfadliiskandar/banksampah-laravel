<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsMobile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $agent = $request->header('User-Agent');
        $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $agent);

        if (!$isMobile) {
            // Jika error berlanjut, hapus ": Response" di atas atau gunakan response() seperti ini
            return response("Maaf, fitur ini hanya tersedia di Smartphone (PWA).", 403);
        }

        return $next($request);
    }
}
