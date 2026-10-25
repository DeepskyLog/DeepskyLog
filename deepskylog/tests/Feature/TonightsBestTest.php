<?php

use App\Jobs\ComputeContrastReserveForObjectsBatch;
use App\Livewire\NightListWizard;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\MoonlightSky;
use App\Services\NightWindow;
use App\Services\TonightsBest;
use Carbon\Carbon;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;

/** A night of new Moon (2026-11-09) and one of (nearly) full Moon (2026-10-24). */
const TB_NEW_MOON = '2026-11-09';
const TB_FULL_MOON = '2026-10-24';

function tbObject(string $name, float $ra, float $decl, string $type = 'GALXY', int $observations = 0, float $mag = 8.0): void
{
    DB::table('objects')->insert([
        'name' => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'AND',
        'ra' => $ra, 'decl' => $decl, 'mag' => $mag, 'subr' => 13.0, 'pa' => 0, 'urano' => 0,
        'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 300.0, 'diam2' => 200.0,
        'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    if ($observations > 0) {
        DB::table('object_observation_stats')->insert(['object_name' => $name, 'observations' => $observations]);
    }
}

/**
 * Objects of the autumn sky, well placed in the evening of November at 51° N
 * (RA in hours), and one that never rises there.
 */
function tbAutumnSky(): void
{
    DB::table('deepskytypes')->insertOrIgnore([['code' => 'OPNCL', 'name' => 'Open cluster'], ['code' => 'GALXY', 'name' => 'Galaxy']]);
    tbObject('Autumn Galaxy 1', 0.7, 41.0, 'GALXY', 900);
    tbObject('Autumn Galaxy 2', 1.5, 30.0, 'GALXY', 800);
    tbObject('Autumn Cluster 1', 2.3, 57.0, 'OPNCL', 700);
    tbObject('Autumn Cluster 2', 3.8, 24.0, 'OPNCL', 600);
    tbObject('Autumn Nebula', 22.9, 42.0, 'PLNNB', 500);
    tbObject('Autumn Globular', 21.5, 12.0, 'GLOCL', 400);
    tbObject('Faint Galaxy', 1.0, 35.0, 'GALXY', 10, 13.5);
    tbObject('Southern Galaxy', 1.0, -70.0, 'GALXY', 1000);
    tbObject('Bright Star', 1.2, 40.0, 'AA1STAR', 1000);
}

function tbObserver(): User
{
    // in the Observer role, which makes observing lists
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'aperture_mm' => 300, 'focal_length_mm' => 1500, 'fixedMagnification' => null]);
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'latitude' => 50.85, 'longitude' => 4.35,
        'timezone' => 'Europe/Brussels', 'skyBackground' => 21.5]);
    $user->forceFill(['stdtelescope' => $instrument->id, 'stdlocation' => $location->id])->save();

    return $user->fresh();
}

/**
 * Contrast reserves of the observer's equipment for all objects.
 */
function tbMetrics(User $user, array $reserves): void
{
    foreach ($reserves as $name => $reserve) {
        DB::table('user_object_metrics')->insert(['user_id' => $user->id, 'instrument_id' => $user->stdtelescope, 'location_id' => $user->stdlocation,
            'lens_id' => null, 'object_name' => $name, 'contrast_reserve' => $reserve, 'created_at' => now(), 'updated_at' => now()]);
    }
}

function tbSeen(User $user, string $object, ?int $instrumentId): void
{
    DB::table('observations')->insert(['observer_username' => $user->username, 'observed_on' => '2025-10-01', 'instrument_id' => $instrumentId,
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => $object, 'target_name' => $object,
        'has_drawing' => false, 'language' => 'en', 'legacy_source' => 'deepsky', 'created_at' => now(), 'updated_at' => now()]);
}

it('finds the highest altitude in the dark hours as stepping through the night does', function () {
    $window = NightWindow::for(50.85, 4.35, TB_NEW_MOON, 'Europe/Brussels');
    expect($window->twilight)->toBe('astronomical');

    foreach ([[0.7, 41.0], [5.6, -5.4], [16.7, 36.5], [12.0, 80.0], [20.0, -30.0]] as [$ra, $decl]) {
        $stepped = -90.0;
        for ($t = $window->start; $t <= $window->end; $t += 60) {
            $stepped = max($stepped, $window->altitude($ra * 15.0, $decl, $t));
        }
        expect($window->highest($ra * 15.0, $decl)['altitude'])->toEqualWithDelta($stepped, 0.01);
    }

    // A part of the night starts with the sidereal time of its start.
    $later = $window->between($window->start + 3600, $window->end);
    expect($later->altitude(10.0, 20.0, $window->start + 7200))->toEqualWithDelta($window->altitude(10.0, 20.0, $window->start + 7200), 1e-6);
    expect($window->between($window->end + 60, $window->end + 3600))->toBeNull();
});

it('brightens the sky with the Moon', function () {
    // The full Moon 45° from a point at 45° altitude: a sky of about 18 at a dark site.
    $sky = MoonlightSky::toSqm(MoonlightSky::toNanoLambert(21.5) + MoonlightSky::moonlight(1.0, 45.0, 45.0, 45.0));
    expect($sky)->toBeGreaterThan(17.5)->toBeLessThan(19.0);

    // A thin crescent hardly matters.
    $crescent = MoonlightSky::toSqm(MoonlightSky::toNanoLambert(21.5) + MoonlightSky::moonlight(0.05, 60.0, 45.0, 90.0));
    expect($crescent)->toBeGreaterThan(21.3);

    // Without the Moon above the horizon the sky stays dark.
    $window = NightWindow::for(50.85, 4.35, TB_NEW_MOON, 'Europe/Brussels');
    $moon = new MoonlightSky($window, 4.35);
    expect($moon->isUp())->toBeFalse()
        ->and($moon->sqmAt($window->start, 10.0, 41.0, 21.5))->toBe(21.5);
});

it('shows the most observed well placed objects to guests', function () {
    tbAutumnSky();
    LocationFactory::new()->create(['latitude' => 50.85, 'longitude' => 4.35, 'timezone' => 'Europe/Brussels', 'observer' => 'someone']);

    $result = TonightsBest::for(null, ['night' => TB_NEW_MOON]);
    $names = collect($result['objects'])->where('kind', 'deepsky')->pluck('name');

    expect($result['mode'])->toBe('popular')
        ->and($result['moon'])->toBeNull()
        ->and($names)->toContain('Autumn Galaxy 1', 'Autumn Cluster 2', 'Autumn Nebula')
        ->and($names)->not->toContain('Southern Galaxy', 'Bright Star')
        ->and(collect($result['objects'])->pluck('status')->unique()->all())->toBe([null]);
    // Planets are calculated as well, and the list is in the order to observe it.
    expect(collect($result['objects'])->where('kind', 'planet')->pluck('name'))->toContain('Saturn');
    $timestamps = array_column($result['objects'], 'timestamp');
    expect($timestamps)->toBe(collect($timestamps)->sort()->values()->all());

    Carbon::setTestNow(Carbon::parse(TB_NEW_MOON.' 15:00', 'Europe/Brussels'));
    $this->get('/')
        ->assertOk()
        ->assertSee("Tonight's best")
        ->assertSee('Autumn Galaxy 1')
        ->assertSee('Open cluster')
        ->assertDontSee('Make an observing list');
    Carbon::setTestNow();
});

it('makes a personal list of mostly new objects with only a few seen ones', function () {
    Queue::fake();
    tbAutumnSky();
    // Of other types than the new objects, so the variety of types does not keep them out.
    foreach (['BRTNB', 'EMINB', 'REFNB', 'HII', 'SNREM', 'ASTER', 'GALCL', 'WRNEB', 'DRKNB', 'GXADN', 'GACAN', 'GXAGC'] as $i => $type) {
        tbObject('Seen Galaxy '.($i + 1), 0.6 + $i / 10, 40.0, $type, 2001 + $i);
    }
    tbObject('Not Yet Computed', 1.1, 38.0, 'GALXY', 50);
    $user = tbObserver();
    $reserves = ['Autumn Galaxy 1' => 1.4, 'Autumn Galaxy 2' => 0.8, 'Autumn Cluster 1' => 2.0, 'Autumn Cluster 2' => 2.5, 'Autumn Nebula' => 0.4,
        'Autumn Globular' => 1.1, 'Faint Galaxy' => 0.2, 'Southern Galaxy' => 1.0];
    foreach (range(1, 12) as $i) {
        $reserves["Seen Galaxy {$i}"] = 1.5;
        tbSeen($user, "Seen Galaxy {$i}", $user->stdtelescope);
    }
    tbMetrics($user, $reserves);
    // Seen with another telescope only: still new for this one.
    tbSeen($user, 'Autumn Galaxy 2', null);

    $result = TonightsBest::for($user, ['night' => TB_NEW_MOON]);
    $deepsky = collect($result['objects'])->where('kind', 'deepsky');

    expect($result['mode'])->toBe('personal')
        ->and($deepsky->where('status', 'seen')->count())->toBe(3)
        ->and($deepsky->where('status', '!=', 'seen')->pluck('name'))->toContain('Autumn Galaxy 1', 'Autumn Galaxy 2', 'Autumn Cluster 2', 'Faint Galaxy')
        ->and($deepsky->firstWhere('name', 'Autumn Galaxy 2')['status'])->toBe('seen_other')
        ->and($deepsky->firstWhere('name', 'Faint Galaxy')['category'])->toBe('difficult')
        ->and($deepsky->pluck('name'))->not->toContain('Southern Galaxy');

    // The object without a contrast reserve is queued, not computed in the request.
    expect($result['pending'])->toBeTrue();
    Queue::assertPushed(ComputeContrastReserveForObjectsBatch::class, fn ($job) => in_array('Not Yet Computed', $job->objectNames, true));

    // Only new objects when asked.
    $new = TonightsBest::for($user, ['night' => TB_NEW_MOON, 'seen' => 'none']);
    expect(collect($new['objects'])->where('status', 'seen')->count())->toBe(0);
});

it('lowers the contrast reserve under the full Moon', function () {
    Queue::fake();
    tbAutumnSky();
    $user = tbObserver();
    tbMetrics($user, ['Autumn Galaxy 1' => 0.9, 'Autumn Galaxy 2' => 0.9, 'Autumn Cluster 1' => 0.9, 'Autumn Cluster 2' => 0.9, 'Autumn Nebula' => 0.9,
        'Autumn Globular' => 0.9, 'Faint Galaxy' => 0.9, 'Southern Galaxy' => 0.9]);

    $dark = TonightsBest::for($user, ['night' => TB_NEW_MOON]);
    $moonlit = TonightsBest::for($user, ['night' => TB_FULL_MOON]);

    expect($moonlit['moon']['fraction'])->toBeGreaterThan(0.9);
    $galaxy = collect($moonlit['objects'])->firstWhere('name', 'Autumn Galaxy 1');
    // The stored reserve holds for a dark sky; the Moon brightens it at the object.
    expect(collect($dark['objects'])->firstWhere('name', 'Autumn Galaxy 1')['contrast_reserve'])->toBe(0.9)
        ->and($galaxy['sqm'])->toBeLessThan(20.5)
        ->and($galaxy['contrast_reserve'])->toBeLessThan(0.9);
});

it('puts the objects of the chosen sources first, or only them', function () {
    Queue::fake();
    tbAutumnSky();
    $user = tbObserver();
    tbMetrics($user, ['Autumn Galaxy 1' => 1.4, 'Autumn Galaxy 2' => 0.8, 'Autumn Cluster 1' => 2.0, 'Autumn Cluster 2' => 2.5, 'Autumn Nebula' => 0.6,
        'Autumn Globular' => 1.1, 'Faint Galaxy' => 0.7]);
    $list = ObservingList::createWithObjects($user, 'Favourites', ['Faint Galaxy', 'Autumn Globular']);

    $first = TonightsBest::for($user, ['night' => TB_NEW_MOON, 'sources' => ['list:'.$list->id], 'count' => 5, 'planets' => false, 'comets' => false]);
    expect(collect($first['objects'])->pluck('name'))->toContain('Faint Galaxy', 'Autumn Globular')->toHaveCount(5);

    $only = TonightsBest::for($user, ['night' => TB_NEW_MOON, 'sources' => ['list:'.$list->id], 'sources_only' => true]);
    expect(collect($only['objects'])->pluck('name')->sort()->values()->all())->toBe(['Autumn Globular', 'Faint Galaxy']);

    $clusters = TonightsBest::for($user, ['night' => TB_NEW_MOON, 'types' => ['OPNCL'], 'planets' => false, 'comets' => false]);
    expect(collect($clusters['objects'])->pluck('name')->sort()->values()->all())->toBe(['Autumn Cluster 1', 'Autumn Cluster 2']);

    // Another observer's private list gives no objects.
    $private = ObservingList::createWithObjects(User::factory()->create(), 'Secret', ['Autumn Nebula']);
    $none = TonightsBest::for($user, ['night' => TB_NEW_MOON, 'sources' => ['list:'.$private->id], 'sources_only' => true]);
    expect($none['objects'])->toBe([]);
});

it('keeps to the part of the night that was chosen', function () {
    Queue::fake();
    tbAutumnSky();
    $user = tbObserver();

    $evening = TonightsBest::for($user, ['night' => TB_NEW_MOON, 'period' => 'evening', 'planets' => false, 'comets' => false]);
    $custom = TonightsBest::for($user, ['night' => TB_NEW_MOON, 'period' => 'custom', 'from' => '23:00', 'until' => '01:30', 'planets' => false, 'comets' => false]);

    expect($evening['period']['from'])->toBe($evening['dark']['from'])
        ->and($custom['period'])->toBe(['from' => '23:00', 'until' => '01:30']);
    foreach ($custom['objects'] as $object) {
        expect($object['time'] >= '23:00' || $object['time'] <= '01:30')->toBeTrue();
    }
});

it('makes an observing list of tonight\'s best from the home page', function () {
    Queue::fake();
    tbAutumnSky();
    $user = tbObserver();
    tbMetrics($user, ['Autumn Galaxy 1' => 1.4, 'Autumn Galaxy 2' => 0.8, 'Autumn Cluster 1' => 2.0, 'Autumn Cluster 2' => 2.5]);
    Carbon::setTestNow(Carbon::parse(TB_NEW_MOON.' 15:00', 'Europe/Brussels'));

    $this->actingAs($user)->get('/')
        ->assertOk()
        ->assertSee('Make an observing list')
        ->assertSee('New');

    $response = $this->actingAs($user)->post(route('observing-list.store-tonight'));

    $list = ObservingList::where('owner_user_id', $user->id)->firstOrFail();
    $response->assertRedirect(route('observing-list.show', $list));
    // All objects, the planets too, in the order to observe them.
    $expected = collect(TonightsBest::for($user)['objects'])->map(fn (array $object) => $object['list_name'] ?? $object['name'])->all();
    expect($list->items()->orderBy('sort_order')->pluck('object_name')->all())->toBe($expected)
        ->and($expected)->toContain('Saturn', 'Autumn Galaxy 1')
        ->and($list->name)->toContain('Night of')
        ->and($list->description)->toContain('Made for the night of')
        ->and($list->public)->toBeFalse();
    Carbon::setTestNow();
});

it('makes an observing list for a night with the wizard', function () {
    Queue::fake();
    tbAutumnSky();
    $user = tbObserver();
    tbMetrics($user, ['Autumn Galaxy 1' => 1.4, 'Autumn Galaxy 2' => 0.8, 'Autumn Cluster 1' => 2.0, 'Autumn Cluster 2' => 2.5, 'Autumn Globular' => 1.1,
        'Autumn Nebula' => 0.6, 'Faint Galaxy' => 0.05]);
    $this->actingAs($user);

    $this->get(route('observing-list.night'))->assertOk()->assertSee('Which night do you want to observe?');

    Livewire::test(NightListWizard::class)
        ->set('night', TB_NEW_MOON)
        ->set('typeGroups', ['galaxies'])
        ->set('count', 5)
        ->call('preview')
        ->assertSet('showPreview', true)
        ->assertSee('Autumn Galaxy 1')
        ->assertDontSee('Autumn Cluster 1')
        ->set('name', 'Galaxies in November')
        ->set('public', true)
        ->call('create')
        ->assertRedirect();

    $list = ObservingList::where('owner_user_id', $user->id)->firstOrFail();
    expect($list->name)->toBe('Galaxies in November')
        ->and($list->public)->toBeTrue()
        ->and($list->items()->pluck('object_name')->sort()->values()->all())->toBe(['Autumn Galaxy 1', 'Autumn Galaxy 2']);
});

it('validates the answers of the wizard', function () {
    $this->actingAs(tbObserver());

    Livewire::test(NightListWizard::class)
        ->set('period', 'custom')
        ->set('count', 500)
        ->call('preview')
        ->assertHasErrors(['from', 'until', 'count'])
        ->assertSet('showPreview', false);
});

it('gives the contrast reserve of an object with the Moon of the night', function () {
    $user = tbObserver();
    $target = \App\Services\ContrastReserveCalculator::target(180.0, 60.0, 4.0);
    $reserve = fn (string $night) => \App\Services\MoonlitContrastReserve::for($target, 0.7 * 15.0, 41.0, $user->standardInstrument, $user->standardLocation, $user, $night, 'Europe/Brussels');
    $newMoon = $reserve(TB_NEW_MOON);
    $fullMoon = $reserve(TB_FULL_MOON);
    $dark = \App\Services\ContrastReserveCalculator::calculate($target, $user->standardInstrument, $user->standardLocation, $user, altitude: $newMoon['altitude'])['contrast_reserve'];

    // Without the Moon it is the contrast reserve of the dark sky, at the
    // altitude of the object then (the altitude is rounded).
    expect($newMoon['moon_up'])->toBeFalse()
        ->and($newMoon['contrast_reserve'])->toEqualWithDelta($dark, 0.005)
        ->and($fullMoon['moon_up'])->toBeTrue()
        ->and($fullMoon['sqm'])->toBeLessThan(20.0)
        ->and($fullMoon['contrast_reserve'])->toBeLessThan($dark - 0.3);

    // An object that never rises has none.
    expect(\App\Services\MoonlitContrastReserve::for($target, 15.0, -75.0, $user->standardInstrument, $user->standardLocation, $user, TB_NEW_MOON, 'Europe/Brussels'))->toBeNull();
});

it('offers planets, comets and double stars as kinds of objects in the wizard', function () {
    Queue::fake();
    tbAutumnSky();
    tbObject('Autumn Double', 1.0, 38.0, 'DS', 5000);
    $user = tbObserver();
    tbMetrics($user, ['Autumn Galaxy 1' => 1.4, 'Autumn Galaxy 2' => 0.8, 'Autumn Cluster 1' => 2.0, 'Autumn Cluster 2' => 2.5, 'Autumn Globular' => 1.1,
        'Autumn Nebula' => 0.6, 'Faint Galaxy' => 0.05, 'Autumn Double' => 6.0]);
    $this->actingAs($user);

    // Double stars only when they are chosen, despite their contrast reserve.
    $all = TonightsBest::for($user, ['night' => TB_NEW_MOON]);
    expect(collect($all['objects'])->pluck('name'))->not->toContain('Autumn Double');

    // The date picker sends a moment; only planets were chosen.
    Livewire::test(NightListWizard::class)
        ->set('night', TB_NEW_MOON.'T00:00:00+01:00')
        ->assertSet('night', TB_NEW_MOON)
        ->set('typeGroups', ['planets', 'double_stars'])
        ->call('preview')
        ->assertSee('Saturn')
        ->assertSee('Autumn Double')
        ->assertDontSee('Autumn Galaxy 1')
        ->call('create')
        ->assertRedirect();

    $names = ObservingList::where('owner_user_id', $user->id)->firstOrFail()->items()->pluck('object_name');
    expect($names)->toContain('Saturn', 'Autumn Double')
        ->and($names)->not->toContain('Autumn Galaxy 1');
});

it('takes the times of the time pickers, also on the 12-hour clock', function () {
    Queue::fake();
    tbAutumnSky();
    $this->actingAs(tbObserver());

    Livewire::test(NightListWizard::class)
        ->set('night', TB_NEW_MOON)
        ->set('period', 'custom')
        ->set('from', '10:30 PM')
        ->assertSet('from', '22:30')
        ->set('until', '12:15 AM')
        ->assertSet('until', '00:15')
        ->set('until', '01:45')
        ->assertSet('until', '01:45')
        ->call('preview')
        ->assertHasNoErrors()
        ->assertSee('Well placed between 22:30 and 01:45.');
});

it('shows the location and telescope the wizard chooses for', function () {
    $user = tbObserver();
    $this->actingAs($user);

    Livewire::test(NightListWizard::class)
        ->assertSee($user->standardLocation->name)
        ->assertSee($user->standardInstrument->fullName())
        ->assertDontSee('No standard telescope at this location');

    // Without standard equipment, another observer's location is not named.
    $other = User::factory()->create();
    $this->actingAs($other);
    Livewire::test(NightListWizard::class)
        ->assertSee('No standard location: a latitude of 51° is used.')
        ->assertDontSee($user->standardLocation->name)
        ->assertSee('No standard telescope at this location');
});
