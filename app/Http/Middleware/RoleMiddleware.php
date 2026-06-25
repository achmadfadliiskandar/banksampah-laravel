<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        if (!in_array($request->user()->role, $roles)) {
            // Jika Nasabah mau masuk ke wilayah Admin
            if ($request->user()->role === 'nasabah') {
                return redirect('/pwa/home')->with('error', 'Anda tidak punya akses ke Dashboard Admin!');
            }
            
            // Jika Admin mau masuk ke wilayah PWA (Opsional, boleh dibolehkan atau tidak)
            if ($request->user()->role === 'admin') {
                return redirect('/dashboard')->with('error', 'Gunakan dashboard web untuk admin!');
            }
        }

        return $next($request);
    }
}
