<?php

use App\Jobs\TranslateNewsItem;
use App\Models\NewsItem;
use App\Services\News\AstronomicalEvents;
use App\Services\News\Eclipses;
use App\Services\News\LocalCircumstances;
use App\Services\Observations\DescriptionTranslator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

// The news of the home page (#960): items written by administrators and
// database experts, and the coming astronomical events added every night.

function newsTimestamp(string $moment): int
{
    return Carbon::parse($moment, 'UTC')->timestamp;
}

function newsEvents(string $from, string $to, ?string $kind = null): array
{
    $events = (new AstronomicalEvents())->between(newsTimestamp($from), newsTimestamp($to));

    return array_values(array_filter($events, fn ($event) => $kind === null || $event['kind'] === $kind));
}

function newsMessier44(): void
{
    DB::table('objects')->insert([
        'name'           => 'M 44', 'slug' => 'm-44', 'type' => 'OPNCL', 'con' => 'CNC', 'ra' => 8.66589, 'decl' => 19.6725,
        'mag'            => 3.1, 'subr' => 0, 'pa' => null, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
        'diam1'          => 5700.0, 'diam2' => 5700.0, 'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '',
        'psa'            => '', 'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'          => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0',
        'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    DB::table('objectnames')->insert([
        'objectname' => 'M 44', 'catalog' => 'M', 'catindex' => '44', 'altname' => 'M 44', 'slug' => 'm-44', 'timestamp' => now(),
    ]);
}

function newsItem(array $attributes = []): NewsItem
{
    return NewsItem::create($attributes + [
        'kind'         => NewsItem::MANUAL,
        'title'        => 'New members of the Hickson groups',
        'body'         => '<p>All members of the <strong>Hickson</strong> groups are in DeepskyLog.</p>',
        'language'     => 'en',
        'published_at' => now()->subDay(),
    ]);
}

it('finds the eclipses of 2026 and 2027 as NASA lists them', function () {
    $eclipses = Eclipses::between(newsTimestamp('2026-01-01'), newsTimestamp('2028-01-01'));
    $found    = array_map(fn ($e) => [$e['kind'], $e['data']['type'], $e['at']], $eclipses);

    // Fred Espenak's canon (NASA): the moment of greatest eclipse in UT
    $expected = [
        ['solar_eclipse', 'annular', '2026-02-17 12:13'],
        ['lunar_eclipse', 'total', '2026-03-03 11:33'],
        ['solar_eclipse', 'total', '2026-08-12 17:46'],
        ['lunar_eclipse', 'partial', '2026-08-28 04:13'],
        ['solar_eclipse', 'annular', '2027-02-06 16:00'],
        ['lunar_eclipse', 'penumbral', '2027-02-20 23:13'],
        ['solar_eclipse', 'total', '2027-08-02 10:07'],
        ['lunar_eclipse', 'penumbral', '2027-08-17 07:14'],
    ];
    expect($found)->toHaveCount(count($expected));
    foreach ($expected as $i => [$kind, $type, $moment]) {
        expect($found[$i][0])->toBe($kind)
            ->and($found[$i][1])->toBe($type)
            ->and(abs($found[$i][2] - newsTimestamp($moment)))->toBeLessThan(300);
    }

    // The point of greatest eclipse of 2026 August 12: 65.2 N, 25.2 W
    $august = $eclipses[2]['data'];
    expect($august['latitude'])->toEqualWithDelta(65.2, 1.0)
        ->and($august['longitude'])->toEqualWithDelta(-25.2, 1.5);
});

it('computes the local circumstances of the solar eclipse of 2026 August 12', function () {
    $at = newsTimestamp('2026-08-12 17:46');

    // Brussels: partial, greatest at 18:13 UT with magnitude 0.92 low in the west
    $brussels = Eclipses::localSolar($at, 50.85, 4.35);
    expect($brussels['type'])->toBe('partial')
        ->and($brussels['magnitude'])->toEqualWithDelta(0.91, 0.02)
        ->and(abs($brussels['max'] - newsTimestamp('2026-08-12 18:13')))->toBeLessThan(180)
        ->and($brussels['visible'])->toBeTrue();

    // León lies in the path of totality, Sydney sees nothing
    expect(Eclipses::localSolar($at, 42.6, -5.57)['type'])->toBe('total')
        ->and(Eclipses::localSolar($at, -33.9, 151.2))->toBeNull();
});

it('computes the contacts of the lunar eclipse of 2026 August 28', function () {
    $eclipse  = Eclipses::between(newsTimestamp('2026-08-20'), newsTimestamp('2026-09-01'))[0];
    $contacts = $eclipse['data']['contacts'];

    foreach (['p1' => '01:23', 'u1' => '02:33', 'u4' => '05:52', 'p4' => '07:02'] as $contact => $time) {
        expect(abs($contacts[$contact] - newsTimestamp('2026-08-28 ' . $time)))->toBeLessThan(180);
    }
    expect($contacts['u2'])->toBeNull();

    // In Belgium the Moon sets before the end of the partial phase
    $altitudes = Eclipses::localLunar($contacts, 50.85, 4.35);
    expect($altitudes['u1'])->toBeGreaterThan(10)
        ->and($altitudes['u4'])->toBeLessThan(0);
});

it('finds oppositions, greatest elongations and meteor showers', function () {
    $jupiter = newsEvents('2026-01-05', '2026-01-15', 'opposition');
    expect($jupiter)->toHaveCount(1)
        ->and($jupiter[0]['data']['bodies'])->toBe(['jupiter'])
        ->and(gmdate('Y-m-d', $jupiter[0]['at']))->toBe('2026-01-10');

    $venus = newsEvents('2026-08-10', '2026-08-20', 'greatest_elongation');
    expect($venus)->toHaveCount(1)
        ->and($venus[0]['data']['side'])->toBe('east')
        ->and($venus[0]['data']['elongation'])->toEqualWithDelta(45.9, 0.2)
        ->and(gmdate('Y-m-d', $venus[0]['at']))->toBe('2026-08-15');

    $perseids = newsEvents('2026-08-10', '2026-08-20', 'meteor_shower');
    expect($perseids)->toHaveCount(1)
        ->and($perseids[0]['key'])->toBe('meteor_shower:perseids:2026')
        ->and(gmdate('Y-m-d', $perseids[0]['at']))->toBeIn(['2026-08-12', '2026-08-13']);
});

it('finds close conjunctions of planets, the Moon and Messier objects', function () {
    // Saturn passes Neptune on 2026 February 20
    $planets = newsEvents('2026-02-15', '2026-02-25', 'planet_conjunction');
    expect($planets)->toHaveCount(1)
        ->and($planets[0]['data']['bodies'])->toBe(['saturn', 'neptune'])
        ->and($planets[0]['data']['separation'])->toBeLessThan(1.0);

    // The Moon close to Venus on 2026 June 17, an occultation for parts of the Earth
    $moon = newsEvents('2026-06-15', '2026-06-20', 'moon_conjunction');
    expect(collect($moon)->pluck('data.bodies')->all())->toContain(['moon', 'venus'])
        ->and(collect($moon)->firstWhere('data.bodies.1', 'venus')['data']['occultation'])->toBeTrue();

    // Mars in the Beehive on 2026 October 11
    newsMessier44();
    $deepsky = newsEvents('2026-10-05', '2026-10-15', 'deepsky_conjunction');
    expect($deepsky)->toHaveCount(1)
        ->and($deepsky[0]['data']['object']['name'])->toBe('M 44')
        ->and($deepsky[0]['data']['separation'])->toBeLessThan(0.2);
});

it('gives the best moment of a conjunction for a location', function () {
    $event = collect(newsEvents('2026-06-15', '2026-06-20', 'moon_conjunction'))->firstWhere('data.bodies.1', 'venus');

    $local = LocalCircumstances::compute('moon_conjunction', $event['at'], $event['data'], 50.85, 4.35);

    // In the evening twilight of June 17, low in the west
    expect($local['best'])->not->toBeNull()
        ->and(gmdate('Y-m-d', $local['best']['at']))->toBe('2026-06-17')
        ->and($local['best']['altitude'])->toBeGreaterThanOrEqual(LocalCircumstances::MIN_ALTITUDE);
});

it('adds the coming events as news items a week before them, once', function () {
    $this->travelTo(Carbon::parse('2026-08-01 12:00', 'UTC'));

    $this->artisan('news:astronomical-events', ['--days' => 20])->assertSuccessful();
    expect(NewsItem::count())->toBe(0);

    $this->artisan('news:astronomical-events', ['--days' => 20, '--apply' => true])->assertSuccessful();
    $eclipse = NewsItem::where('event_key', 'solar_eclipse:2026-08-12')->firstOrFail();
    expect($eclipse->kind)->toBe('solar_eclipse')
        ->and($eclipse->published_at->toDateString())->toBe('2026-08-05')
        ->and($eclipse->expires_at->toDateString())->toBe('2026-08-13')
        ->and($eclipse->data['type'])->toBe('total');

    $count = NewsItem::count();
    $eclipse->forceFill(['hidden' => true])->save();
    NewsItem::create(['kind' => 'opposition', 'event_key' => 'opposition:pluto:2026-08-15', 'event_at' => '2026-08-15 00:00:00', 'published_at' => '2026-08-08 00:00:00']);

    $this->artisan('news:astronomical-events', ['--days' => 20, '--apply' => true])->assertSuccessful();
    expect(NewsItem::count())->toBe($count)
        ->and($eclipse->fresh()->hidden)->toBeTrue()
        ->and(NewsItem::where('event_key', 'opposition:pluto:2026-08-15')->exists())->toBeFalse();
});

it('shows the current news on the home page and on /news', function () {
    $current = newsItem();
    newsItem(['title' => 'Coming soon', 'published_at' => now()->addDay()]);
    newsItem(['title' => 'Not wanted', 'hidden' => true]);
    newsItem(['title' => 'Old news', 'published_at' => now()->subMonth(), 'expires_at' => now()->subWeek()]);

    $this->get('/')->assertOk()
        ->assertSee('New members of the Hickson groups')
        ->assertDontSee('Coming soon')
        ->assertDontSee('Not wanted')
        ->assertDontSee('Old news');

    $this->get(route('news.index'))->assertOk()
        ->assertSee('New members of the Hickson groups')
        ->assertSee('Old news')
        ->assertDontSee('Coming soon');

    $this->get(route('news.show', $current))->assertOk()->assertSee('All members of the', false);
    $this->get(route('news.show', NewsItem::where('title', 'Coming soon')->first()))->assertNotFound();

    $this->get(route('feed.news'))->assertOk()->assertSee('New members of the Hickson groups');
});

it('writes the text of an automatic item in the language of the reader', function () {
    $this->travelTo(Carbon::parse('2026-08-08 12:00', 'UTC'));
    $item = NewsItem::create([
        'kind'         => 'lunar_eclipse',
        'event_key'    => 'lunar_eclipse:2026-08-28',
        'event_at'     => '2026-08-28 04:13:00',
        'data'         => Eclipses::between(newsTimestamp('2026-08-20'), newsTimestamp('2026-09-01'))[0]['data'],
        'published_at' => now()->subDay(),
    ]);

    expect($item->displayTitle())->toBe('Partial lunar eclipse on 28 August 2026');
    app()->setLocale('nl');
    expect($item->displayTitle())->toBe('Gedeeltelijke maansverduistering op 28 augustus 2026');
    app()->setLocale('en');

    $this->get(route('news.show', $item))->assertOk()
        ->assertSee('Partial eclipse begins')
        ->assertSee('Umbral magnitude 0.925', false);
});

it('lets only administrators and database experts manage the news', function (string $team, int $status) {
    $this->actingAs($this->createUserAndAssignToTeam($team))
        ->get(route('admin.news'))
        ->assertStatus($status);
})->with([
    ['Observers', 403],
    ['Database Experts', 200],
    ['Administrators', 200],
]);

it('saves a written item and queues its translation', function () {
    Queue::fake();
    config(['app.translate_observations' => true]);
    $this->actingAs($this->createUserAndAssignToTeam('Database Experts'));

    $this->post(route('admin.news.store'), [
        'title'        => 'Hickson',
        'body'         => '<p>New <b>members</b></p><script>alert(1)</script>',
        'language'     => 'en',
        'published_at' => '2026-10-25T10:00',
    ])->assertRedirect(route('admin.news'));

    $item = NewsItem::firstOrFail();
    expect($item->body)->not->toContain('<script')
        ->and($item->user_id)->toBe(2);
    Queue::assertPushed(TranslateNewsItem::class, fn ($job) => $job->newsItemId === $item->id);

    // Saving the same text again keeps the translations
    Queue::fake();
    $this->put(route('admin.news.update', $item), [
        'title' => 'Hickson', 'body' => $item->body, 'language' => 'en', 'published_at' => '2026-10-26T10:00',
    ])->assertRedirect();
    Queue::assertNothingPushed();
});

it('hides an automatic item but does not delete it', function () {
    $this->actingAs($this->createUserAndAssignToTeam('Administrators'));
    $item = NewsItem::create(['kind' => 'opposition', 'event_key' => 'opposition:saturn:2026-10-04', 'event_at' => '2026-10-04 12:13:00',
        'data'                       => ['bodies' => ['saturn'], 'magnitude' => 0.5, 'distance' => 8.434, 'decl' => 2.0], 'published_at' => now()->subDay()]);

    $this->delete(route('admin.news.destroy', $item))->assertNotFound();
    $this->post(route('admin.news.hidden', $item))->assertRedirect();
    expect($item->fresh()->hidden)->toBeTrue();
});

it('translates a written item into the other languages of the site', function () {
    config(['app.translate_observations' => true]);
    $item = newsItem();
    $this->app->instance(DescriptionTranslator::class, new class () extends DescriptionTranslator {
        public function translate(string $text, ?string $source, string $target): ?string
        {
            return '[' . $target . '] ' . $text;
        }
    });

    (new TranslateNewsItem($item->id))->handle(app(DescriptionTranslator::class));

    $item->refresh();
    expect(array_keys($item->translations))->toEqualCanonicalizing(['de', 'fr', 'es', 'nl', 'sv']);
    app()->setLocale('nl');
    expect($item->displayTitle())->toBe('[nl] New members of the Hickson groups')
        ->and($item->isTranslated())->toBeTrue();
    app()->setLocale('en');
    expect($item->displayTitle())->toBe('New members of the Hickson groups')
        ->and($item->isTranslated())->toBeFalse();
});

it('shows the form to write and edit an item', function () {
    $this->actingAs($this->createUserAndAssignToTeam('Administrators'));
    $item = newsItem();

    $this->get(route('admin.news.create'))->assertOk()->assertSee('Write a news item');
    $this->get(route('admin.news.edit', $item))->assertOk()->assertSee('New members of the Hickson groups');
    $this->get(route('admin.news'))->assertOk()->assertSee('New members of the Hickson groups')->assertSee('Published');
});

it('announces the Moon near a Messier object only when the object is bright enough for the phase', function () {
    expect(AstronomicalEvents::moonDeepskyLimit(0.0))->toBe(AstronomicalEvents::MOON_DEEPSKY_NEW)
        ->and(AstronomicalEvents::moonDeepskyLimit(1.0))->toEqualWithDelta(AstronomicalEvents::MOON_DEEPSKY_FULL, 0.001);

    // The thin crescent passes the Beehive (magnitude 3.1) on 2026 June 18
    newsMessier44();
    $crescent = newsEvents('2026-06-15', '2026-06-20', 'moon_deepsky_conjunction');
    expect($crescent)->toHaveCount(1)
        ->and($crescent[0]['data']['object']['name'])->toBe('M 44')
        ->and($crescent[0]['data']['moon_illumination'])->toBeLessThan(0.2);

    // ... but on 2026 October 5 the Moon is 29% illuminated: too bright for an object of magnitude 3.5
    DB::table('objects')->where('name', 'M 44')->update(['mag' => 3.5]);
    expect(newsEvents('2026-10-03', '2026-10-08', 'moon_deepsky_conjunction'))->toBe([]);
});

it('announces a comet at its brightest while it can be seen', function () {
    $orbit = ['epoch' => 2461332.5, 'q' => 0.781924, 'e' => 0.954748, 'i' => 74.1467, 'w' => 199.0356, 'node' => 255.8679, 'Tp' => 20240421.2071, 'ref' => 'test'];
    DB::table('comets_orbital_elements')->insert([
        $orbit + ['name' => '12P/Pons-Brooks', 'H' => 5, 'n' => 13.5, 'light_curve' => json_encode([
            ['from' => null, 'to' => -840, 'H' => 12.5, 'K' => 5, 'shift' => 0],
            ['from' => -840, 'to' => -276, 'H' => 5, 'K' => 13.5, 'shift' => 0],
            ['from' => -276, 'to' => -158, 'H' => 9, 'K' => 0, 'shift' => 0],
            ['from' => -158, 'to' => -100, 'H' => 8.5, 'K' => -5, 'shift' => 0],
            ['from' => -100, 'to' => 14, 'H' => 4.6, 'K' => 9.5, 'shift' => 0],
            ['from' => 14, 'to' => 175, 'H' => 4.3, 'K' => 11, 'shift' => 10],
            ['from' => 175, 'to' => null, 'H' => 5, 'K' => 13.5, 'shift' => 0],
        ])],
        // H without its coefficient of log r: the default would make it very bright
        $orbit + ['name' => 'P/2099 X1 (Bogus)', 'H' => -14.5, 'n' => null, 'light_curve' => null],
    ]);

    // 12P was brightest (about magnitude 4.5) low in the evening sky around its perihelion of 2024 April 21
    $comets = newsEvents('2024-04-01', '2024-05-10', 'comet');
    expect($comets)->toHaveCount(1)
        ->and($comets[0]['key'])->toBe('comet:12ppons-brooks:2024-04-21:evening')
        ->and($comets[0]['data']['magnitude'])->toEqualWithDelta(4.6, 0.5)
        ->and($comets[0]['data']['elongation'])->toBeGreaterThanOrEqual(\App\Services\News\BrightComets::MIN_ELONGATION);

    $item = NewsItem::create(['kind' => 'comet', 'event_key' => $comets[0]['key'], 'event_at' => now()->setTimestamp($comets[0]['at']),
        'data' => $comets[0]['data'], 'published_at' => now()->subDay()]);
    $this->get(route('news.show', $item))->assertOk()
        ->assertSee('Comet 12P/Pons-Brooks at its brightest')
        ->assertSee('in the evening sky');
});
