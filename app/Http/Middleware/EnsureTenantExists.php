<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantExists
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check if user has a tenant (for manual tenant:create commands)
        // In production, every user should be created with a tenant
        if (!$user->tenant_id) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'error' => 'Aucun compte tenant associé. Veuillez contacter le support.'
            ]);
        }

        return $next($request);
    }
}

