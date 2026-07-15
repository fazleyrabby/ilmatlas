<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        app()->instance('csp-nonce', $nonce);
        Vite::useCspNonce($nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $connectSrc = "connect-src 'self' https:";
        if (app()->environment('local')) {
            $connectSrc .= ' ws://localhost:* ws://127.0.0.1:* wss://localhost:* wss://127.0.0.1:*';
        }

        // Skip strict CSP when debugging so framework error/debug pages
        // (Ignition) — which rely on inline styles/scripts — render correctly.
        if (! config('app.debug')) {
            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; ".
                "script-src 'self' 'nonce-{$nonce}' 'strict-dynamic' https://cdn.jsdelivr.net; ".
                "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com; ".
                "font-src 'self' https://fonts.gstatic.com; ".
                "img-src 'self' data: https:; ".
                "media-src 'self'; ".
                $connectSrc.'; '.
                "frame-src 'none'; ".
                "object-src 'none'; ".
                "base-uri 'self'; ".
                "form-action 'self';"
            );
        }

        return $response;
    }
}
