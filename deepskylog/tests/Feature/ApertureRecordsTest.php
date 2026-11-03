<?php

use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\Observation;
use App\Models\SuspectObservation;
use App\Models\User;
use App\Services\Observations\ApertureRecords;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// The smallest aperture and the brightest sky with which an object was seen
// (App\Services\Observations\ApertureRecords).

function apertureRecordsObject(string $name, float $mag, string $type = 'GALXY'): void
{
    DB::table('objects')->insert([
        'name' => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'CVN',
        'ra' => 13.4980, 'decl' => 47.1953, 'mag' => $mag, 'subr' => 13.0, 'pa' => 0,
        'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 300.0, 'diam2' => 200.0,
        'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL' => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function apertureRecordsInstrument(User $user, float $apertureMm, string $type = 'Reflector'): int
{
    return InstrumentFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'name' => $apertureMm.' mm '.$type, 'aperture_mm' => $apertureMm,
        'focal_length_mm' => $apertureMm * 5, 'instrument_type_id' => InstrumentType::firstOrCreate(['name' => $type])->id,
        'mount_type_id' => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
    ])->id;
}

function apertureRecordsObservation(User $user, int $instrumentId, string $object, string $date, array $attributes = []): Observation
{
    return Observation::create($attributes + [
        'observer_username' => $user->username, 'observer_user_id' => $user->id,
        'observed_on' => $date, 'observed_time' => '22:00:00', 'language' => 'en', 'description' => 'Seen',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => $object, 'target_name' => $object,
        'instrument_id' => $instrumentId,
    ]);
}

it('finds the smallest aperture with which an object was seen, leaving out doubtful observations', function () {
    apertureRecordsObject('M 51', 8.4);
    $user = User::factory()->create();

    $notSeen = apertureRecordsObservation($user, apertureRecordsInstrument($user, 30), 'M 51', '2024-01-01');
    DB::table('deepsky_observation_details')->insert(['observation_id' => $notSeen->id, 'visibility' => Observation::VISIBILITY_NOT_SEEN]);
    $suspect = apertureRecordsObservation($user, apertureRecordsInstrument($user, 35), 'M 51', '2024-01-02');
    SuspectObservation::create(['observation_id' => $suspect->id, 'reason' => SuspectObservation::REASON_BELOW_HORIZON, 'details' => []]);
    // 6 mm is a typing error (6 inch), not a record
    apertureRecordsObservation($user, apertureRecordsInstrument($user, 6), 'M 51', '2024-01-03');
    $second = apertureRecordsObservation($user, apertureRecordsInstrument($user, 50), 'M 51', '2024-01-05');
    $first = apertureRecordsObservation($user, apertureRecordsInstrument($user, 50, 'Binoculars'), 'M 51', '2024-01-04');
    apertureRecordsObservation($user, apertureRecordsInstrument($user, 200), 'M 51', '2023-01-01');

    expect((new ApertureRecords)->forObject('M 51')['aperture']->id)->toBe($first->id);

    // A dismissed finding counts again
    SuspectObservation::where('observation_id', $suspect->id)->first()->dismiss($user);
    $record = (new ApertureRecords)->forObject('M 51')['aperture'];
    expect($record->id)->toBe($suspect->id)
        ->and((float) $record->record_aperture_mm)->toBe(35.0);

    // The naked eye counts as a 7 mm pupil, whatever its instrument stores
    $eye = apertureRecordsObservation($user, apertureRecordsInstrument($user, 30, ApertureRecords::NAKED_EYE_TYPE), 'M 51', '2024-02-01');
    $record = (new ApertureRecords)->forObject('M 51')['aperture'];
    expect($record->id)->toBe($eye->id)
        ->and((float) $record->record_aperture_mm)->toBe(7.0)
        ->and((bool) $record->record_naked_eye)->toBeTrue();
});

it('leaves out objects too faint for the aperture under any sky', function () {
    // A planetary nebula with the magnitude of its central star
    apertureRecordsObject('PK 032-03.01', 17.0, 'PLNNB');
    $user = User::factory()->create();
    apertureRecordsObservation($user, apertureRecordsInstrument($user, 200), 'PK 032-03.01', '2024-01-01');

    expect((new ApertureRecords)->forObject('PK 032-03.01')['aperture'])->toBeNull();

    apertureRecordsObservation($user, apertureRecordsInstrument($user, 500), 'PK 032-03.01', '2024-01-02');
    expect((float) (new ApertureRecords)->forObject('PK 032-03.01')['aperture']->record_aperture_mm)->toBe(500.0);
});

it('finds the brightest sky by SQM and by NELM, from the location when the observation has neither', function () {
    apertureRecordsObject('M 51', 8.4);
    $user = User::factory()->create();
    $instrument = apertureRecordsInstrument($user, 200);
    $city = LocationFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'name' => 'City', 'latitude' => 50.8, 'longitude' => 4.7,
        'timezone' => 'Europe/Brussels', 'skyBackground' => 17.5, 'limitingMagnitude' => 3.5,
    ]);

    apertureRecordsObservation($user, $instrument, 'M 51', '2024-01-01', ['sqm' => 18.2]);
    // A typing error
    apertureRecordsObservation($user, $instrument, 'M 51', '2024-01-02', ['sqm' => 1.8]);
    $nelm = apertureRecordsObservation($user, $instrument, 'M 51', '2024-01-03', ['limiting_magnitude' => 4.0, 'location_id' => $city->id]);

    $records = (new ApertureRecords)->forObject('M 51');
    expect((float) $records['sqm']->record_sqm)->toBe(18.2)
        ->and($records['nelm']->id)->toBe($nelm->id)
        ->and((float) $records['nelm']->record_nelm)->toBe(4.0)
        ->and((bool) $records['nelm']->record_sky_from_location)->toBeFalse();

    // Neither on the observation: the sky of its location
    $fromLocation = apertureRecordsObservation($user, $instrument, 'M 51', '2024-01-04', ['location_id' => $city->id]);
    $records = (new ApertureRecords)->forObject('M 51');
    expect($records['sqm']->id)->toBe($fromLocation->id)
        ->and((float) $records['sqm']->record_sqm)->toBe(17.5)
        ->and((bool) $records['sqm']->record_sky_from_location)->toBeTrue()
        ->and($records['nelm']->id)->toBe($fromLocation->id);
});

it('ranks the faintest objects seen up to an aperture and the observers who saw them', function () {
    apertureRecordsObject('M 51', 8.4);
    apertureRecordsObject('NGC 5195', 9.6);
    apertureRecordsObject('NGC 5194A', 12.5);
    apertureRecordsObject('STF 1', 12.0, 'DS');
    $alice = User::factory()->create(['name' => 'Alice Observer']);
    $bob = User::factory()->create(['name' => 'Bob Observer']);

    apertureRecordsObservation($alice, apertureRecordsInstrument($alice, 100), 'NGC 5195', '2024-01-01');
    $smallest = apertureRecordsObservation($bob, apertureRecordsInstrument($bob, 80), 'NGC 5195', '2024-01-02');
    apertureRecordsObservation($alice, apertureRecordsInstrument($alice, 100), 'M 51', '2024-01-01');
    // Too large for the limit
    apertureRecordsObservation($alice, apertureRecordsInstrument($alice, 254), 'NGC 5194A', '2024-01-01');
    // Double stars are left out
    apertureRecordsObservation($alice, apertureRecordsInstrument($alice, 100), 'STF 1', '2024-01-01');

    $objects = (new ApertureRecords)->ranking(100);
    expect(array_column($objects, 'name'))->toBe(['NGC 5195', 'M 51'])
        ->and($objects[0]['observation_id'])->toBe($smallest->id)
        ->and($objects[0]['aperture_mm'])->toBe(80.0);

    // Both saw NGC 5195: they share the first place
    $observers = (new ApertureRecords)->observers(100);
    expect(array_column($observers, 'rank'))->toBe([1, 1])
        ->and(array_column($observers, 'mag'))->toBe([9.6, 9.6]);

    $this->get('/records/aperture?aperture=100')
        ->assertOk()
        ->assertSee('NGC 5195')
        ->assertSee('Bob Observer')
        ->assertDontSee('NGC 5194A')
        ->assertDontSee('STF 1');

    $this->get('/records/aperture?aperture=0')
        ->assertOk()
        ->assertSee('No objects were reported seen with this aperture yet.');

    // Without a limit on the aperture the 254 mm observation counts too
    $all = (new ApertureRecords)->ranking(ApertureRecords::ALL);
    expect(array_column($all, 'name'))->toBe(['NGC 5194A', 'NGC 5195', 'M 51'])
        ->and($all[1]['observation_id'])->toBe($smallest->id);
    expect(array_column((new ApertureRecords)->observers(ApertureRecords::ALL), 'mag'))->toBe([12.5, 9.6]);

    $this->get('/records/aperture?aperture=all')
        ->assertOk()
        ->assertSee('NGC 5194A')
        ->assertSee('All apertures')
        ->assertSee('reported seen with any aperture');

    // An unknown value falls back to the default limit
    $this->get('/records/aperture?aperture=300')
        ->assertOk()
        ->assertDontSee('NGC 5194A');
});

it('shows the records on the object page with the viewer\'s telescope', function () {
    apertureRecordsObject('M 51', 8.4);
    $user = User::factory()->create(['name' => 'Alice Observer']);
    apertureRecordsObservation($user, apertureRecordsInstrument($user, 80, 'Refractor'), 'M 51', '2024-01-01', ['sqm' => 19.1]);
    $viewer = User::factory()->create();
    $viewer->forceFill(['stdtelescope' => apertureRecordsInstrument($viewer, 114)])->save();

    $this->actingAs($viewer)
        ->blade('<x-aperture-records object-name="M 51" />')
        ->assertSee('Smallest aperture')
        ->assertSee('80 mm')
        ->assertSee('19.10')
        ->assertSee('Alice Observer')
        ->assertSee('is at least as large as the smallest aperture', false)
        ->assertDontSee('Brightest sky (NELM)');

    $this->blade('<x-aperture-records object-name="NGC 1" />')->assertDontSee('Smallest aperture');
});
