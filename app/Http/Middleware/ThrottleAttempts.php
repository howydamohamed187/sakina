<?php

namespace App\Http\Middleware;

use App\Http\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAttempts
{
    public function handle(Request $request, Closure $next, string $bucket): Response
    {
        [$max, $decay] = match ($bucket) {
            'otp-send' => [5, 60],
            'otp-verify' => [8, 60],
            default => [10, 60],
        };

        $key = $bucket.'|'.$request->ip().'|'.strtolower((string) $request->input('email', 'guest'));

        if (RateLimiter::tooManyAttempts($key, $max)) {
            return ApiResponse::json(429, __('api.too_many_attempts'));
        }

        RateLimiter::hit($key, $decay);

        return $next($request);
    }
}
