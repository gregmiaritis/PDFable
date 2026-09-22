<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonRequestMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isJson()) {
            return response()->json(['message' => 'Content-Type must be application/json.'], 415);
        }

        return $next($request);
    }
}
