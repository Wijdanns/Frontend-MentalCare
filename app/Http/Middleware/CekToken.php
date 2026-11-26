<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengganti middleware "auth" + "EnsureRole" di frontend.
 * Pemakaian di route:  CekToken::class          -> cukup sudah login
 *                      CekToken::class.':pasien' -> harus login sebagai pasien
 */
class CekToken
{
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        if (! session()->has('api_token')) {
            session(['url.intended' => $request->fullUrl()]);

            return redirect()->route('login')
                ->with('error', 'Silakan masuk terlebih dahulu.');
        }

        if ($role && session('role') !== $role) {
            abort(403);
        }

        return $next($request);
    }
}