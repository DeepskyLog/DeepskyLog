<?php

use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\OnThisNight;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

function onThisNightObservation(User $observer, string $object, string $date, array $attributes = []): Observation
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
    Carbon::setTestNow('2026-10-30 20:00:00');
    $this->observer = User::factory()->create(['name' => 'Night Owl', 'username' => 'nightowl', 'slug' => 'night-owl', 'language' => 'en']);
    $this->location = Location::query()->create([
        'user_id'           => $this->observer->id,
        'name'              => 'Owl Site',
        'longitude'         => -100,
        'latitude'          => 40,
        'timezone'          => 'America/Chicago',
        'limitingMagnitude' => -999,
        'skyBackground'     => -999,
        'elevation'         => 120,
        'country'           => 'United States',
        'active'            => true,
        'observer'          => '',
        'observations'      => 0,
        'slug'              => 'owl-site',
    ]);
});

afterEach(fn () => Carbon::setTestNow());

it('shows the observations of this night in earlier years', function () {
    $site = ['location_id' => $this->location->id];
    // 30 October 2024, 21:00 in Chicago is 31 October 02:00 UT.
    onThisNightObservation($this->observer, 'M 1', '2024-10-31', $site + ['observed_time' => '02:00:00', 'has_drawing' => true]);
    onThisNightObservation($this->observer, 'M 2', '2024-10-31', $site + ['observed_time' => '03:00:00']);
    $notSeen = onThisNightObservation($this->observer, 'M 3', '2024-10-31', $site + ['observed_time' => '04:00:00']);
    DB::table('deepsky_observation_details')->insert(['observation_id' => $notSeen->id, 'visibility' => Observation::VISIBILITY_NOT_SEEN]);
    // 30 October 2020 at 23:00 UT is the evening in Chicago.
    onThisNightObservation($this->observer, 'M 31', '2020-10-30', $site + ['observed_time' => '23:00:00']);
    // The night of 31 October 2023, not this one.
    onThisNightObservation($this->observer, 'M 42', '2023-11-01', $site + ['observed_time' => '03:00:00']);
    // This year's own night is not an earlier one.
    onThisNightObservation($this->observer, 'M 45', '2026-10-30', $site);

    $result = OnThisNight::forHomePage($this->observer);

    expect($result['night'])->toBe('2026-10-30')
        ->and(collect($result['years'])->pluck('night')->all())->toBe(['2024-10-30', '2020-10-30'])
        ->and($result['years'][0]['observations'])->toBe(3)
        ->and(collect($result['years'][0]['objects'])->pluck('name')->all())->toBe(['M 1', 'M 2'])
        ->and($result['years'][0]['years_ago'])->toBe(2)
        ->and($result['years'][0]['drawing']?->target_name)->toBe('M 1')
        ->and($result['years'][1]['drawing'])->toBeNull()
        ->and($result['years'][0]['blog_url'])->toContain('before=2024-10-31');
});

it('takes the night before in the morning and gives nothing without earlier nights', function () {
    Carbon::setTestNow('2026-10-31 10:00:00');
    expect(OnThisNight::tonight($this->observer)->toDateString())->toBe('2026-10-30')
        ->and(OnThisNight::forHomePage($this->observer))->toBeNull();
});

it('shows the box on the home page', function () {
    onThisNightObservation($this->observer, 'M 1', '2024-10-30', ['location_id' => $this->location->id]);

    $this->actingAs($this->observer)->get('/')
        ->assertOk()
        ->assertSee('On this night')
        ->assertSee('2 years ago');
});
