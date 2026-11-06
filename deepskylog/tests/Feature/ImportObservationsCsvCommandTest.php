<?php

use App\Models\Observation;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ObservationImportFixtures;

function importCsvCommandFolder(): string
{
    $folder = sys_get_temp_dir().'/import-csv-command-'.uniqid();
    mkdir($folder);
    $image = imagecreatetruecolor(20, 20);
    imagepng($image, $folder.'/Jupiter drawing.png');
    file_put_contents($folder.'/observations.csv',
        "Jupiter;Someone;27-03-2017;21:55;Example Site;Example Dobson 250;Example 10mm;;;;;;;en;See drawing;Jupiter drawing.png\n");

    return $folder;
}

beforeEach(function () {
    Storage::fake('public');
    ObservationImportFixtures::seedTargets();
});

it('imports nothing without --apply', function () {
    $user = ObservationImportFixtures::createObserver();
    $folder = importCsvCommandFolder();

    $this->artisan('observations:import-csv', ['observer' => $user->username, 'file' => $folder.'/observations.csv', '--drawings' => $folder])
        ->expectsOutputToContain('Would import 1 observations (0 updated), 1 drawings, 0 skipped.')
        ->assertSuccessful();

    expect(Observation::query()->count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});

it('imports the observations with their drawings once', function () {
    $user = ObservationImportFixtures::createObserver();
    $folder = importCsvCommandFolder();
    $arguments = ['observer' => $user->username, 'file' => $folder.'/observations.csv', '--drawings' => $folder, '--apply' => true];

    $this->artisan('observations:import-csv', $arguments)->assertSuccessful();
    $this->artisan('observations:import-csv', $arguments)
        ->expectsOutputToContain('Imported 1 observations (1 updated), 0 drawings, 0 skipped.')
        ->assertSuccessful();

    $observation = Observation::query()->sole();
    expect($observation->target_table)->toBe('planets')
        ->and($observation->target_key)->toBe('jupiter')
        ->and($observation->observer_username)->toBe($user->username)
        ->and($observation->has_drawing)->toBeTrue()
        ->and(Storage::disk('public')->allFiles('observations'))->toHaveCount(1);
});

it('refuses a file whose drawings are not in the folder', function () {
    $user = ObservationImportFixtures::createObserver();
    $folder = importCsvCommandFolder();
    unlink($folder.'/Jupiter drawing.png');

    $this->artisan('observations:import-csv', ['observer' => $user->username, 'file' => $folder.'/observations.csv', '--drawings' => $folder, '--apply' => true])
        ->assertFailed();

    expect(Observation::query()->count())->toBe(0);
});
