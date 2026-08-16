<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitBooking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maxAttempts = config('ratelimit.booking.max_attempts', 10);
        $decayMinutes = config('ratelimit.booking.decay_minutes', 1);
        
        // Membatasi berdasarkan User ID jika login, atau IP jika guest
        $userId = $request->user()?->id ?: $request->ip();
        $key = 'booking:' . $userId;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => 'Batas pengajuan antrian tercapai. Silakan coba lagi dalam ' . $seconds . ' detik.',
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
