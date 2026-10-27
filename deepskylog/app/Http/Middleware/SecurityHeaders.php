<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers on every response, cached ones included. A header the
 * response already carries is left alone.
 *
 * The Content-Security-Policy does not restrict scripts, styles or images:
 * the views are full of inline scripts and handlers, Alpine evaluates its
 * expressions, and observers' descriptions show images from anywhere. It
 * does stop the site from being framed elsewhere (clickjacking), plugins,
 * and a <base> tag injected into a page from redirecting its relative URLs.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $csp = "base-uri 'self'; object-src 'none'; frame-ancestors 'self'";
        if ($request->isSecure()) {
            $csp .= '; upgrade-insecure-requests';
        }

        $headers = [
            'Content-Security-Policy' => $csp,
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            // The microphone only for the site's own pages: dictating a description
            'Permissions-Policy' => 'camera=(), microphone=(self), payment=(), usb=()',
        ];

        // Only over HTTPS: browsers ignore it over HTTP, and a local site
        // must not be pinned to HTTPS.
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
