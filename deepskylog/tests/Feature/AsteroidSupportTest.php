<?php

use App\Http\Livewire\ObjectEphemerides;
use App\Models\Asteroid;
use App\Models\Observation;
use App\Models\User;
use App\Services\AsteroidCoordinates;
use Carbon\Carbon;
use Database\Factories\InstrumentFactory;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use Illuminate\Support\Facades\DB;

// Asteroids: added from their orbital elements, observed, and calculated
// with the astronomy library (App\Services\AsteroidCoordinates).

function asteroidElements(int $number, string $name, array $elements = []): void
{
    DB::table('asteroids_orbital_elements')->insert($elements + [
        'number' => $number, 'name' => $name, 'epoch' => 2461200.5,
        'a' => 2.7, 'e' => 0.1, 'i' => 10.0, 'w' => 70.0, 'node' => 80.0, 'M' => 270.0,
        'H' => 10.0, 'G' => 0.15, 'ref' => 'JPL 1',
    ]);
}

beforeEach(function () {
    // The elements of JPL for Vesta, epoch 2026-06-19
    asteroidElements(4, 'Vesta', [
        'a' => 2.361366, 'e' => 0.09020374, 'i' => 7.14393, 'w' => 151.46865,
        'node' => 103.70129, 'M' => 81.1901561, 'H' => 3.25, 'G' => 0.32,
    ]);
    asteroidElements(1, 'Ceres');
    asteroidElements(1982, 'Vestfold');
    asteroidElements(100001, '1982 UC3');
});

it('agrees with JPL Horizons on the position and magnitude of Vesta', function () {
    $target = AsteroidCoordinates::target(AsteroidCoordinates::orbitalElements(4));
    $date = Carbon::create(2026, 10, 23, 0, 0, 0, 'UTC');
    $coords = AsteroidCoordinates::calculate($target, $date, new GeographicalCoordinates(0.0, 0.0));

    // Horizons, geocentric astrometric: RA 20.21631°, Dec -4.07299°, APmag 6.507
    expect($coords->getRA()->getCoordinate() * 15)->toEqualWithDelta(20.21631, 0.01)
        ->and($coords->getDeclination()->getCoordinate())->toEqualWithDelta(-4.07299, 0.01)
        ->and(AsteroidCoordinates::magnitude($target, $date))->toEqualWithDelta(6.507, 0.02);
});

it('gives Pluto the magnitude of Mallama, as JPL Horizons does', function () {
    asteroidElements(134340, 'Pluto', ['epoch' => 2457588.5, 'a' => 39.5886294, 'e' => 0.25183788, 'i' => 17.14771, 'w' => 113.709, 'node' => 110.29238, 'M' => 38.6836635, 'H' => -0.55]);
    $target = AsteroidCoordinates::target(AsteroidCoordinates::orbitalElements(134340));

    // Horizons: 14.580 on 2026-10-24, 14.491 on 2027-07-20 (H-G would give 15.17)
    expect($target)->toBeInstanceOf(\App\Services\Targets\Pluto::class)
        ->and(AsteroidCoordinates::magnitude($target, Carbon::create(2026, 10, 24, 0, 0, 0, 'UTC')))->toEqualWithDelta(14.580, 0.02)
        ->and(AsteroidCoordinates::magnitude($target, Carbon::create(2027, 7, 20, 0, 0, 0, 'UTC')))->toEqualWithDelta(14.491, 0.02);
});

it('searches the orbital elements by number, name or designation', function () {
    $numbers = fn (string $q) => AsteroidCoordinates::search($q)->pluck('number')->all();

    expect($numbers('4'))->toBe([4])
        ->and($numbers('(4)'))->toBe([4])
        ->and($numbers('Vesta'))->toBe([4])
        ->and($numbers('Vest'))->toBe([4, 1982])
        ->and($numbers('(4) Vesta'))->toBe([4])
        ->and($numbers('4 Vesta'))->toBe([4])
        // A designation that starts with digits is not a number
        ->and($numbers('1982 UC3'))->toBe([100001])
        ->and($numbers('Pallas'))->toBe([])
        ->and($numbers(''))->toBe([]);
});

it('adds an asteroid from its orbital elements once, with its designation in the search index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('object.store.asteroid'), ['number' => 4])
        ->assertRedirect(route('object.show', ['slug' => '4-vesta']));

    $asteroid = Asteroid::firstWhere('number', 4);
    expect($asteroid->name)->toBe('(4) Vesta')
        ->and($asteroid->designation)->toBe('Vesta')
        ->and(DB::table('search_index')->where('source_table', 'asteroids')->orderBy('name')->pluck('source_type', 'name')->all())
        ->toBe(['(4) Vesta' => 'asteroid', 'Vesta' => 'alias']);

    // A second time it is not added again
    $this->actingAs($user)->post(route('object.store.asteroid'), ['number' => 4])
        ->assertRedirect(route('object.show', ['slug' => '4-vesta']));
    expect(Asteroid::count())->toBe(1);

    $this->actingAs($user)->post(route('object.store.asteroid'), ['number' => 2])
        ->assertSessionHas('error');
});

it('lists the asteroids found and links the ones already added', function () {
    Asteroid::addFromElements(AsteroidCoordinates::orbitalElements(4));

    $this->actingAs(User::factory()->create())
        ->get(route('object.create.asteroid', ['q' => 'Vest']))
        ->assertOk()
        ->assertSeeInOrder(['(4) Vesta', 'Already in DeepskyLog', '(1982) Vestfold'])
        ->assertSee('name="number" value="1982"', false)
        ->assertDontSee('name="number" value="4"', false);
});

it('records an observation of an asteroid', function () {
    $user = User::factory()->create();
    $asteroid = Asteroid::addFromElements(AsteroidCoordinates::orbitalElements(4));

    $this->actingAs($user)->get(route('observation.create', ['slug' => $asteroid->slug]))
        ->assertOk()
        ->assertSee('(4) Vesta')
        ->assertSee('name="target_table" value="asteroids"', false);

    $this->actingAs($user)->post(route('observation.store'), [
        'target_table' => 'asteroids',
        'target_slug' => $asteroid->slug,
        'observed_on' => '2026-10-23',
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => 'A star that moved.',
    ])->assertSessionHasNoErrors();

    $observation = Observation::latest('id')->first();
    expect($observation->target_type)->toBe('solarsystem')
        ->and($observation->target_table)->toBe('asteroids')
        ->and($observation->target_key)->toBe('4-vesta')
        ->and($observation->target_name)->toBe('(4) Vesta');
});

it('calculates the ephemerides of the asteroid, not of an object with the same id', function () {
    $user = User::factory()->create();
    DB::table('cometobjects')->insert(['id' => 1, 'name' => '1P/Halley', 'slug' => '1p-halley', 'icqname' => '', 'timestamp' => now()]);
    $asteroid = Asteroid::addFromElements(AsteroidCoordinates::orbitalElements(4));
    $location = (int) DB::table('locations')->insertGetId([
        'name' => 'Home', 'longitude' => 4.35, 'latitude' => 50.85, 'timezone' => 'Europe/Brussels',
        'limitingMagnitude' => -999, 'skyBackground' => -999, 'elevation' => 0, 'country' => 'Belgium',
        'active' => true, 'user_id' => $user->id, 'observer' => $user->username, 'observations' => 0,
        'slug' => 'home', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $user->forceFill(['stdlocation' => $location])->save();
    $this->actingAs($user);

    expect($asteroid->id)->toBe(1);

    $component = new ObjectEphemerides();
    $component->mount((string) $asteroid->id, null, $asteroid->name, 'asteroid');
    $component->recalculate(['objectId' => (string) $asteroid->id, 'sourceTypeRaw' => 'asteroid', 'date' => '2026-10-23']);

    expect($component->ephemerides['raDeg'])->toEqualWithDelta(20.1, 0.3)
        ->and($component->ephemerides['mag'])->toEqualWithDelta(6.5, 0.1)
        ->and($component->ephemerides['predicted_magnitudes'])->not->toBeEmpty()
        // The magnitude during the year, but no diameter graph without a diameter
        ->and($component->ephemerides['year_magnitude_graph'])->toContain('<img')
        ->and($component->ephemerides['year_diameter_graph'] ?? null)->toBeEmpty();
});

it('lists the observations of an asteroid on its page and in the observation lists', function () {
    $user = User::factory()->create();
    $asteroid = Asteroid::addFromElements(AsteroidCoordinates::orbitalElements(4));
    Observation::create([
        'observer_username' => $user->username, 'observer_user_id' => $user->id, 'observed_on' => '2026-10-23',
        'target_type' => 'solarsystem', 'target_table' => 'asteroids', 'target_key' => $asteroid->slug, 'target_name' => $asteroid->name,
        'description' => 'A star that moved.',
    ]);

    $list = $this->get(route('observations.object', ['slug' => $asteroid->slug]))->assertOk();
    $list->assertSee('A star that moved.')
        ->assertSee('asteroid')
        ->assertDontSee(' in  on ')
        ->assertSee('href="'.route('object.show', ['slug' => $asteroid->slug]).'"', false);
});

it('shows an observation of an asteroid and counts it on the asteroid page', function () {
    $user = User::factory()->create();
    $asteroid = Asteroid::addFromElements(AsteroidCoordinates::orbitalElements(4));
    $observation = Observation::create([
        'observer_username' => $user->username, 'observer_user_id' => $user->id, 'observed_on' => '2026-10-23',
        'target_type' => 'solarsystem', 'target_table' => 'asteroids', 'target_key' => $asteroid->slug, 'target_name' => $asteroid->name,
        'description' => 'A star that moved.',
    ]);

    $this->get($observation->detailUrl())
        ->assertOk()
        ->assertSee('(4) Vesta')
        ->assertSee('A star that moved.');

    $this->actingAs($user)->get(route('object.show', ['slug' => $asteroid->slug]))
        ->assertOk()
        ->assertSee('(4) Vesta')
        // The number of observations links to their list
        ->assertSeeInOrder(['href="'.route('observations.object', ['slug' => $asteroid->slug]).'"', 'hover:underline">1</a>'], false);
});

it('counts asteroids in the statistics and the popular objects', function () {
    $user = User::factory()->create();
    $asteroid = Asteroid::addFromElements(AsteroidCoordinates::orbitalElements(4));
    foreach (['2026-10-22', '2026-10-23'] as $date) {
        Observation::create([
            'observer_username' => $user->username, 'observer_user_id' => $user->id, 'observed_on' => $date,
            'target_type' => 'solarsystem', 'target_table' => 'asteroids', 'target_key' => $asteroid->slug, 'target_name' => $asteroid->name,
            'description' => 'A star that moved.',
        ]);
    }

    $this->get(route('statistics.index'))
        ->assertOk()
        ->assertViewHas('summaryRows', function (array $rows) {
            $observations = collect($rows)->firstWhere('key', 'observations')['values'];

            return $observations['asteroids'] === 2 && $observations['total'] === 2;
        });

    expect(app(\App\Services\PopularObjectsRanking::class)->rows('observations'))->toMatchArray([[
        'id' => 1, 'rank' => 1, 'name' => '(4) Vesta', 'kind' => 'asteroid', 'count' => 2, 'slug' => '4-vesta',
    ]]);
});

it('calculates the contrast reserve of a point source against the limiting magnitude of the instrument', function () {
    $user = User::factory()->create();
    $instrument = InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id, 'aperture_mm' => 200, 'focal_length_mm' => 1200, 'obstruction_perc' => 0]);
    $location = new \App\Models\Location(['limitingMagnitude' => 6.5, 'skyBackground' => -999]);
    $magnifications = [48 => [], 120 => [], 240 => []];
    $calculator = \App\Services\ContrastReserveCalculator::class;

    $bright = $calculator::pointSource(8.0, $instrument, $location, $user, 1.0, $magnifications);
    $limit = \App\Services\TelescopicLimitingMagnitude::calculate(6.5, 200, 240);
    // The highest magnification darkens the sky most
    expect($bright['magnification'])->toBe(240)
        ->and($bright['contrast_reserve'])->toEqualWithDelta(0.4 * ($limit - 8.0), 1e-9)
        ->and($bright['category'])->toBe('very_easy')
        ->and($calculator::pointSource($limit + 1.0, $instrument, $location, $user, 1.0, $magnifications)['category'])->toBe('not_visible')
        // Without a sky quality nothing is known
        ->and($calculator::pointSource(8.0, $instrument, new \App\Models\Location(['limitingMagnitude' => -999, 'skyBackground' => -999]), $user, 1.0, $magnifications)['contrast_reserve'])->toBeNull();
});

it('recalculates the magnitude and contrast reserve of an asteroid for the date', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    $asteroid = Asteroid::addFromElements(AsteroidCoordinates::orbitalElements(4));
    $instrument = InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id, 'aperture_mm' => 200, 'focal_length_mm' => 1200, 'fixedMagnification' => null]);
    $location = (int) DB::table('locations')->insertGetId([
        'name' => 'Dark site', 'longitude' => 4.35, 'latitude' => 50.85, 'timezone' => 'Europe/Brussels',
        'limitingMagnitude' => 6.5, 'skyBackground' => -999, 'elevation' => 0, 'country' => 'Belgium',
        'active' => true, 'user_id' => $user->id, 'observer' => $user->username, 'observations' => 0,
        'slug' => 'dark-site', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $user->forceFill(['stdlocation' => $location, 'stdtelescope' => $instrument->id])->save();

    $component = Livewire\Livewire::actingAs($user)->test(\App\Http\Livewire\AladinPreviewInfo::class, [
        'objectId' => (string) $asteroid->id, 'initial' => ['object_type' => 'asteroid'],
    ])->call('recalculate', ['objectId' => (string) $asteroid->id, 'instrument' => $instrument->id, 'date' => '2026-10-23']);

    expect($component->get('object_mag'))->toEqualWithDelta(6.5, 0.1)
        ->and($component->get('contrast_reserve'))->toBeGreaterThan(1.0)
        ->and($component->get('contrast_reserve_category'))->toBe('very_easy')
        ->and($component->get('optimum_detection_magnification'))->toBe(200);
});

it('shows the asteroids on the observer page', function () {
    $user = User::factory()->create();
    $asteroid = Asteroid::addFromElements(AsteroidCoordinates::orbitalElements(4));
    foreach (['2026-10-22', '2026-10-23'] as $date) {
        Observation::create([
            'observer_username' => $user->username, 'observer_user_id' => $user->id, 'observed_on' => $date,
            'target_type' => 'solarsystem', 'target_table' => 'asteroids', 'target_key' => $asteroid->slug, 'target_name' => $asteroid->name,
            'description' => 'A star that moved.',
        ]);
    }

    $this->actingAs($user)->get(route('observer.show', $user->slug))
        ->assertOk()
        ->assertViewHas('asteroidCounts', fn (array $counts) => $counts['Observations'] === [2, 2] && $counts['Unique'] === [1, 1])
        ->assertSeeInOrder([__('Planets'), __('Asteroids'), __('Number of observations')]);

    expect(Illuminate\Support\Facades\Cache::get('observer_stats:'.$user->id)['perYear']['asteroids'])->toContain(2);
});

it('moves Pluto from the planets to the asteroids', function () {
    asteroidElements(134340, 'Pluto', ['a' => 39.5886294, 'e' => 0.25183788, 'i' => 17.14771, 'w' => 113.709, 'node' => 110.29238, 'M' => 38.6836635, 'H' => -0.55]);
    $plutoId = DB::table('planets')->insertGetId(['name' => 'Pluto', 'slug' => 'pluto', 'body_type' => 'dwarf planet', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('search_index')->insert(['name' => 'Pluto', 'name_normalized' => 'pluto', 'source_table' => 'planets', 'source_pk' => (string) $plutoId, 'display_name' => 'Pluto', 'source_type' => 'dwarf planet']);
    DB::table('object_name_translations')->insert(['objectname' => 'Pluto', 'locale' => 'fr', 'name' => 'Pluton']);
    $user = User::factory()->create();
    $observation = Observation::create([
        'observer_username' => $user->username, 'observer_user_id' => $user->id, 'observed_on' => '2006-08-24',
        'target_type' => 'planet', 'target_table' => 'planets', 'target_key' => 'pluto', 'target_name' => 'Pluto',
        'description' => 'Still a planet then.',
    ]);

    (require database_path('migrations/2026_10_24_000001_move_pluto_to_the_asteroids.php'))->up();

    expect(DB::table('planets')->where('name', 'Pluto')->exists())->toBeFalse()
        ->and(DB::table('search_index')->where('source_table', 'planets')->exists())->toBeFalse()
        ->and(DB::table('object_name_translations')->exists())->toBeFalse()
        ->and($observation->fresh()->only(['target_type', 'target_table', 'target_key', 'target_name']))->toBe([
            'target_type' => 'solarsystem', 'target_table' => 'asteroids', 'target_key' => '134340-pluto', 'target_name' => '(134340) Pluto',
        ]);

    // The old address shows the asteroid
    $this->get('/object/pluto')->assertOk()->assertSee('(134340) Pluto');
});
