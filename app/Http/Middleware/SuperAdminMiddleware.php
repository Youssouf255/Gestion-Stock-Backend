<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user() instanceof \App\Models\Admin) {
            return response()->json([
                'message' => 'Non autorisé. Accès super administrateur requis.',
            ], 403);
        }

        if (!$request->user()->isSuperAdmin()) {
            return response()->json([
                'message' => 'Accès refusé. Privilèges super administrateur requis.',
            ], 403);
        }

        return $next($request);
    }
}











