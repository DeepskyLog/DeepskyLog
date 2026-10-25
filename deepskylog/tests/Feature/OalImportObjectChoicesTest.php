<?php

use App\Http\Controllers\ObservationImportController;
use App\Models\Observation;
use App\Services\Import\OalObservationImporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\ObservationImportFixtures as Fixtures;

/*
 * The targets of an OAL file that are not in the database (#221): the observer
 * chooses for each whether it is an object DeepskyLog has, a new object, or
 * not to import it, before anything is imported. The upload is kept meanwhile.
 */

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
    Fixtures::seedTargets();
    $this->user = Fixtures::createObserver(['username' => 'choosing-observer', 'slug' => 'choosing-observer']);
});

function choicesUpload(?string $content = null): UploadedFile
{
    return UploadedFile::fake()->createWithContent('new-targets.xml', $content ?? Fixtures::content('oal/valid-new-targets.xml'));
}

/** Upload the file with new targets and return the token of the kept upload. */
function uploadNewTargets(array $extra = [], ?string $content = null): string
{
    $response = test()->actingAs(test()->user)->post(route('observation.import.process'), ['import_file' => choicesUpload($content)] + $extra);
    $response->assertRedirect();

    return Str::of($response->headers->get('Location'))->match('#/observation/import/([A-Za-z0-9]{40})/objects#')->toString();
}

function exampleTargets(): int
{
    return DB::table('objects')->where('name', 'like', 'Example%')->count();
}

it('lists the new targets with the objects near them and suggests the same object', function () {
    $targets = (new OalObservationImporter($this->user))->newTargets(Fixtures::content('oal/valid-new-targets.xml'));

    // The target found through its alias M31 is not new.
    expect($targets)->toHaveCount(1);
    expect($targets[0])->toMatchArray([
        'name' => 'Example Nebula 123',
        'type' => 'BRTNB',
        'observations' => 2,
        'diameter' => 10.0,
        'suggestion' => 'M 42',
    ]);
    expect(array_column($targets[0]['nearby'], 'name'))->toBe(['M 42'])
        ->and($targets[0]['nearby'][0]['separation'])->toBeLessThan(0.5);
});

it('suggests no object when the nearby object is of another type', function () {
    DB::table('objects')->where('name', 'M 42')->update(['type' => 'OPNCL']);

    $targets = (new OalObservationImporter($this->user))->newTargets(Fixtures::content('oal/valid-new-targets.xml'));

    expect($targets[0]['suggestion'])->toBeNull()
        ->and(array_column($targets[0]['nearby'], 'name'))->toBe(['M 42']);
});

it('imports nothing before the observer has chosen, and keeps the upload', function () {
    $token = uploadNewTargets();

    expect(Observation::query()->count())->toBe(0)
        ->and(exampleTargets())->toBe(0)
        ->and(Storage::disk('local')->exists(ObservationImportController::DIRECTORY . "/{$token}/import"))->toBeTrue();

    $this->actingAs($this->user)->get(route('observation.import.objects', $token))
        ->assertOk()
        ->assertSee('Example Nebula 123')
        ->assertSee('value="object:M 42"', false)
        ->assertSee('probably the same object');
});

it('imports the observations of a new target as the nearby object chosen', function () {
    $token = uploadNewTargets();

    $response = $this->actingAs($this->user)->post(route('observation.import.objects.store', $token), ['choice' => ['object:M 42']]);
    $result = $response->assertOk()->viewData('result');

    expect($result->imported)->toBe(3)
        ->and($result->newObjects)->toBe([])
        ->and(exampleTargets())->toBe(0)
        ->and(Observation::query()->orderBy('id')->pluck('target_name')->all())->toBe(['M 42', 'M 42', 'M 31'])
        ->and(Storage::disk('local')->exists(ObservationImportController::DIRECTORY . "/{$token}"))->toBeFalse();
});

it('imports the observations of a new target as another object named by the observer', function () {
    $token = uploadNewTargets();

    $this->actingAs($this->user)->post(route('observation.import.objects.store', $token), ['choice' => ['other'], 'other' => ['m42']])
        ->assertOk();

    expect(Observation::query()->where('target_name', 'M 42')->count())->toBe(2)
        ->and(exampleTargets())->toBe(0);
});

it('asks again when the other object is not in the database', function () {
    $token = uploadNewTargets();

    $this->actingAs($this->user)
        ->from(route('observation.import.objects', $token))
        ->post(route('observation.import.objects.store', $token), ['choice' => ['other'], 'other' => ['NGC 99999']])
        ->assertRedirect(route('observation.import.objects', $token))
        ->assertSessionHasErrors('other.0');

    expect(Observation::query()->count())->toBe(0)
        ->and(Storage::disk('local')->exists(ObservationImportController::DIRECTORY . "/{$token}/import"))->toBeTrue();
});

it('adds a new target as a new object when chosen', function () {
    $token = uploadNewTargets();

    $result = $this->actingAs($this->user)->post(route('observation.import.objects.store', $token), ['choice' => ['new']])
        ->assertOk()->viewData('result');

    expect($result->newObjects)->toBe(['Example Nebula 123' => 'example-nebula-123'])
        ->and($result->imported)->toBe(3)
        ->and(exampleTargets())->toBe(1);
});

it('skips the observations of a new target when chosen', function () {
    $token = uploadNewTargets();

    $result = $this->actingAs($this->user)->post(route('observation.import.objects.store', $token), ['choice' => ['skip']])
        ->assertOk()->viewData('result');

    expect($result->imported)->toBe(1)
        ->and($result->skipped)->toBe(2)
        ->and($result->errors)->toBe(0)
        ->and(exampleTargets())->toBe(0)
        ->and(Observation::query()->pluck('target_name')->all())->toBe(['M 31']);
});

it('skips a new target nothing was chosen for', function () {
    $result = (new OalObservationImporter($this->user))
        ->withObjectChoices([])
        ->import(Fixtures::content('oal/valid-new-targets.xml'));

    expect($result->imported)->toBe(1)
        ->and($result->skipped)->toBe(2)
        ->and(exampleTargets())->toBe(0)
        ->and(implode("\n", Fixtures::messages($result, 'error')))->toContain("Target 'Example Nebula 123' is not in the DeepskyLog database and nothing was chosen for it");
});

it('keeps the drawings uploaded with the file until the import', function () {
    $content = str_replace(
        "<result lang=\"en\"><description>A faint nebula next to M 42.</description></result>\n",
        "<result lang=\"en\"><description>A faint nebula next to M 42.</description></result>\n    <image>nebula.jpg</image>\n",
        Fixtures::content('oal/valid-new-targets.xml'),
    );
    $image = imagecreatetruecolor(8, 8);
    ob_start();
    imagejpeg($image);
    $jpeg = (string) ob_get_clean();

    $token = uploadNewTargets(['drawings' => [UploadedFile::fake()->createWithContent('nebula.jpg', $jpeg)]], $content);

    $result = $this->actingAs($this->user)->post(route('observation.import.objects.store', $token), ['choice' => ['object:M 42']])
        ->assertOk()->viewData('result');

    $drawn = Observation::query()->where('has_drawing', true)->sole();
    expect($result->drawings)->toBe(1)
        ->and($drawn->target_name)->toBe('M 42')
        ->and(Storage::disk('public')->get($drawn->drawing_filename))->toBe($jpeg);
});

it('imports a file without new targets at once', function () {
    $this->actingAs($this->user)
        ->post(route('observation.import.process'), ['import_file' => choicesUpload(Fixtures::content('oal/valid-observations.xml'))])
        ->assertOk()
        ->assertViewIs('observations.import-result');

    expect(Storage::disk('local')->directories(ObservationImportController::DIRECTORY))->toBe([]);
});

it('never adds objects from a CSV file', function () {
    $this->actingAs($this->user)
        ->post(route('observation.import.process'), [
            'import_file' => UploadedFile::fake()->createWithContent('unknown.csv', Fixtures::content('csv/invalid-unknown-object.csv')),
        ])
        ->assertOk()
        ->assertViewIs('observations.import-result');

    expect(DB::table('objects')->where('name', 'like', 'Example%')->exists())->toBeFalse();
});

it('shows a kept upload only to its observer', function () {
    $token = uploadNewTargets();
    $other = Fixtures::createObserver(['username' => 'other-observer']);

    $this->actingAs($other)->get(route('observation.import.objects', $token))->assertNotFound();
    $this->actingAs($other)->post(route('observation.import.objects.store', $token), ['choice' => ['new']])->assertNotFound();
    $this->actingAs($other)->delete(route('observation.import.cancel', $token))->assertNotFound();

    expect(exampleTargets())->toBe(0);
});

it('throws a kept upload away when the import is cancelled', function () {
    $token = uploadNewTargets();

    $this->actingAs($this->user)->delete(route('observation.import.cancel', $token))
        ->assertRedirect(route('observation.import'));

    expect(Storage::disk('local')->exists(ObservationImportController::DIRECTORY . "/{$token}"))->toBeFalse();
    $this->actingAs($this->user)->get(route('observation.import.objects', $token))->assertNotFound();
});

it('removes uploads kept longer than a day', function () {
    $old = uploadNewTargets();
    touch(Storage::disk('local')->path(ObservationImportController::DIRECTORY . "/{$old}"), now()->subHours(ObservationImportController::KEEP_HOURS + 1)->getTimestamp());

    $new = uploadNewTargets();

    expect(Storage::disk('local')->exists(ObservationImportController::DIRECTORY . "/{$old}"))->toBeFalse()
        ->and(Storage::disk('local')->exists(ObservationImportController::DIRECTORY . "/{$new}/import"))->toBeTrue();
});

it('keeps a zip file until the import', function () {
    $path = tempnam(sys_get_temp_dir(), 'import') . '.zip';
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('observations.xml', Fixtures::content('oal/valid-new-targets.xml'));
    $zip->close();

    $response = $this->actingAs($this->user)->post(route('observation.import.process'), [
        'import_file' => new UploadedFile($path, 'observations.zip', 'application/zip', null, true),
    ]);
    $token = Str::of($response->headers->get('Location'))->match('#/observation/import/([A-Za-z0-9]{40})/objects#')->toString();

    $this->actingAs($this->user)->get(route('observation.import.objects', $token))->assertOk()->assertSee('observations.zip');

    $result = $this->actingAs($this->user)->post(route('observation.import.objects.store', $token), ['choice' => ['object:M 42']])
        ->assertOk()->viewData('result');

    expect($result->imported)->toBe(3)
        ->and(exampleTargets())->toBe(0);
});
