<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for guest routes
        if (!Auth::check()) {
            return $next($request);
        }

        // Get user's tenant
        $user = Auth::user();
        
        // For now, we'll get the tenant from the user table
        // In a more complex setup, you might determine tenant from domain
        if ($user && method_exists($user, 'tenant')) {
            $tenant = $user->tenant;
            
            if ($tenant) {
                // Switch to tenant database
                $tenant->switchDatabase();
                
                // Store tenant in session for easy access
                session(['current_tenant' => $tenant->id]);
            }
        }

        return $next($request);
    }
}

