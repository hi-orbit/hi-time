<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    /**
     * Authenticate an incoming API request by API key.
     *
     * Accepts the key in the X-API-Key header, or as a Bearer token
     * in the Authorization header.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->extractKey($request);

        if (! $key) {
            return response()->json(['message' => 'Missing API key. Use the X-API-Key header.'], 401);
        }

        $user = User::where('api_key', $key)->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid or missing API key.'], 401);
        }

        Auth::setUser($user);

        return $next($request);
    }

    private function extractKey(Request $request): ?string
    {
        if ($request->header('X-API-Key')) {
            return $request->header('X-API-Key');
        }

        $authorization = $request->header('Authorization');

        if ($authorization && str_starts_with($authorization, 'Bearer ')) {
            return substr($authorization, 7);
        }

        return null;
    }
}
