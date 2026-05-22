<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\ObservingList;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class RssFeedController extends Controller
{
    /**
     * RSS feed for the latest observations.
     */
    public function observations(): Response
    {
        $observations = Observation::with(['instrument', 'eyepiece', 'observerUser'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $feedUpdated = $observations->first()?->created_at ?? now();

        $contents = view('rss.observations', compact('observations', 'feedUpdated'))->render();

        return response($contents, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }

    /**
     * RSS feed for the latest observations that have a sketch/drawing.
     */
    public function drawings(): Response
    {
        $observations = Observation::with(['instrument', 'eyepiece', 'observerUser'])
            ->where('has_drawing', true)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $feedUpdated = $observations->first()?->created_at ?? now();

        $contents = view('rss.observations', compact('observations', 'feedUpdated'))->render();

        return response($contents, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }

    /**
     * RSS feed for the latest public observing lists.
     */
    public function observingLists(): Response
    {
        $lists = ObservingList::with(['owner', 'items'])
            ->withCount('items')
            ->where('public', true)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $feedUpdated = $lists->first()?->created_at ?? now();

        $contents = view('rss.observing-lists', compact('lists', 'feedUpdated'))->render();

        return response($contents, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }
}
