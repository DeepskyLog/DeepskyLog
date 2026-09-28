<?php

use App\Jobs\BuildSessionCopies;
use App\Jobs\SendSessionInvitations;
use App\Livewire\CreateSession;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function fixesTestLocation(User $owner): Location
{
    return Location::query()->create([
        'user_id' => $owner->id,
        'name' => 'Fixes Test Site',
        'longitude' => 4.5,
        'latitude' => 50.5,
        'timezone' => 'Europe/Brussels',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 120,
        'country' => 'Belgium',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'fixes-test-site',
    ]);
}

function fixesTestSession(User $owner, Location $location, int $id, string $begin, string $end): ObservationSession
{
    return ObservationSession::query()->create([
        'id' => $id,
        'name' => 'Session '.$id,
        'observerid' => $owner->username,
        'begindate' => $begin.' 00:00:00',
        'enddate' => $end.' 23:59:59',
        'locationid' => $location->id,
        'weather' => '',
        'equipment' => '',
        'comments' => null,
        'language' => 'en',
        'active' => 1,
    ]);
}

function fixesTestObservation(User $observer, string $date, array $extra = []): Observation
{
    return Observation::query()->create(array_merge([
        'observer_username' => $observer->username,
        'observer_user_id' => $observer->id,
        'observed_on' => $date,
        'language' => 'en',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 65',
        'target_name' => 'M 65',
        'has_drawing' => false,
    ], $extra));
}

function fixesTestM65(): void
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

function fixesTestSessionObservations(int $sessionId): array
{
    return DB::table('sessionObservations')->where('sessionid', $sessionId)
        ->pluck('observationid')->map(fn ($v) => (int) $v)->sort()->values()->all();
}

it('links new observations to the sessions of the observer and of the sessions they take part in', function () {
    $owner = User::factory()->create(['username' => 'owner']);
    $guest = User::factory()->create(['username' => 'guest']);
    $location = fixesTestLocation($owner);
    $session = fixesTestSession($owner, $location, 31001, '2026-03-01', '2026-03-02');
    DB::table('sessionObservers')->insert(['sessionid' => $session->id, 'observer' => $guest->username]);

    $inRange = fixesTestObservation($owner, '2026-03-02');
    $guestObservation = fixesTestObservation($guest, '2026-03-01');
    fixesTestObservation($owner, '2026-03-05');

    expect(fixesTestSessionObservations($session->id))->toBe([$inRange->id, $guestObservation->id]);
});

it('moves an observation to another session when its date changes and unlinks it when deleted', function () {
    $owner = User::factory()->create(['username' => 'owner']);
    $location = fixesTestLocation($owner);
    $first = fixesTestSession($owner, $location, 31002, '2026-03-01', '2026-03-01');
    $second = fixesTestSession($owner, $location, 31003, '2026-03-10', '2026-03-11');

    $observation = fixesTestObservation($owner, '2026-03-01');
    expect(fixesTestSessionObservations($first->id))->toBe([$observation->id]);

    $observation->update(['observed_on' => '2026-03-11']);
    expect(fixesTestSessionObservations($first->id))->toBe([])
        ->and(fixesTestSessionObservations($second->id))->toBe([$observation->id]);

    $observation->delete();
    expect(fixesTestSessionObservations($second->id))->toBe([]);
});

it('updates a session without crashing and relinks observations, copies and invitations for added observers', function () {
    Bus::fake([BuildSessionCopies::class, SendSessionInvitations::class]);

    $owner = User::factory()->create(['username' => 'owner']);
    $existing = User::factory()->create(['username' => 'existing']);
    $added = User::factory()->create(['username' => 'added']);
    $location = fixesTestLocation($owner);
    $session = fixesTestSession($owner, $location, 31004, '2026-03-01', '2026-03-01');
    DB::table('sessionObservers')->insert(['sessionid' => $session->id, 'observer' => $existing->username]);

    $before = fixesTestObservation($owner, '2026-03-01');
    $after = fixesTestObservation($owner, '2026-03-03');
    $addedObservation = fixesTestObservation($added, '2026-03-03');
    expect(fixesTestSessionObservations($session->id))->toBe([$before->id]);

    Livewire::actingAs($owner)
        ->test(CreateSession::class, ['session' => $session])
        ->set('begindate', '2026-03-02')
        ->set('enddate', '2026-03-03')
        ->set('otherObservers', [$existing->username, $added->username])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect(fixesTestSessionObservations($session->id))->toBe([$after->id, $addedObservation->id]);

    Bus::assertDispatched(BuildSessionCopies::class, fn ($job) => $job->sessionId === $session->id && $job->copiesFor === ['added']);
    Bus::assertDispatched(SendSessionInvitations::class, fn ($job) => $job->recipients === ['added']);
});

it('creates copies only for the given participants', function () {
    $owner = User::factory()->create(['username' => 'owner']);
    $existing = User::factory()->create(['username' => 'existing']);
    $added = User::factory()->create(['username' => 'added']);
    $location = fixesTestLocation($owner);
    $session = fixesTestSession($owner, $location, 31005, '2026-03-01', '2026-03-01');
    $ownerObservation = fixesTestObservation($owner, '2026-03-01');

    (new BuildSessionCopies($session->id, ['owner', 'existing', 'added'], ['added']))->handle();

    expect(ObservationSession::where('observerid', 'existing')->exists())->toBeFalse();
    $copy = ObservationSession::where('observerid', 'added')->first();
    expect($copy)->not->toBeNull()
        ->and($copy->active)->toBe(0)
        ->and(fixesTestSessionObservations($copy->id))->toBe([$ownerObservation->id]);
});

it('gives the time of an observation as HHMM and treats the migrated placeholder as unknown', function () {
    $observer = User::factory()->create();

    expect(fixesTestObservation($observer, '2026-03-01', ['observed_time' => '21:35:00'])->time)->toBe(2135)
        ->and(fixesTestObservation($observer, '2026-03-01', ['observed_time' => '00:00:00'])->time)->toBe(0)
        ->and(fixesTestObservation($observer, '2026-03-01')->time)->toBeNull()
        ->and(fixesTestObservation($observer, '2026-03-01', ['observed_time' => '23:59:00', 'legacy_time_int' => -9999])->time)->toBeNull()
        ->and(fixesTestObservation($observer, '2026-03-01', ['legacy_time_int' => 145])->time)->toBe(145)
        // An edited time wins over the stale legacy value.
        ->and(fixesTestObservation($observer, '2026-03-01', ['observed_time' => '22:10:00', 'legacy_time_int' => 2130])->time)->toBe(2210);
});

it('stores the sqm and limiting magnitude of an observation and prefills them when editing', function () {
    $user = User::factory()->create(['username' => 'sqmuser', 'slug' => 'sqm-user']);
    fixesTestM65();

    $this->actingAs($user)->post(route('observation.store'), [
        'target_table' => 'objects',
        'target_slug' => 'm-65',
        'observed_on' => '2026-03-01',
        'observed_time' => '22:15',
        'sqm' => '21.3',
        'limiting_magnitude' => '6.2',
    ])->assertSessionHasNoErrors();

    $observation = Observation::latest('id')->first();
    expect((float) $observation->getRawOriginal('sqm'))->toBe(21.3)
        ->and((float) $observation->getRawOriginal('limiting_magnitude'))->toBe(6.2);

    $this->actingAs($user)->post(route('observation.store'), [
        'target_table' => 'objects',
        'target_slug' => 'm-65',
        'observed_on' => '2026-03-01',
        'sqm' => '35',
    ])->assertSessionHasErrors('sqm');

    $this->actingAs($user)
        ->get('/observation/sqm-user/2026-03-01/m-65/1/edit')
        ->assertRedirect();
    expect(session()->getOldInput('sqm'))->toBe(21.3)
        ->and(session()->getOldInput('limiting_magnitude'))->toBe(6.2)
        ->and(session()->getOldInput('observed_time'))->toBe('22:15');
});

it('converts between the local time of the location and UT', function () {
    expect(Observation::utcFromLocal('2026-03-02', '00:30', 'Europe/Brussels'))->toBe(['2026-03-01', '23:30:00'])
        ->and(Observation::utcFromLocal('2026-07-01', '23:30', 'Europe/Brussels'))->toBe(['2026-07-01', '21:30:00'])
        ->and(Observation::utcFromLocal('2026-03-01', '21:00', 'America/Santiago'))->toBe(['2026-03-02', '00:00:00'])
        ->and(Observation::utcFromLocal('2026-03-01', null, 'Europe/Brussels'))->toBe(['2026-03-01', null])
        ->and(Observation::utcFromLocal('2026-03-01', '21:00', null))->toBe(['2026-03-01', '21:00:00']);
});

it('stores the time entered in the time zone of the location in UT and shows it in local time', function () {
    $user = User::factory()->create(['username' => 'tzuser', 'slug' => 'tz-user']);
    $location = fixesTestLocation($user);
    fixesTestM65();

    $this->actingAs($user)->post(route('observation.store'), [
        'target_table' => 'objects',
        'target_slug' => 'm-65',
        'observed_on' => '2026-03-02',
        'observed_time' => '00:30',
        'location_id' => $location->id,
    ])->assertSessionHasNoErrors();

    $observation = Observation::latest('id')->first();
    expect($observation->observed_on->format('Y-m-d'))->toBe('2026-03-01')
        ->and($observation->observed_time)->toBe('23:30:00')
        ->and($observation->time)->toBe(2330)
        ->and($observation->localDate())->toBe('2026-03-02')
        ->and($observation->localTime())->toBe('00:30');

    // The URL keeps the UT date; the page shows the local date and time.
    $this->get(route('observations.detail', ['observer' => 'tz-user', 'date' => '2026-03-01', 'object' => 'm-65']))
        ->assertOk()
        ->assertSee('00:30 CET')
        ->assertSee('2 Mar 2026');

    $this->get('/observation/tz-user/2026-03-01/m-65/1/edit')->assertRedirect();
    expect(session()->getOldInput('observed_on'))->toBe('2026-03-02')
        ->and(session()->getOldInput('observed_time'))->toBe('00:30');
});

it('stores the begin of an oal observation in UT', function () {
    $parse = (new ReflectionClass(\App\Services\Import\OalObservationImporter::class))->getMethod('parseOalDateTime');
    $importer = (new ReflectionClass(\App\Services\Import\OalObservationImporter::class))->newInstanceWithoutConstructor();

    expect($parse->invoke($importer, '2026-03-02T00:30:00+01:00'))->toBe(['2026-03-01', '23:30:00'])
        ->and($parse->invoke($importer, '2026-03-01T21:15:00Z'))->toBe(['2026-03-01', '21:15:00'])
        ->and($parse->invoke($importer, '2026-03-01T21:15:00'))->toBe(['2026-03-01', '21:15:00'])
        ->and($parse->invoke($importer, '2026-03-01'))->toBe(['2026-03-01', null]);
});
