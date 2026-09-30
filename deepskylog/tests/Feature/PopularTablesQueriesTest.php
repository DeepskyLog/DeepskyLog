<?php

use App\Livewire\PopularObservationsTable;
use App\Livewire\PopularSessionsTable;
use App\Models\Location;
use App\Models\ObservationLike;
use App\Models\ObservationSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * The popular tables load the liked sessions/observations and their observers
 * per page, so the number of queries does not grow with the rows shown.
 */
function popularQueryCount(string $component): int
{
    app()->forgetScopedInstances();
    $count = 0;
    DB::listen(function () use (&$count) {
        $count++;
    });
    Livewire::test($component)->assertOk();
    DB::getEventDispatcher()->forget(Illuminate\Database\Events\QueryExecuted::class);

    return $count;
}

function popularSessions(int $count): void
{
    $liker = User::factory()->create();
    for ($i = 0; $i < $count; $i++) {
        $owner = User::factory()->create();
        $location = Location::query()->create([
            'user_id' => $owner->id, 'name' => 'Site '.$i, 'longitude' => 4.5, 'latitude' => 50.5,
            'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => -999, 'elevation' => 120,
            'country' => 'Belgium', 'active' => true, 'observer' => '', 'observations' => 0, 'slug' => 'site-'.$i,
        ]);
        $session = ObservationSession::query()->create([
            'name' => 'Night '.$i, 'observerid' => $owner->username, 'begindate' => '2026-03-01 00:00:00',
            'enddate' => '2026-03-01 23:59:59', 'locationid' => $location->id, 'weather' => '', 'equipment' => '',
            'comments' => null, 'language' => 'en', 'active' => 1,
        ]);
        ObservationLike::create(['user_id' => $liker->id, 'observation_type' => ObservationLike::TYPE_SESSION, 'observation_id' => $session->id]);
    }
}

function popularObservations(int $count): void
{
    $liker = User::factory()->create();
    for ($i = 0; $i < $count; $i++) {
        $owner = User::factory()->create();
        $id = DB::table('observations')->insertGetId([
            'observer_user_id' => $owner->id, 'observer_username' => $owner->username, 'target_name' => 'M '.($i + 1),
            'observed_on' => '2026-01-15', 'created_at' => now(), 'updated_at' => now(),
        ]);
        ObservationLike::create(['user_id' => $liker->id, 'observation_type' => ObservationLike::TYPE_OBSERVATION, 'observation_id' => $id]);
    }
}

beforeEach(function () {
    Livewire::withoutLazyLoading();
});

it('does not query per row in the popular sessions table', function () {
    popularSessions(2);
    $few = popularQueryCount(PopularSessionsTable::class);

    popularSessions(6);
    $many = popularQueryCount(PopularSessionsTable::class);

    expect($many)->toBe($few);
});

it('does not query per row in the popular observations table', function () {
    popularObservations(2);
    $few = popularQueryCount(PopularObservationsTable::class);

    popularObservations(6);
    $many = popularQueryCount(PopularObservationsTable::class);

    expect($many)->toBe($few);
});

it('links popular observations to their detail page and observer', function () {
    popularObservations(1);
    $owner = User::orderByDesc('id')->first();

    Livewire::test(PopularObservationsTable::class)
        ->assertSee(route('observations.detail', ['observer' => $owner->slug, 'date' => '2026-01-15', 'object' => 'm-1']), false)
        ->assertSee(route('observer.show', $owner->slug), false);
});
