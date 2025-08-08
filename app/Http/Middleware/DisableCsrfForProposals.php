<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableCsrfForProposals
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip CSRF verification for proposal routes temporarily for debugging
        if ($request->is('proposals') && $request->isMethod('POST')) {
            $request->session()->regenerateToken();
        }

        return $next($request);
    }
}
