<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Vérifier le rôle si spécifié
        if ($role && Auth::user()->role->nom !== $role) {
            abort(403, 'Accès non autorisé');
        }

        return $next($request);
    }
}