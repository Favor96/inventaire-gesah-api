<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AcountVerifed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifie si l'utilisateur est connecté et son compte est vérifié
        if (Auth::check() && Auth::user()->is_verified) {
            return $next($request);
        }

        // Sinon, renvoie une erreur 403
        return response()->json(['message' => 'Compte non vérifié.'], 403);
    }
}
