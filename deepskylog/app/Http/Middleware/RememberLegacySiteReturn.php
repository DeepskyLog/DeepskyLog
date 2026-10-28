<?php

namespace App\Http\Middleware;

use App\Support\LegacySiteReturn;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the ?return_to= of the old site's link to the registration page
 * (App\Support\LegacySiteReturn).
 */
class RememberLegacySiteReturn
{
    public function handle(Request $request, Closure $next): Response
    {
        LegacySiteReturn::remember($request);

        return $next($request);
    }
}
