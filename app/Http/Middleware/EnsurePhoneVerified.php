<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/logout')) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();

            if (!$user->phone_verified_at) {
                return response()->json(['message' => 'Phone is not verified'], 400);
            }
        }
        return $next($request);
    }
}
