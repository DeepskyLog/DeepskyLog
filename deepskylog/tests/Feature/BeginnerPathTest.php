<?php

use App\Http\Controllers\ObservationsController;
use App\Livewire\BeginnerPath as BeginnerPathPage;
use App\Models\Location;
use App\Models\ObjectFindingNote;
use App\Models\Observation;
use App\Models\ObservingGoal;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\BeginnerPath;
use Carbon\Carbon;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

/** Pool objects with their real position (RA in hours), magnitude and size (arcseconds). */
const BEGINNER_OBJECTS = [
    'M 31'     => ['GALXY', 0.712, 41.27, 3.5, 11346, 4000],
    'M 45'     => ['OPNCL', 3.79, 24.12, 1.5, 6540, 6540],
    'M 42'     => ['HII', 5.59, -5.39, 4.0, 5400, 3600],
    'M 13'     => ['GLOCL', 16.69, 36.46, 5.8, 1392, 1392],
    'M 57'     => ['PLNNB', 18.89, 33.03, 9.0, 76, 60],
    'NGC 6543' => ['PLNNB', 17.98, 66.63, 8.1, 19.8, 19.8],
    'Beta Cyg' => ['DS', 19.51, 27.96, 3.1, 35, 0],
    'NGC 5139' => ['GLOCL', 13.45, -47.48, 5.3, 3300, 3300],
];

function beginnerObjects(): void
{
    foreach (BEGINNER_OBJECTS as $name => [$type, $ra, $decl, $mag, $diam1, $diam2]) {
        DB::table('objects')->insert([
            'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'AND',
            'ra'            => $ra, 'decl' => $decl, 'mag' => $mag, 'subr' => $type === 'DS' ? 4.7 : 0.0, 'pa' => $type === 'DS' ? 54 : 999,
            'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $diam1, 'diam2' => $diam2,
            'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
            'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
            'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
            'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
        ]);
    }
}

function beginnerObserver(bool $withLocation = true): User
{
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    if ($withLocation) {
        $location = LocationFactory::new()->create([
            'user_id'  => $user->id, 'observer' => $user->username, 'name' => 'Backyard', 'latitude' => 50.8, 'longitude' => 4.7,
            'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => 20.5, 'active' => true,
        ]);
        $user->forceFill(['stdlocation' => $location->id])->save();
    }

    return $user->fresh();
}

function beginnerObservation(User $user, string $object, string $date, ?int $visibility = null): void
{
    $id = DB::table('observations')->insertGetId(['observer_username' => $user->username, 'observed_on' => $date,
        'target_type'                                                 => 'deepsky', 'target_table' => 'objects', 'target_key' => $object, 'target_name' => $object,
        'has_drawing'                                                 => false, 'language' => 'en', 'legacy_source' => 'deepsky', 'created_at' => now(), 'updated_at' => now()]);
    if ($visibility !== null) {
        DB::table('deepsky_observation_details')->insert(['observation_id' => $id, 'visibility' => $visibility]);
    }
}

function beginnerLocation(User $user): Location
{
    return Location::find($user->stdlocation);
}

beforeEach(function () {
    Cache::flush();
    beginnerObjects();
});

it('picks the objects that rise high enough and the instrument shows, from the current month on', function () {
    $user = beginnerObserver();
    beginnerObservation($user, 'M 57', '2025-08-01');

    $names = BeginnerPath::pick($user, BeginnerPath::instrument($user, 'starter:reflector'), beginnerLocation($user), Carbon::parse('2026-10-15'));

    // Omega Centauri never rises high enough in Belgium, M 57 was seen already
    expect($names)->not->toContain('NGC 5139')
        ->not->toContain('M 57')
        ->toContain('M 31', 'M 45', 'M 42', 'M 13', 'NGC 6543', 'Beta Cyg')
        // the evening sky of autumn first, the summer objects last
        ->and($names[0])->toBe('M 31')
        ->and(array_search('M 45', $names))->toBeLessThan(array_search('M 42', $names))
        ->and(array_search('M 42', $names))->toBeLessThan(array_search('M 13', $names));
});

it('leaves out small objects and close pairs that binoculars do not show', function () {
    $user = beginnerObserver();

    $names = BeginnerPath::pick($user, BeginnerPath::instrument($user, 'starter:binoculars'), beginnerLocation($user), Carbon::parse('2026-10-15'));

    expect($names)->toContain('M 31', 'M 45', 'M 13', 'Beta Cyg')
        ->not->toContain('NGC 6543')
        ->not->toContain('M 57');
});

it('picks for the standard telescope by default', function () {
    $user = beginnerObserver();

    expect(BeginnerPath::defaultChoice($user))->toBe('starter:' . BeginnerPath::DEFAULT_STARTER)
        ->and(BeginnerPath::instrumentName(BeginnerPath::instrument($user)))->toBe('130 mm telescope')
        ->and(BeginnerPath::instrumentName(BeginnerPath::instrument($user, 'starter:binoculars')))->toBe('10x50 binoculars');
});

it('starts a path as an observing list with a goal, and counts the objects seen since', function () {
    $user  = beginnerObserver();
    $today = Carbon::parse('2026-10-15');
    beginnerObservation($user, 'M 57', '2025-08-01');

    $goal = BeginnerPath::start($user, BeginnerPath::instrument($user), beginnerLocation($user), $today);

    expect($goal->beginner_path)->toBeTrue()
        ->and($goal->observingList->owner_user_id)->toBe($user->id)
        ->and($goal->observingList->public)->toBeFalse()
        ->and($goal->starts_on->toDateString())->toBe('2026-10-15')
        ->and($goal->ends_on->toDateString())->toBe('2027-10-15')
        ->and($goal->progress()['total'])->toBe(6);

    beginnerObservation($user, 'M 31', '2026-10-20');
    beginnerObservation($user, 'M 45', '2026-10-21', Observation::VISIBILITY_NOT_SEEN);

    $goal  = BeginnerPath::current($user);
    $steps = BeginnerPath::steps($goal, beginnerLocation($user), Carbon::parse('2026-10-22'));

    expect($goal->progress()['count'])->toBe(1)
        ->and($steps[0])->toMatchArray(['number' => 1, 'name' => 'M 31', 'slug' => 'm-31', 'done_on' => '2026-10-20', 'now' => true])
        ->and($steps[0]['months'])->toContain(10)
        // M 45 was not seen: it is next
        ->and(BeginnerPath::next($steps)['name'])->toBe('M 45');
});

it('shows the finding notes of the objects', function () {
    $user  = beginnerObserver();
    $other = beginnerObserver(false);
    $goal  = BeginnerPath::start($user, BeginnerPath::instrument($user), beginnerLocation($user), Carbon::parse('2026-10-15'));
    ObjectFindingNote::add($other, 'M 31', 'Visible to the naked eye from a dark site.');

    $steps = collect(BeginnerPath::steps($goal, beginnerLocation($user)))->keyBy('name');

    expect($steps['M 31']['notes']->pluck('text')->all())->toBe(['Visible to the naked eye from a dark site.'])
        ->and($steps['M 45']['notes'])->toBeEmpty();
});

it('replaces the path with a new one', function () {
    $user  = beginnerObserver();
    $first = BeginnerPath::start($user, BeginnerPath::instrument($user), beginnerLocation($user));

    $second = BeginnerPath::start($user, BeginnerPath::instrument($user, 'starter:binoculars'), beginnerLocation($user));

    expect(ObservingGoal::where('user_id', $user->id)->pluck('id')->all())->toBe([$second->id])
        ->and(ObservingList::find($first->observing_list_id))->toBeNull()
        ->and(BeginnerPath::current($user)->id)->toBe($second->id);
});

it('gives no path when no object suits the location', function () {
    $user     = beginnerObserver();
    $location = beginnerLocation($user);
    $location->forceFill(['latitude' => -89.0])->save();
    DB::table('objects')->where('decl', '<', -40)->delete();

    expect(BeginnerPath::start($user, BeginnerPath::instrument($user), $location))->toBeNull()
        ->and(ObservingGoal::count())->toBe(0);
});

it('starts the hop at the nearest bright star', function () {
    DB::table('stars')->insert([
        ['source_key' => sha1('eta her'), 'source_table' => 'stars8', 'constellation_code' => 'Her', 'bayer' => 'Eta', 'name' => 'eta Her', 'ra_degrees' => 250.3225, 'decl_degrees' => 38.9223, 'vmag' => 3.5],
        ['source_key' => sha1('zet her'), 'source_table' => 'stars8', 'constellation_code' => 'Her', 'bayer' => 'Zet', 'name' => 'zet Her', 'ra_degrees' => 250.3217, 'decl_degrees' => 31.6027, 'vmag' => 2.8],
        ['source_key' => sha1('faint'), 'source_table' => 'stars8', 'constellation_code' => 'Her', 'bayer' => 'Sig', 'name' => 'faint', 'ra_degrees' => 250.5, 'decl_degrees' => 36.5, 'vmag' => 4.2],
    ]);

    // M 13 lies a third of the way from eta to zeta Herculis
    expect(BeginnerPath::hop(16.6949 * 15, 36.4603))->toBe(['star' => 'η Her', 'distance' => 2.5, 'direction' => 'south of'])
        ->and(BeginnerPath::hop(5.59 * 15, -5.39))->toBeNull()
        // a cluster around the star: no direction
        ->and(BeginnerPath::hop(16.6949 * 15, 36.4603, 3.0)['direction'])->toBeNull();
});

it('names the months as ranges', function () {
    expect(BeginnerPath::monthRanges([11, 12, 1, 2, 5]))->toBe('May, Nov – Feb')
        ->and(BeginnerPath::monthRanges([3]))->toBe('Mar')
        ->and(BeginnerPath::monthRanges(range(1, 12)))->toBe('all year')
        ->and(BeginnerPath::monthRanges([]))->toBe('');
});

it('starts a path on the page and shows its objects', function () {
    $user = beginnerObserver();

    Livewire::actingAs($user)->test(BeginnerPathPage::class)
        ->assertSee('Start my beginner path')
        ->set('instrument', 'starter:binoculars')
        ->call('start')
        ->assertRedirect(route('beginner-path'));

    expect(BeginnerPath::current($user)->observingList->description)->toContain('10x50 binoculars');

    $this->actingAs($user)->get(route('beginner-path'))
        ->assertOk()
        ->assertSee('M 31')
        ->assertSee('Finder and eyepiece views')
        ->assertSee('return_to=%2Fbeginner-path', false);
});

it('asks for a location first', function () {
    $user = beginnerObserver(false);

    Livewire::actingAs($user)->test(BeginnerPathPage::class)
        ->assertSee('Add the place where you observe first')
        ->call('start')
        ->assertHasErrors('location');
});

it('invites new observers on the home page and shows the next object of their path', function () {
    $newcomer = beginnerObserver();
    $veteran  = beginnerObserver();
    foreach (range(1, BeginnerPath::INVITE_BELOW_OBSERVATIONS) as $day) {
        beginnerObservation($veteran, 'M 13', '2025-07-' . str_pad((string) min($day, 28), 2, '0', STR_PAD_LEFT));
    }

    expect(BeginnerPath::forHomePage($newcomer))->toBe(['goal' => null, 'next' => null])
        ->and(BeginnerPath::forHomePage($veteran))->toBeNull();

    $this->actingAs($newcomer)->get('/')->assertOk()->assertSee('Start my beginner path');

    BeginnerPath::start($newcomer, BeginnerPath::instrument($newcomer), beginnerLocation($newcomer));
    $home = BeginnerPath::forHomePage($newcomer);

    expect($home['goal'])->not->toBeNull()
        ->and($home['next'])->not->toBeNull();
    $this->actingAs($newcomer)->get('/')->assertOk()->assertSee('My beginner path');
});

it('returns to the beginner path after logging an observation', function () {
    expect(ObservationsController::returnUrl('/beginner-path'))->toBe('/beginner-path')
        ->and(ObservationsController::returnUrl('/beginner-path/../admin'))->toBeNull()
        ->and(ObservationsController::returnUrl('https://example.com/beginner-path'))->toBeNull();
});
