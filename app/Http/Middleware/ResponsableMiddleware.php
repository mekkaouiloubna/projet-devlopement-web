<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResponsableMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->isResponsable()) {
            return response()->json([
                'message' => 'Accès non autorisé. Responsable requis.'
            ], 403);
        }

        return $next($request);
    }
}