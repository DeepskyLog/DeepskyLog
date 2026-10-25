<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ObserverBlog;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * The blog of an observer (#964): their sessions, observations and public
 * observing lists in one timeline, paged by date with ?before=Y-m-d.
 */
class ObserverBlogController extends Controller
{
    public function show(Request $request, string $observer)
    {
        $user = User::where('slug', $observer)->firstOrFail();

        $before      = null;
        $beforeParam = (string) $request->query('before', '');
        if ($beforeParam !== '') {
            try {
                $before = Carbon::createFromFormat('!Y-m-d', $beforeParam);
            } catch (\Throwable $_) {
                $before = null;
            }
            if (! $before || $before->toDateString() !== $beforeParam) {
                abort(404);
            }
        }

        $page = (new ObserverBlog($user))->page($before);

        return view('observers.blog', [
            'user'    => $user,
            'entries' => $page['entries'],
            'next'    => $page['next'],
            'before'  => $before,
        ]);
    }
}
