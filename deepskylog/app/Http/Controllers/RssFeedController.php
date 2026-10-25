<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
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

    /**
     * RSS feed of the news (#960): the written items and the coming
     * astronomical events, in English with the moments in UT.
     */
    public function news(): Response
    {
        return $this->feed('rss:news', function () {
            $items = NewsItem::published()->orderByDesc('published_at')->orderByDesc('id')->limit(30)->get();
            $feedUpdated = $items->first()?->published_at ?? now();

            // The feed is cached for everybody: always in English.
            $locale = app()->getLocale();
            app()->setLocale('en');
            try {
                return view('rss.news', compact('items', 'feedUpdated'))->render();
            } finally {
                app()->setLocale($locale);
            }
        });
    }

    /**
     * Personal RSS feed of the latest observations of the observers a user
     * follows. The URL holds the user's secret token instead of a login, so
     * feed readers can poll it.
     */
    public function following(string $token): Response
    {
        $user = User::where('follow_feed_token', $token)->firstOrFail();

        return $this->feed(self::followingKey($user), fn() => $this->renderObservations(false, $user));
    }

    /**
     * Forget the cached personal feed, after the user followed or unfollowed
     * someone.
     */
    public static function forgetFollowingFeed(User $user): void
    {
        Cache::forget(self::followingKey($user));
    }

    private static function followingKey(User $user): string
    {
        return 'rss:following:' . $user->id;
    }

    private function renderObservations(bool $drawingsOnly, ?User $follower = null): string
    {
        $observations = ($follower ? $follower->followedObservations() : Observation::query())
            ->with(['instrument.instrument_make', 'eyepiece', 'observerUser'])
            ->when($drawingsOnly, fn($query) => $query->where('has_drawing', true))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // The locations (for the local time) and the observations of the same
        // day (for the index in the detail URLs) in a few queries.
        $cards = ObservationCardData::instance()->preload($observations);

        $feedUpdated = $observations->first()?->created_at ?? now();

        $channel = $follower
            ? [
                'title' => 'Observers followed by ' . $follower->name,
                'link' => url('/observations?following=1'),
                'description' => 'The latest observations of the observers ' . $follower->name . ' follows on DeepskyLog.',
                'self' => route('feed.following', ['token' => $follower->follow_feed_token]),
            ]
            : null;

        return view('rss.observations', compact('observations', 'feedUpdated', 'cards', 'channel'))->render();
    }

    private function feed(string $cacheKey, \Closure $render): Response
    {
        return response(Cache::remember($cacheKey, self::CACHE_SECONDS, $render), 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }
}
