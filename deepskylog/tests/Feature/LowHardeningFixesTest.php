<?php

use App\Livewire\CreateLens;
use App\Livewire\Profile\UpdateUserLanguageInformation;
use App\Models\Lens;
use App\Models\User;
use App\Services\ObservingListFileImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function lensPhoto(): UploadedFile
{
    return UploadedFile::fake()->image('photo.png');
}

it('gives equipment photos of different observers different names', function () {
    // The factor fills in a proposed name: set it first, so the names below stay.
    Storage::fake('public');
    // Slug names collided here: "john" + "doe xyz" and "john-doe" + "xyz".
    $john = User::factory()->create(['slug' => 'john']);
    $johnDoe = User::factory()->create(['slug' => 'john-doe']);

    Livewire::actingAs($john)->test(CreateLens::class)
        ->set('factor', 2)->set('name', 'doe xyz')->set('photo', lensPhoto())
        ->call('save')->assertHasNoErrors();
    Livewire::actingAs($johnDoe)->test(CreateLens::class)
        ->set('factor', 2)->set('name', 'xyz')->set('photo', lensPhoto())
        ->call('save')->assertHasNoErrors();

    $pictures = Lens::pluck('picture');

    expect($pictures->unique())->toHaveCount(2);
    $pictures->each(fn ($p) => Storage::disk('public')->assertExists($p));
});

it('removes the old equipment photo when it is replaced', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(CreateLens::class)
        ->set('factor', 2)->set('name', 'Barlow')->set('photo', lensPhoto())
        ->call('save');
    $lens = Lens::where('name', 'Barlow')->firstOrFail();
    $old = $lens->picture;

    Livewire::actingAs($user)->test(CreateLens::class, ['lens' => $lens])
        ->set('photo', lensPhoto())
        ->call('save')->assertHasNoErrors();

    $new = $lens->fresh()->picture;
    expect($new)->not->toBe($old);
    Storage::disk('public')->assertExists($new);
    Storage::disk('public')->assertMissing($old);
});

it('validates the language settings', function () {
    $user = User::factory()->create(['language' => 'en', 'observationlanguage' => 'en']);

    Livewire::actingAs($user)->test(UpdateUserLanguageInformation::class)
        ->set('language', '../../evil')
        ->set('observationlanguage', '<script>')
        ->call('updateLanguageInformation')
        ->assertHasErrors(['language', 'observationlanguage']);

    expect($user->fresh())
        ->language->toBe('en')
        ->observationlanguage->toBe('en');

    Livewire::actingAs($user)->test(UpdateUserLanguageInformation::class)
        ->set('language', 'nl')
        ->set('observationlanguage', 'it')
        ->call('updateLanguageInformation')
        ->assertHasNoErrors();

    expect($user->fresh())
        ->language->toBe('nl')
        ->observationlanguage->toBe('it');
});

it('keeps an observation language that is no longer offered valid', function () {
    $user = User::factory()->create(['language' => 'en', 'observationlanguage' => 'ru']);

    Livewire::actingAs($user)->test(UpdateUserLanguageInformation::class)
        ->set('language', 'de')
        ->call('updateLanguageInformation')
        ->assertHasNoErrors();

    expect($user->fresh()->language)->toBe('de');
});

it('reads an AstroPlanner file without changing it', function () {
    $path = tempnam(sys_get_temp_dir(), 'apd');
    $db = new PDO('sqlite:'.$path);
    $db->exec('CREATE TABLE Objects (Name TEXT)');
    $db->exec("INSERT INTO Objects VALUES ('M 31'), ('NGC 7000'), ('')");
    $db = null;
    $before = hash_file('sha256', $path);

    $parse = new ReflectionMethod(ObservingListFileImportService::class, 'parseAstroPlanner');
    $names = $parse->invoke(new ObservingListFileImportService, new UploadedFile($path, 'list.apd', null, null, true));

    expect($names)->toBe(['M 31', 'NGC 7000'])
        ->and(hash_file('sha256', $path))->toBe($before);

    unlink($path);
});
