<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Resource;

class AdminOrResponsableMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($user->isResponsable()) {
            $resourceId = $request->route('resource');

            if ($resourceId) {
                $resource = Resource::find($resourceId);

                if ($resource && $resource->responsable_id == $user->id) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Accès non autorisé. Vous devez être l\'administrateur ou le responsable de ce resource.');
    }
}
