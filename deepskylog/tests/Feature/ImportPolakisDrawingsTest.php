<?php

use App\Models\Instrument;
use App\Models\Location;
use App\Models\Observation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ObservationImportFixtures as Fixtures;

// observations:import-polakis-drawings links the drawings of Tom Polakis's
// pbase gallery (database/polakis_drawings.csv) to his observations.

function polakisJpeg(): string
{
    $image = imagecreatetruecolor(8, 8);
    ob_start();
    imagejpeg($image);

    return (string) ob_get_clean();
}

beforeEach(function () {
    Storage::fake('public');
    Http::fake(['pbase.test/*' => Http::response(polakisJpeg())]);
    Fixtures::seedTargets();
    // The name resolver looks the objects up in the search index.
    foreach (['M 13', 'M 31', 'M 42', 'NGC 104'] as $name) {
        DB::table('search_index')->insert([
            'name' => $name, 'name_normalized' => strtolower(str_replace(' ', '', $name)), 'source_table' => 'objects',
            'source_pk' => strtolower(str_replace(' ', '-', $name)), 'display_name' => $name, 'source_type' => 'object',
            'name_unique' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $this->observer = Fixtures::createObserver(['username' => 'polakis', 'name' => 'Tom Polakis', 'slug' => 'polakis']);
    $this->telescope = Instrument::query()->where('user_id', $this->observer->id)->where('instrument_type_id', 4)->first();
    $this->telescope->update(['name' => '13" telescope']);
    $this->site = Location::query()->where('user_id', $this->observer->id)->first();
    $this->site->update(['name' => 'Vekol']);

    $this->file = tempnam(sys_get_temp_dir(), 'polakis');
    file_put_contents($this->file, implode("\n", [
        'pbase_id,title,instrument,magnification,filter,date,site,original',
        // Logged the evening before the date on the drawing.
        '1,M13,13-inch,115,,1987-05-30,Vekol Rd.,https://pbase.test/1.jpg',
        // No observation of that night.
        '2,M31,13-inch,75,,1988-10-07,Vekol Rd.,https://pbase.test/2.jpg',
        // A nebula of the SMC, not NGC 19.
        '3,N 19,13-inch,115,,1993-11-10,LCO,https://pbase.test/3.jpg',
        // Binoculars he has no instrument for.
        '4,M42,60mm,9,,1988-09-05,Vekol Rd.,https://pbase.test/4.jpg',
    ])."\n");

    $this->m13 = Observation::query()->create([
        'observer_username' => 'polakis', 'observer_user_id' => $this->observer->id,
        'observed_on' => '1987-05-29', 'instrument_id' => $this->telescope->id, 'location_id' => $this->site->id,
        'language' => 'en', 'description' => 'Resolved to the core',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 13', 'target_name' => 'M 13',
    ]);
});

afterEach(fn () => @unlink($this->file));

it('changes nothing in a dry run', function () {
    $this->artisan('observations:import-polakis-drawings', ['--file' => $this->file])->assertSuccessful();

    expect($this->m13->fresh()->has_drawing)->toBeFalse()
        ->and(Storage::disk('public')->allFiles())->toBe([]);
    Http::assertNothingSent();
});

it('links a drawing to the observation of its night with the same telescope', function () {
    $this->artisan('observations:import-polakis-drawings', ['--file' => $this->file, '--apply' => true])->assertSuccessful();

    $m13 = $this->m13->fresh();
    expect($m13->has_drawing)->toBeTrue()
        ->and($m13->drawing_filename)->toBe('observations/polakis-1.jpg')
        ->and(DB::table('deepsky_observation_details')->where('observation_id', $m13->id)->value('magnification'))->toEqual(115);
    Storage::disk('public')->assertExists('observations/polakis-1.jpg');

    // Without --create the other drawings get no observation.
    expect(Observation::query()->count())->toBe(1);
    Http::assertSentCount(1);
});

it('adds an observation for a drawing of a night the log does not have', function () {
    $this->artisan('observations:import-polakis-drawings', ['--file' => $this->file, '--apply' => true, '--create' => true])->assertSuccessful();

    $m31 = Observation::query()->where('target_name', 'M 31')->sole();
    expect($m31->observed_on->format('Y-m-d'))->toBe('1988-10-07')
        ->and($m31->instrument_id)->toBe($this->telescope->id)
        ->and($m31->location_id)->toBe($this->site->id)
        ->and($m31->drawing_filename)->toBe('observations/polakis-2.jpg');

    // N 19 is not read as NGC 19, and the drawing in binoculars is left out.
    expect(Observation::query()->count())->toBe(2);

    // A second run finds the drawings linked.
    $this->artisan('observations:import-polakis-drawings', ['--file' => $this->file, '--apply' => true, '--create' => true])
        ->expectsOutputToContain('0 drawings belong to an observation, 0 have none and get an observation of their own, 2 are linked already')
        ->assertSuccessful();
    expect(Observation::query()->count())->toBe(2);
});
