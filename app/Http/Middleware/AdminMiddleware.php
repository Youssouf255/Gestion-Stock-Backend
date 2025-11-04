<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
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
                'message' => 'Non autorisé. Accès administrateur requis.',
            ], 403);
        }

        if (!$request->user()->is_active) {
            return response()->json([
                'message' => 'Votre compte administrateur est désactivé.',
            ], 403);
        }

        return $next($request);
    }
}











