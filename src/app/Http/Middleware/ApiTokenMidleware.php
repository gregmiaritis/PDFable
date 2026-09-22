<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMidleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.pdf_api.token');
        $given = (string) $request->bearerToken();

        if ($expected === '') {
            return response()->json(['message' => 'API token is not configured.'], 500);
        }

        if ($given === '') {
            return response()->json(['message' => 'Missing API token. Send it as "Authorization: Bearer <token>".'], 401);
        }

        if (! hash_equals($expected, $given)) {
            return response()->json(['message' => 'Invalid API token.'], 401);
        }

        return $next($request);
    }
}
