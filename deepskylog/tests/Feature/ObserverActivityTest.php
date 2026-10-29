<?php

use App\Livewire\ObserverActivityCalendar;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\ObserverActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function activityObservation(User $observer, string $object, string $date, array $attributes = []): Observation
{
    return Observation::query()->create(array_merge([
        'observer_username' => $observer->username,
        'observer_user_id'  => $observer->id,
        'observed_on'       => $date,
        'observed_time'     => '22:00:00',
        'language'          => 'en',
        'description'       => '',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => $object,
        'target_name'       => $object,
        'has_drawing'       => false,
    ], $attributes));
}

beforeEach(function () {
    Carbon::setTestNow('2026-10-29 12:00:00');
    $this->observer = User::factory()->create(['name' => 'Night Owl', 'username' => 'nightowl', 'slug' => 'night-owl', 'language' => 'en']);
    $this->location = Location::query()->create([
        'user_id'           => $this->observer->id,
        'name'              => 'Owl Site',
        'longitude'         => 4.5,
        'latitude'          => 50.5,
        'timezone'          => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground'     => -999,
        'elevation'         => 120,
        'country'           => 'Belgium',
        'active'            => true,
        'observer'          => '',
        'observations'      => 0,
        'slug'              => 'owl-site',
    ]);
});

afterEach(fn () => Carbon::setTestNow());

it('counts the different objects seen per night', function () {
    $site = ['location_id' => $this->location->id];
    // One night: the evening of 10 February and the morning after, M 1 twice.
    activityObservation($this->observer, 'M 1', '2025-02-10', $site);
    activityObservation($this->observer, 'M 2', '2025-02-10', $site);
    activityObservation($this->observer, 'M 1', '2025-02-11', $site + ['observed_time' => '02:00:00']);
    // A night with only an object that was not seen.
    $notSeen = activityObservation($this->observer, 'M 3', '2024-08-20', $site);
    DB::table('deepsky_observation_details')->insert(['observation_id' => $notSeen->id, 'visibility' => Observation::VISIBILITY_NOT_SEEN]);

    $activity = new ObserverActivity($this->observer);

    expect($activity->nights())->toBe([
        '2024-08-20' => ['objects' => 0, 'observations' => 1],
        '2025-02-10' => ['objects' => 2, 'observations' => 3],
    ])->and($activity->years())->toBe([2025, 2024]);

    $year = $activity->year(2025);
    $days = collect($year['weeks'])->flatten(1)->filter();
    expect($days)->toHaveCount(365)
        ->and($year['nights'])->toBe(1)
        ->and($year['observations'])->toBe(3)
        ->and($days->firstWhere('date', '2025-02-10')['level'])->toBe(1)
        ->and($days->firstWhere('date', '2025-02-11')['level'])->toBe(0)
        // 1 January 2025 is a Wednesday.
        ->and($year['weeks'][0][2]['date'])->toBe('2025-01-01')
        ->and($year['weeks'][0][1])->toBeNull()
        ->and($year['months'])->toHaveCount(12);
});

it('sees a new observation at once', function () {
    activityObservation($this->observer, 'M 1', '2025-02-10');
    expect((new ObserverActivity($this->observer))->nights())->toHaveCount(1);

    activityObservation($this->observer, 'M 2', '2025-03-10');
    expect((new ObserverActivity($this->observer))->nights())->toHaveCount(2);
});

it('gives the levels by the number of objects', function () {
    expect(ObserverActivity::level(0))->toBe(1)
        ->and(ObserverActivity::level(2))->toBe(1)
        ->and(ObserverActivity::level(3))->toBe(2)
        ->and(ObserverActivity::level(6))->toBe(3)
        ->and(ObserverActivity::level(11))->toBe(4);
});

it('marks the nights of the new Moons', function () {
    // New Moons of 2026: 18 Jan 19:52 UT, 17 Mar 01:23 UT ... 9 Dec 00:52 UT.
    $nights = ObserverActivity::newMoonNights(2026);

    expect($nights)->toHaveCount(12)
        ->and($nights[0])->toBe('2026-01-18')
        ->and($nights)->toContain('2026-03-18')
        ->and(end($nights))->toBe('2026-12-08');
});

it('shows the calendar with links to the blog', function () {
    activityObservation($this->observer, 'M 1', '2025-02-10', ['location_id' => $this->location->id]);
    activityObservation($this->observer, 'M 1', '2024-02-10', ['location_id' => $this->location->id]);

    Livewire::test(ObserverActivityCalendar::class, ['observer' => $this->observer])
        ->assertSee('Observing activity')
        ->assertSee(route('observer.blog', ['observer' => 'night-owl', 'before' => '2025-02-11']), false)
        ->set('year', 2024)
        ->assertSee(route('observer.blog', ['observer' => 'night-owl', 'before' => '2024-02-11']), false)
        ->assertDontSee('before=2025-02-11', false);
});

it('shows nothing for an observer without observations', function () {
    Livewire::test(ObserverActivityCalendar::class, ['observer' => $this->observer])
        ->assertDontSee('Observing activity');
});
