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

        // Only apply to HTML responses (skip JSON, file downloads, etc.)
        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        // Content Security Policy
        $csp = implode('; ', [
            "default-src 'self'",
            
            // Scripts: SheetJS, Font Awesome CDN, jsDelivr, unpkg (Leaflet + MarkerCluster)
            "script-src 'self' 'unsafe-inline' https://cdn.sheetjs.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com blob:",

            // Styles: Google Fonts, Font Awesome CDN, unpkg (Leaflet CSS)
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://unpkg.com",

            // Fonts: Google Fonts, Font Awesome
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:",

            // Images: your images, alumni photos, and OSM map tiles
            "img-src 'self' data: blob: https: https://tile.openstreetmap.org https://*.tile.openstreetmap.org",

            // Connect: your API + OSM tile servers for Leaflet's fetch requests
            "connect-src 'self' blob: https://tile.openstreetmap.org https://*.tile.openstreetmap.org",
                        
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        
        // Changed to cross-origin to allow attachments from external storage
        $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin');
        
        // Added HSTS
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Hide server fingerprinting
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}