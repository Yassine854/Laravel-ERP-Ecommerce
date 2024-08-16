<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubdomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $subdomain = $request->route('subdomain');
        $user = Auth::user();

        if ($user && $user->subdomain !== $subdomain) {
            return response()->json(['message' => $user], 401);
        }
        return $next($request);
    }
}
