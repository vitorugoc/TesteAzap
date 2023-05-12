<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class JsonMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response->getStatusCode() == 404 && !$request->expectsJson()) {
            return response()->json(['erro' => 'Rota inexistente'], 404);
        }

        return $response;
    }
}
