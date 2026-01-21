<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Запросы на создание холда должны содержать заголовок Idempotency-Key
 */
class IdempotencyKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('Idempotency-Key');

        if ($key === null || !Str::isUuid($key)) {
            return response()->json([
                'message' => 'Invalid Idempotency-Key',
            ], 400);
        }

        $request->attributes->set('idempotency_key', $key);

        return $next($request);
    }
}
