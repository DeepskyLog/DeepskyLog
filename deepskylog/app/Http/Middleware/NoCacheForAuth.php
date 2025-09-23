<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoCacheForAuth
{
    /**
     * Handle an incoming request.
     *
     * Prevent caching for authenticated users, non-GET requests and typical auth routes.
     */
    public function handle(Request $request, Closure $next)
    {
        // Do not cache POST/PUT/PATCH/DELETE as they may change state or rely on fresh CSRF tokens
        if (!in_array($request->method(), ['GET', 'HEAD'])) {
            $request->attributes->set('responsecache.doNotCache', true);
        }

        // If user is authenticated, don't cache
        if ($request->user()) {
            $request->attributes->set('responsecache.doNotCache', true);
        }

        // Common auth routes we should ensure are never cached
        $path = ltrim($request->path(), '/');
        $authPaths = [
            'login',
            'logout',
            'register',
            'password/reset',
            'password/email',
            'password/confirm',
        ];

        foreach ($authPaths as $p) {
            if ($path === $p || str_starts_with($path, rtrim($p, '/').'/')) {
                $request->attributes->set('responsecache.doNotCache', true);
                break;
            }
        }

        $response = $next($request);

        // Ensure HTTP cache headers prevent proxies and browsers from serving stale pages
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        return $response;
    }
}
