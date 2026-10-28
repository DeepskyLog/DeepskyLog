<?php

use App\Livewire\ObservationSearchTable;
use App\Models\Eyepiece;
use App\Models\Observation;
use App\Models\User;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Spatie\ResponseCache\Events\ClearingResponseCache;

function listingFixesM65(): void
{
    DB::table('objects')->insert([
        'name' => 'M 65', 'slug' => 'm-65', 'type' => 'GALXY', 'con' => 'LEO', 'ra' => 11.3155, 'decl' => 13.0922,
        'mag' => 9.3, 'subr' => 12.4, 'pa' => 174, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
        'diam1' => 600.0, 'diam2' => 180.0, 'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '',
        'psa' => '', 'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0',
        'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function listingFixesObservation(User $observer, array $extra = []): Observation
{
    return Observation::query()->create(array_merge([
        'observer_username' => $observer->username, 'observer_user_id' => $observer->id, 'observed_on' => '2026-03-01',
        'language' => 'en', 'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 65',
        'target_name' => 'M 65', 'has_drawing' => false,
    ], $extra));
}

it('labels the observations of an object as observations, not drawings', function () {
    $user = User::factory()->create(['username' => 'listingobs', 'slug' => 'listing-obs']);
    listingFixesM65();
    listingFixesObservation($user);

    $this->get('/observations/m-65')
        ->assertOk()
        ->assertSee('All observations')
        ->assertDontSee('<h3 class="mb-3 text-lg font-semibold text-gray-200">All drawings</h3>', false);
});

it('returns 404 for an unknown object instead of all observations', function () {
    $user = User::factory()->create(['username' => 'listing404', 'slug' => 'listing-404']);
    listingFixesObservation($user);

    $this->get('/observations/listing-404/no-such-object')->assertNotFound();
    $this->get('/observations/drawings/listing-404/no-such-object')->assertNotFound();
    $this->get('/cometdrawings/listing-404/no-such-comet')->assertNotFound();
    $this->get('/cometdrawings/no-such-comet-or-user')->assertNotFound();
});

it('adds the index to the detail url of a second observation on one day', function () {
    $user = User::factory()->create(['username' => 'listingidx', 'slug' => 'listing-idx']);
    listingFixesM65();
    $first = listingFixesObservation($user, ['observed_time' => '20:00:00']);
    $second = listingFixesObservation($user, ['observed_time' => '22:00:00']);
    listingFixesObservation($user, ['observed_time' => '21:00:00', 'target_key' => 'M 66', 'target_name' => 'M 66']);

    expect($first->detailIndex())->toBe(1)
        ->and($second->detailIndex())->toBe(2)
        ->and($first->detailUrl())->toBe(route('observations.detail', ['listing-idx', '2026-03-01', 'm-65']))
        ->and($second->detailUrl())->toBe(route('observations.detail', ['listing-idx', '2026-03-01', 'm-65', 2]));

    $this->get($second->detailUrl())->assertOk();
});

it('keeps integer values when formatting decimals for export', function () {
    $method = new ReflectionMethod(ObservationSearchTable::class, 'decimalOrZero');
    $table = new ObservationSearchTable();

    expect($method->invoke($table, '20'))->toBe('20')
        ->and($method->invoke($table, 20))->toBe('20')
        ->and($method->invoke($table, '20.50'))->toBe('20.5')
        ->and($method->invoke($table, '20.0'))->toBe('20')
        ->and($method->invoke($table, null))->toBe('0');
});

it('gives the observation itself for the first and last use of an eyepiece', function () {
    $user = User::factory()->create(['username' => 'listingep', 'slug' => 'listing-ep']);
    $eyepiece = Eyepiece::query()->create([
        'user_id' => $user->id, 'make_id' => DB::table('eyepiece_makes')->value('id'),
        'type_id' => DB::table('eyepiece_types')->value('id'), 'name' => 'Listing Eyepiece', 'focal_length_mm' => 10,
        'apparentFOV' => 82, 'active' => true, 'observer' => '', 'observations' => 0, 'slug' => 'listing-eyepiece',
    ]);
    $early = listingFixesObservation($user, ['eyepiece_id' => $eyepiece->id, 'observed_on' => '2026-01-01']);
    $late = listingFixesObservation($user, ['eyepiece_id' => $eyepiece->id, 'observed_on' => '2026-02-01']);

    $first = Observation::getFirstObservationDateAndIdForEyepieceIds([$eyepiece->id])[$eyepiece->id];
    $last = Observation::getLastObservationDateAndIdForEyepieceIds([$eyepiece->id])[$eyepiece->id];

    expect($first['id']->id)->toBe($early->id)
        ->and($last['id']->id)->toBe($late->id);

    Eyepiece::setBulkFirstObservationMap([$eyepiece->id => $first]);
    expect($eyepiece->first_observation_date()[1]->id)->toBe($eyepiece->first_observation_date_generic('eyepieceid')[1]->id);
    Eyepiece::setBulkFirstObservationMap([]);
});

it('clears the response cache when only the observation details change', function () {
    $user = User::factory()->create(['username' => 'listingcache', 'slug' => 'listing-cache']);
    listingFixesM65();
    $observation = listingFixesObservation($user);
    $payload = [
        'edit_unified_id' => $observation->id, 'target_table' => 'objects', 'target_slug' => 'm-65',
        'observed_on' => '2026-03-01', 'visibility' => 2,
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id, 'description' => 'Seen.',
    ];
    $this->actingAs($user)->post(route('observation.store'), $payload)->assertSessionHasNoErrors();

    Event::fake([ClearingResponseCache::class]);
    $this->actingAs($user)->post(route('observation.store'), array_merge($payload, ['visibility' => 4]))
        ->assertSessionHasNoErrors();

    Event::assertDispatched(ClearingResponseCache::class);
    expect(DB::table('deepsky_observation_details')->where('observation_id', $observation->id)->value('visibility'))->toBe(4);
});

it('links the observation cards and the rss feed to the right observation of a day', function () {
    $user = User::factory()->create(['username' => 'listinglinks', 'slug' => 'listing-links']);
    listingFixesM65();
    listingFixesObservation($user, ['observed_time' => '20:00:00']);
    listingFixesObservation($user, ['observed_time' => '22:00:00']);
    $second = route('observations.detail', ['listing-links', '2026-03-01', 'm-65', 2]);

    $this->get('/observations/listing-links')->assertOk()->assertSee($second, false);
    $this->get(route('feed.observations'))->assertOk()->assertSee($second, false);
});
