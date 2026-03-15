<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // This middleware can be used to perform additional checks or logic per request.
        // The TenantScope handles the filtering, so this is primarily for validation or initialization.
        
        if (auth()->check() && !auth()->user()->shop_id) {
             return response()->json(['message' => 'Unauthorized: No shop associated with this user.'], 403);
        }

        return $next($request);
    }
}
