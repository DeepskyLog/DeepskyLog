<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\ObservingList;
use App\Services\Observations\ObservationCardData;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class RssFeedController extends Controller
{
    /**
     * Feed readers poll often; a feed is rendered at most once per quarter of
     * an hour.
     */
    private const CACHE_SECONDS = 900;

    /**
     * RSS feed for the latest observations.
     */
    public function observations(): Response
    {
        return $this->feed('rss:observations', fn() => $this->renderObservations(false));
    }

    /**
     * RSS feed for the latest observations that have a sketch/drawing.
     */
    public function drawings(): Response
    {
        return $this->feed('rss:drawings', fn() => $this->renderObservations(true));
    }

    /**
     * RSS feed for the latest public observing lists.
     */
    public function observingLists(): Response
    {
        return $this->feed('rss:observing-lists', function () {
            $lists = ObservingList::with(['owner', 'items'])
                ->withCount('items')
                ->where('public', true)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            $feedUpdated = $lists->first()?->created_at ?? now();

            return view('rss.observing-lists', compact('lists', 'feedUpdated'))->render();
        });
    }

    private function renderObservations(bool $drawingsOnly): string
    {
        $observations = Observation::with(['instrument.instrument_make', 'eyepiece', 'observerUser'])
            ->when($drawingsOnly, fn($query) => $query->where('has_drawing', true))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // The locations (for the local time) and the observations of the same
        // day (for the index in the detail URLs) in a few queries.
        $cards = ObservationCardData::instance()->preload($observations);

        $feedUpdated = $observations->first()?->created_at ?? now();

        return view('rss.observations', compact('observations', 'feedUpdated', 'cards'))->render();
    }

    private function feed(string $cacheKey, \Closure $render): Response
    {
        return response(Cache::remember($cacheKey, self::CACHE_SECONDS, $render), 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }
}
