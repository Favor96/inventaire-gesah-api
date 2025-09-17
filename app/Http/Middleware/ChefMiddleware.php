<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ChefMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifie si l'utilisateur est connecté et a le rôle 'admin'
        if (Auth::check() && Auth::user()->role === 'chef_de_mission') {
            return $next($request);
        }

        // Sinon, renvoie une erreur 403
        return response()->json(['message' => 'Accès refusé.'], 403);
    }
}
