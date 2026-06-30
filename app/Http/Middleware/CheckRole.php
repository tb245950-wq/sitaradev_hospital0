<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        
        // Log for debugging (remove in production)
        \Log::info('CheckRole Middleware', [
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'required_roles' => $roles,
            'path' => $request->path(),
        ]);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak terautentikasi.'
            ], 401);
        }

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Role Anda (' . $user->role . ') tidak memiliki izin untuk mengakses resource ini.',
                'required_roles' => $roles,
                'your_role' => $user->role
            ], 403);
        }

        return $next($request);
    }
}
