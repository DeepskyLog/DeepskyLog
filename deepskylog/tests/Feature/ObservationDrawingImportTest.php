<?php

use App\Models\Instrument;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\Import\ImportResult;
use App\Services\Observations\ObservationZipBuilder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ObservationImportFixtures as Fixtures;

/*
 * Drawings imported with the observations (#817): from a zip file holding the
 * CSV or OAL file and the drawings (as ObservationZipBuilder makes it), or
 * uploaded next to the file.
 */

beforeEach(function () {
    Storage::fake('public');
    Fixtures::seedTargets();
    $this->observer = Fixtures::createObserver(['username' => 'drawing-owner', 'name' => 'Drawing Owner', 'slug' => 'drawing-owner']);
});

function drawingJpeg(int $red = 200): string
{
    $image = imagecreatetruecolor(8, 8);
    imagefill($image, 0, 0, imagecolorallocate($image, $red, 30, 30));
    ob_start();
    imagejpeg($image);

    return (string) ob_get_clean();
}

function drawnObservation(User $user, string $object, string $date, ?string $drawing = null): Observation
{
    if ($drawing !== null) {
        Storage::disk('public')->put($drawing, drawingJpeg());
    }

    return Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => $date,
        'observed_time' => '21:30:00',
        'location_id' => Location::query()->where('user_id', $user->id)->value('id'),
        'instrument_id' => Instrument::query()->where('user_id', $user->id)->where('instrument_type_id', '<>', 1)->value('id'),
        'language' => 'en',
        'description' => "{$object} seen",
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $object,
        'target_name' => $object,
        'has_drawing' => $drawing !== null,
        'drawing_filename' => $drawing,
    ]);
}

/**
 * @param  array<string, string>  $entries  name => content
 */
function drawingZip(array $entries): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'import') . '.zip';
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    foreach ($entries as $name => $content) {
        $zip->addFromString($name, $content);
    }
    $zip->close();

    return new UploadedFile($path, 'observations.zip', 'application/zip', null, true);
}

function importUpload(User $user, UploadedFile $file, array $drawings = []): ImportResult
{
    $response = test()->actingAs($user)->post(route('observation.import.process'), array_filter([
        'import_file' => $file,
        'drawings' => $drawings,
    ]));
    $response->assertOk();

    return $response->viewData('result');
}

function csvRow(string $object, string $drawing = ''): string
{
    return "{$object};x;12-01-2024;21:30;Example Site;Example Dobson 250;;;;;;;;en;{$object} seen;{$drawing}";
}

it('imports the zip of observations with their drawings into another account', function () {
    $drawn = drawnObservation($this->observer, 'M 31', '2024-01-12', 'observations/m31.jpg');
    drawnObservation($this->observer, 'M 81', '2024-01-12');

    $path = tempnam(sys_get_temp_dir(), 'zip');
    app(ObservationZipBuilder::class)->write($path, ['observer_id' => $this->observer->id], fn () => null);

    $other = Fixtures::createObserver(['username' => 'drawing-importer']);
    $result = importUpload($other, new UploadedFile($path, 'observations-drawing-owner.zip', 'application/zip', null, true));

    expect($result->imported)->toBe(2)
        ->and($result->drawings)->toBe(1)
        ->and($result->unusedDrawings)->toBe([]);

    $imported = Observation::query()->where('observer_user_id', $other->id)->where('target_name', 'M 31')->sole();
    expect($imported->has_drawing)->toBeTrue()
        ->and($imported->drawing_filename)->toStartWith('observations/')
        ->and($imported->drawing_filename)->not->toBe($drawn->drawing_filename)
        ->and(Storage::disk('public')->get($imported->drawing_filename))->toBe(Storage::disk('public')->get('observations/m31.jpg'));

    expect(Observation::query()->where('observer_user_id', $other->id)->where('target_name', 'M 81')->sole()->has_drawing)->toBeFalse();
});

it('keeps the drawing of an observation that is imported again', function () {
    drawnObservation($this->observer, 'M 31', '2024-01-12', 'observations/m31.jpg');

    $path = tempnam(sys_get_temp_dir(), 'zip');
    app(ObservationZipBuilder::class)->write($path, ['observer_id' => $this->observer->id], fn () => null);
    $files = count(Storage::disk('public')->allFiles());

    $result = importUpload($this->observer, new UploadedFile($path, 'observations.zip', 'application/zip', null, true));

    expect($result->imported)->toBe(1)
        ->and($result->drawings)->toBe(0)
        ->and($result->unusedDrawings)->toBe([])
        ->and(Fixtures::messages($result, 'info'))->toContain('Observation 1: The observation already has a drawing, kept it.')
        ->and(Observation::query()->where('observer_user_id', $this->observer->id)->count())->toBe(1)
        ->and(Observation::query()->where('observer_user_id', $this->observer->id)->sole()->drawing_filename)->toBe('observations/m31.jpg')
        ->and(Storage::disk('public')->allFiles())->toHaveCount($files);
});

it('imports the drawings named in a csv file uploaded next to it', function () {
    $csv = UploadedFile::fake()->createWithContent('observations.csv', implode("\n", [
        csvRow('M 31', 'm31.jpg'),
        csvRow('M 81', 'missing.jpg'),
        csvRow('M 45'),
    ]));

    $result = importUpload($this->observer, $csv, [
        UploadedFile::fake()->createWithContent('M31.JPG', drawingJpeg()),
        UploadedFile::fake()->createWithContent('extra.jpg', drawingJpeg(100)),
    ]);

    expect($result->imported)->toBe(3)
        ->and($result->drawings)->toBe(1)
        ->and($result->unusedDrawings)->toBe(['extra.jpg'])
        ->and(Fixtures::messages($result, 'warning'))->toContain("Row 2: Drawing 'missing.jpg' is not in the upload.");

    $observations = Observation::query()->where('observer_user_id', $this->observer->id)->get()->keyBy('target_name');
    expect($observations['M 31']->has_drawing)->toBeTrue()
        ->and(Storage::disk('public')->get($observations['M 31']->drawing_filename))->toBe(drawingJpeg())
        ->and($observations['M 81']->has_drawing)->toBeFalse()
        ->and($observations['M 45']->has_drawing)->toBeFalse();
});

it('reads a csv file and its drawings from a zip file', function () {
    $result = importUpload($this->observer, drawingZip([
        'my observations/list.csv' => csvRow('M 31', 'sketches/m31.jpg'),
        'my observations/sketches/m31.jpg' => drawingJpeg(),
        '__MACOSX/my observations/._list.csv' => 'resource fork',
    ]));

    expect($result->imported)->toBe(1)
        ->and($result->drawings)->toBe(1)
        ->and(Observation::query()->where('observer_user_id', $this->observer->id)->sole()->has_drawing)->toBeTrue();
});

it('stores a drawing under a name of its own, whatever its name in the zip', function () {
    $result = importUpload($this->observer, drawingZip([
        'observations.csv' => csvRow('M 31', '../../evil.jpg') . "\n" . csvRow('M 81', 'fake.jpg'),
        '../../evil.jpg' => drawingJpeg(),
        'fake.jpg' => '<?php echo "no image";',
    ]));

    $observations = Observation::query()->where('observer_user_id', $this->observer->id)->get()->keyBy('target_name');

    expect($result->drawings)->toBe(1)
        ->and($observations['M 31']->drawing_filename)->toMatch('#^observations/[A-Za-z0-9]+\.jpg$#')
        ->and($observations['M 81']->has_drawing)->toBeFalse()
        ->and(Fixtures::messages($result, 'warning'))->toContain("Row 2: Drawing 'fake.jpg' is no JPEG, PNG, GIF or WebP image.")
        ->and(Storage::disk('public')->allFiles())->toBe([$observations['M 31']->drawing_filename]);
});

it('refuses a zip file without exactly one file to import', function (array $entries, string $error) {
    $this->actingAs($this->observer)
        ->from(route('observation.import'))
        ->post(route('observation.import.process'), ['import_file' => drawingZip($entries)])
        ->assertRedirect(route('observation.import'))
        ->assertSessionHasErrors(['import_file' => $error]);
})->with([
    'none' => [['m31.jpg' => 'x'], 'The zip file holds no CSV or OAL file to import.'],
    'two' => [['a.csv' => 'x', 'b.csv' => 'y'], 'The zip file holds several files to import (a.csv, b.csv). Put only one CSV or OAL file in it.'],
]);

it('says how many drawings an OAL file names when none were uploaded', function () {
    drawnObservation($this->observer, 'M 31', '2024-01-12', 'observations/m31.jpg');
    $path = tempnam(sys_get_temp_dir(), 'zip');
    app(ObservationZipBuilder::class)->write($path, ['observer_id' => $this->observer->id], fn () => null);
    $zip = new ZipArchive();
    $zip->open($path);
    $xml = $zip->getFromName('observations.xml');
    $zip->close();

    $other = Fixtures::createObserver(['username' => 'oal-only']);
    $result = importUpload($other, UploadedFile::fake()->createWithContent('observations.xml', $xml));

    expect($result->imported)->toBe(1)
        ->and($result->drawings)->toBe(0)
        ->and(Fixtures::messages($result, 'info'))->toContain('The file names 1 drawings. Upload them with the file, or the file and the drawings in a zip file, to import them.')
        ->and(Observation::query()->where('observer_user_id', $other->id)->sole()->has_drawing)->toBeFalse();
});

it('offers a zip file and drawings on the import form', function () {
    $this->actingAs($this->observer)->get(route('observation.import'))
        ->assertOk()
        ->assertSee('accept=".csv,.txt,.xml,.zip"', false)
        ->assertSee('name="drawings[]"', false);
});
