<?php

use App\Http\Middleware\StartSession;
use App\Http\ResponseCache\TeamAwareCacheProfile;
use App\Http\ResponseCache\TeamAwareHasher;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\User;
use App\Support\EphemerisDate;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Event;
use Spatie\ResponseCache\Events\ClearingResponseCache;

function responseCacheGuestRequest(string $uri, array $session = []): Request
{
    $store = new Store('test', new ArraySessionHandler(60));
    $store->put(array_merge(['_token' => 'abc', '_previous' => ['url' => 'http://localhost/']], $session));
    $request = Request::create($uri);
    $request->setLaravelSession($store);

    return $request;
}

it('does not store a session or set a session cookie for crawlers without cookies', function () {
    $cookie = config('session.cookie');

    $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
        ->get('/')
        ->assertOk()
        ->assertCookieMissing($cookie);

    $this->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0 Safari/537.36')
        ->get('/')
        ->assertOk()
        ->assertCookie($cookie);
});

it('recognises crawlers by their user agent', function (string $userAgent, bool $crawler) {
    $request = Request::create('/');
    $request->headers->set('User-Agent', $userAgent);

    expect(StartSession::isCrawler($request))->toBe($crawler);
})->with([
    'googlebot' => ['Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', true],
    'bingbot' => ['Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)', true],
    'ahrefs' => ['Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)', true],
    'curl' => ['curl/8.5.0', true],
    'empty' => ['', true],
    'firefox' => ['Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:130.0) Gecko/20100101 Firefox/130.0', false],
    'safari' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1', false],
]);

it('uses the response cache for guests with a session cookie', function () {
    config(['responsecache.enabled' => true]);
    $profile = app(TeamAwareCacheProfile::class);

    $request = responseCacheGuestRequest('/observers', ['locale' => 'nl', EphemerisDate::SESSION_KEY => '2026-09-30', EphemerisDate::SESSION_UNTIL_KEY => now()->addDay()->toIso8601String()]);
    $request->cookies->set(config('session.cookie'), 'some-session-id');

    expect($profile->enabled($request))->toBeTrue()
        ->and($profile->shouldCacheRequest($request))->toBeTrue();
});

it('does not use the response cache when the session holds state that changes the page', function () {
    config(['responsecache.enabled' => true]);

    expect(app(TeamAwareCacheProfile::class)->enabled(responseCacheGuestRequest('/observers', ['pg7:observation-search' => ['filters' => ['x']]])))
        ->toBeFalse();
});

it('does not use the response cache for logged-in users', function () {
    config(['responsecache.enabled' => true]);
    $user = User::factory()->create();
    $request = responseCacheGuestRequest('/observers');
    $request->setUserResolver(fn () => $user);

    expect(app(TeamAwareCacheProfile::class)->enabled($request))->toBeFalse();
});

it('keys the response cache on the whitelisted query parameters only', function () {
    config(['responsecache.enabled' => true]);
    $hasher = app(TeamAwareHasher::class);
    $profile = app(TeamAwareCacheProfile::class);

    $plain = $hasher->getHashFor(responseCacheGuestRequest('/observers?sort=name&direction=desc'));

    expect($hasher->getHashFor(responseCacheGuestRequest('/observers?direction=desc&sort=name')))->toBe($plain)
        ->and($hasher->getHashFor(responseCacheGuestRequest('/observers?sort=name&direction=desc&utm_source=newsletter&fbclid=x')))->toBe($plain)
        ->and($hasher->getHashFor(responseCacheGuestRequest('/observers?sort=name&direction=asc')))->not->toBe($plain)
        ->and($profile->enabled(responseCacheGuestRequest('/observers?sort=name&utm_medium=mail')))->toBeTrue()
        ->and($profile->enabled(responseCacheGuestRequest('/observers?drawingsPage=3')))->toBeTrue()
        ->and($profile->enabled(responseCacheGuestRequest('/observers?junk=1')))->toBeFalse();
});

it('keys the response cache on the ephemeris date', function () {
    $hasher = app(TeamAwareHasher::class);
    $request = Request::create('/observers');

    EphemerisDate::choose('2026-01-01');
    $january = $hasher->getHashFor($request);
    EphemerisDate::choose('2026-02-01');

    expect($hasher->getHashFor($request))->not->toBe($january);
});

it('does not clear the response cache for changes guests cannot see', function () {
    $owner = User::factory()->create();

    Event::fake([ClearingResponseCache::class]);
    $owner->forceFill(['stdeyepiece' => 12, 'overviewFoV' => 120, 'sendMail' => 0])->save();
    $list = ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Private list', 'public' => false]);
    ObservingListItem::create(['observing_list_id' => $list->id, 'object_name' => 'M 31', 'added_by_user_id' => $owner->id]);
    $list->update(['name' => 'Still private']);
    $session = ObservationSession::create(['name' => 'Draft', 'observerid' => $owner->username, 'begindate' => '2026-09-01 20:00:00',
        'enddate' => '2026-09-02 02:00:00', 'locationid' => 0, 'active' => 0]);
    $session->update(['name' => 'Still a draft']);
    Event::assertNotDispatched(ClearingResponseCache::class);

    $list->update(['public' => true]);
    Event::assertDispatched(ClearingResponseCache::class);

    Event::fake([ClearingResponseCache::class]);
    $list->update(['public' => false]);
    Event::assertDispatched(ClearingResponseCache::class);

    Event::fake([ClearingResponseCache::class]);
    $session->update(['active' => 1]);
    Event::assertDispatched(ClearingResponseCache::class);
});
