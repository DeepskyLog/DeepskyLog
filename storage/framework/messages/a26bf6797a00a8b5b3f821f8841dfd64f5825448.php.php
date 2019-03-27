<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ClearanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request The request
     * @param \Closure                 $next    The next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // TODO: Change the clearances
        // If user has this permission
        if (Auth::user()->hasPermissionTo('Administer roles & permissions')) {
            return $next($request);
        }

        // TODO: ADAPT PERMISSIONS
        // If user is creating a post
        if ($request->is('posts/create')) {
            if (!Auth::user()->hasPermissionTo('Create Post')) {
                abort('401');
            } else {
                return $next($request);
            }
        }

        // If user is editing a post
        if ($request->is('posts/*/edit')) {
            if (!Auth::user()->hasPermissionTo('Edit Post')) {
                abort('401');
            } else {
                return $next($request);
            }
        }

        // If user is deleting a post
        if ($request->isMethod('Delete')) {
            if (!Auth::user()->hasPermissionTo('Delete Post')) {
                abort('401');
            }
        } else {
            return $next($request);
        }

        return $next($request);
    }
}
