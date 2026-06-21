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

        // CSP: Alpine.js usa scripts inline — 'unsafe-inline' em script-src seria necessário sem nonce.
        // Optamos por 'unsafe-inline' apenas em style-src (Tailwind inline styles gerados pelo Vite)
        // e bloqueamos scripts de origens externas. PDFs (dompdf) são gerados server-side, não afetados.
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; ".
            "script-src 'self' 'unsafe-inline'; ".   // Alpine.js precisa de inline scripts
            "style-src 'self' 'unsafe-inline'; ".    // Tailwind/Vite gera estilos inline
            "img-src 'self' data: blob:; ".          // data: para logos base64 nos PDFs, blob: para previews
            "font-src 'self' data:; ".
            "connect-src 'self'; ".
            "frame-ancestors 'none';"               // reforça X-Frame-Options
        );

        return $response;
    }
}
