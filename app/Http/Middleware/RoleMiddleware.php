<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string[]  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }
        
        // Handle comma-separated string if passed as a single argument (Laravel middleware style)
        if (count($roles) === 1 && str_contains($roles[0], ',')) {
            $roles = explode(',', $roles[0]);
        }
        
        // Cek apakah role user ada dalam daftar role yang diizinkan
        if (!in_array($request->user()->role, $roles)) {
            return response()->json(['success' => false, 'message' => 'Forbidden: Akses ditolak'], 403);
        }
        
        if ($request->user()->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Akun tidak aktif'], 403);
        }
        
        return $next($request);
    }
}
