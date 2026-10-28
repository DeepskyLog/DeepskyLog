<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ObserverBlog;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * The blog of an observer (#964): their sessions, observations and public
 * observing lists in one timeline, paged by date with ?before=Y-m-d, with
 * their running observing goals on top of the first page.
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

        $blog = new ObserverBlog($user);
        $page = $blog->page($before);

        return view('observers.blog', [
            'user'    => $user,
            'entries' => $page['entries'],
            'next'    => $page['next'],
            'before'  => $before,
            // The goals still running, above the newest entries
            'goals'   => $before === null
                ? $blog->goals()->filter(fn ($goal) => in_array($goal->progress()['status'], ['active', 'upcoming'], true))->values()
                : collect(),
        ]);
    }
}
