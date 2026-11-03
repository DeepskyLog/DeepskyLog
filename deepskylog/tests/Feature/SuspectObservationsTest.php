<?php

use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\Observation;
use App\Models\SuspectObservation;
use App\Models\User;
use App\Services\Observations\SuspectObservations;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// The observations that look wrong (App\Services\Observations\SuspectObservations).

function suspectObject(string $name, float $ra, float $decl, string $type = 'GALXY', float $mag = 9.0, float $diam1 = 120, float $diam2 = 60): void
{
    DB::table('objects')->insert([
        'name' => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'HER',
        'ra' => $ra, 'decl' => $decl, 'mag' => $mag, 'subr' => 99.9, 'pa' => 0,
        'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $diam1, 'diam2' => $diam2,
        'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL' => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

/**
 * An observer in Leuven (50.8° N) under a sky of SQM 18 with a 50 mm
 * refractor of 500 mm focal length and no eyepieces (generic magnifications).
 *
 * @return array{0: User, 1: int, 2: int} the observer, instrument id and location id
 */
function suspectObserver(): array
{
    $user = User::factory()->create();
    $telescope = InstrumentFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'name' => 'Small refractor', 'aperture_mm' => 50, 'focal_length_mm' => 500,
        'obstruction_perc' => 0, 'fixedMagnification' => null,
        'instrument_type_id' => InstrumentType::firstOrCreate(['name' => 'Refractor'])->id,
        'mount_type_id' => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
    ]);
    $location = LocationFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'name' => 'Leuven', 'latitude' => 50.8, 'longitude' => 4.7,
        'timezone' => 'Europe/Brussels', 'skyBackground' => 18.0, 'limitingMagnitude' => -999,
    ]);

    return [$user, $telescope->id, $location->id];
}

function suspectObservation(User $user, int $instrumentId, int $locationId, string $object, array $attributes = []): Observation
{
    return Observation::create($attributes + [
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'instrument_id' => $instrumentId,
        'location_id' => $locationId,
        'observed_on' => '2026-01-15',
        'observed_time' => '12:00:00',
        'description' => 'Seen',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $object,
        'target_name' => $object,
    ]);
}

function checkAllSuspects(): void
{
    $suspects = new SuspectObservations();
    $suspects->check(Observation::query());
    $suspects->checkDuplicates();
}

it('finds an object below the horizon at the time of the observation', function () {
    [$user, $instrument, $location] = suspectObserver();
    suspectObject('NGC 9001', 18.7, 10.0);
    // 00:00 UT in January: RA 18.7 h is near its lower culmination, about 29° below the horizon
    $below = suspectObservation($user, $instrument, $location, 'NGC 9001', ['observed_time' => '00:00:00']);
    // 12:00 UT: near its transit, about 49° high
    $above = suspectObservation($user, $instrument, $location, 'NGC 9001', ['observed_time' => '12:00:00']);

    checkAllSuspects();

    $suspect = SuspectObservation::where('observation_id', $below->id)->sole();
    expect($suspect->reason)->toBe(SuspectObservation::REASON_BELOW_HORIZON)
        ->and($suspect->details['altitude'])->toBeLessThan(-20)
        ->and($suspect->details['moment'])->toBe('2026-01-15 00:00')
        ->and($suspect->explanation())->toContain('Leuven')
        ->and(SuspectObservation::where('observation_id', $above->id)->exists())->toBeFalse();
});

it('finds an object that never rises at the location, without a time', function () {
    [$user, $instrument, $location] = suspectObserver();
    suspectObject('NGC 9002', 10.0, -60.0);
    $observation = suspectObservation($user, $instrument, $location, 'NGC 9002', ['observed_time' => null]);

    checkAllSuspects();

    $suspect = SuspectObservation::where('observation_id', $observation->id)->sole();
    expect($suspect->reason)->toBe(SuspectObservation::REASON_BELOW_HORIZON)
        ->and($suspect->details['moment'])->toBeNull();
});

it('finds a faint galaxy marked as seen with a small telescope, not one marked as not seen or an open cluster', function () {
    [$user, $instrument, $location] = suspectObserver();
    suspectObject('NGC 9003', 18.7, 40.0, 'GALXY', 15.5, 60, 30);
    suspectObject('NGC 9004', 18.7, 40.0, 'OPNCL', 15.5, 60, 30);
    suspectObject('NGC 9005', 18.7, 40.0, 'GALXY', 8.0, 300, 200);
    $faint = suspectObservation($user, $instrument, $location, 'NGC 9003');
    $notSeen = suspectObservation($user, $instrument, $location, 'NGC 9003', ['observed_time' => '12:30:00']);
    DB::table('deepsky_observation_details')->insert(['observation_id' => $notSeen->id, 'visibility' => Observation::VISIBILITY_NOT_SEEN]);
    $cluster = suspectObservation($user, $instrument, $location, 'NGC 9004');
    $bright = suspectObservation($user, $instrument, $location, 'NGC 9005');

    checkAllSuspects();

    $suspect = SuspectObservation::where('observation_id', $faint->id)->sole();
    expect($suspect->reason)->toBe(SuspectObservation::REASON_TOO_FAINT)
        ->and($suspect->details['contrast_reserve'])->toBeLessThan(SuspectObservations::FAINT_BELOW)
        ->and($suspect->details['sqm'])->toEqual(18.0)
        ->and(SuspectObservation::whereIn('observation_id', [$notSeen->id, $cluster->id, $bright->id])->exists())->toBeFalse();
});

it('finds a duplicate and forgets it when the original is deleted', function () {
    [$user, $instrument, $location] = suspectObserver();
    suspectObject('NGC 9005', 18.7, 40.0, 'GALXY', 8.0, 300, 200);
    $original = suspectObservation($user, $instrument, $location, 'NGC 9005');
    $copy = suspectObservation($user, $instrument, $location, 'NGC 9005', ['description' => 'Seen again']);
    $otherTime = suspectObservation($user, $instrument, $location, 'NGC 9005', ['observed_time' => '13:00:00']);

    checkAllSuspects();

    $suspect = SuspectObservation::sole();
    expect($suspect->observation_id)->toBe($copy->id)
        ->and($suspect->reason)->toBe(SuspectObservation::REASON_DUPLICATE)
        ->and($suspect->related_observation_id)->toBe($original->id);

    $original->delete();

    expect(SuspectObservation::count())->toBe(0);
});

it('keeps a dismissal while the check finds it and forgets an open finding when the observation is corrected', function () {
    [$user, $instrument, $location] = suspectObserver();
    suspectObject('NGC 9001', 18.7, 10.0);
    $observation = suspectObservation($user, $instrument, $location, 'NGC 9001', ['observed_time' => '00:00:00']);
    checkAllSuspects();
    SuspectObservation::sole()->dismiss($user);

    checkAllSuspects();

    expect(SuspectObservation::sole()->dismissed_at)->not->toBeNull();

    SuspectObservation::sole()->restore();
    $observation->update(['observed_time' => '12:00:00']);

    expect(SuspectObservation::count())->toBe(0);

    checkAllSuspects();

    expect(SuspectObservation::count())->toBe(0);
});

it('checks only the changed observations after the first run', function () {
    [$user, $instrument, $location] = suspectObserver();
    suspectObject('NGC 9001', 18.7, 10.0);

    $this->artisan('observations:check-suspect')->assertSuccessful();
    $this->travel(5)->minutes();
    $observation = suspectObservation($user, $instrument, $location, 'NGC 9001', ['observed_time' => '00:00:00']);
    suspectObservation($user, $instrument, $location, 'NGC 9001', ['observed_time' => '00:00:00']);

    $this->artisan('observations:check-suspect')
        ->expectsOutputToContain('Checking 2 observations changed since')
        ->assertSuccessful();

    expect(SuspectObservation::where('observation_id', $observation->id)->value('reason'))->toBe(SuspectObservation::REASON_BELOW_HORIZON)
        ->and(SuspectObservation::where('reason', SuspectObservation::REASON_DUPLICATE)->count())->toBe(1);
});

it('shows the observer their own suspect observations and lets them mark one as right', function () {
    [$user, $instrument, $location] = suspectObserver();
    suspectObject('NGC 9001', 18.7, 10.0);
    suspectObservation($user, $instrument, $location, 'NGC 9001', ['observed_time' => '00:00:00']);
    checkAllSuspects();
    $suspect = SuspectObservation::sole();

    $this->actingAs($user)->get(route('observations.suspect'))
        ->assertOk()
        ->assertSee('NGC 9001')
        ->assertSee('below the horizon of Leuven');

    $other = User::factory()->create();
    $this->actingAs($other)->get(route('observations.suspect'))->assertOk()->assertDontSee('NGC 9001');
    $this->actingAs($other)->post(route('suspect-observations.dismiss', $suspect))->assertForbidden();

    $this->actingAs($user)->post(route('suspect-observations.dismiss', $suspect))->assertRedirect();

    expect($suspect->fresh()->dismissed_at)->not->toBeNull()
        ->and(SuspectObservations::openCount($user))->toBe(0);
    $this->actingAs($user)->get(route('observations.suspect'))->assertDontSee('below the horizon of Leuven');
});

it('lists all suspect observations for administrators only', function () {
    // made first: it gets a fixed id
    $admin = $this->createUserAndAssignToTeam('Administrators');
    [$user, $instrument, $location] = suspectObserver();
    suspectObject('NGC 9001', 18.7, 10.0);
    suspectObservation($user, $instrument, $location, 'NGC 9001', ['observed_time' => '00:00:00']);
    checkAllSuspects();

    $this->actingAs($user)->get(route('admin.suspect-observations'))->assertForbidden();

    $this->actingAs($admin)->get(route('admin.suspect-observations', ['reason' => SuspectObservation::REASON_BELOW_HORIZON]))
        ->assertOk()
        ->assertSee('NGC 9001')
        ->assertSee($user->username);
    $this->actingAs($admin)->get(route('admin.suspect-observations', ['observer' => 'nobody']))
        ->assertOk()
        ->assertDontSee('NGC 9001');
    $this->actingAs($admin)->get(route('admin.objects.check'))
        ->assertOk()
        ->assertSee('Suspect observations')
        ->assertSee('1 to check');
});
