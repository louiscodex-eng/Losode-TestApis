<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth; 
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVendor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
public function handle(Request $request, Closure $next): Response
    {
        // Use the Auth facade for better type-hinting
        if (Auth::check() && Auth::user()->role === 'vendor') {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Forbidden: Only vendors can perform this action.'
        ], 403);
    }
}
