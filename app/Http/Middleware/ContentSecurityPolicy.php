<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Set Content Security Policy headers
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self'; ";
        $csp .= "style-src 'self'; ";
        $csp .= "img-src 'self' data:;";
        
        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
