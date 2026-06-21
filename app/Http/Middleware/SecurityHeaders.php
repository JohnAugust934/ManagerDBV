<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // CSP: Alpine.js v3 avalia expressões via new Function() — requer 'unsafe-eval'.
        // 'unsafe-inline' cobre scripts inline do Vite/Blade.
        // fonts.bunny.net é necessário para carregar a fonte Inter usada no layout.
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; ".
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'; ".  // Alpine.js v3 requer unsafe-eval (new Function)
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; ".
            "img-src 'self' data: blob:; ".          // data: para logos base64 nos PDFs, blob: para previews
            "font-src 'self' data: https://fonts.bunny.net; ".
            "connect-src 'self'; ".
            "frame-ancestors 'none';"               // reforça X-Frame-Options
        );

        return $response;
    }
}
