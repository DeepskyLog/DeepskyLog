<?php

use App\Livewire\CreateLens;
use App\Models\Lens;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// The public disk is served by Apache, so a stored photo must never keep an
// extension of the client (.fcgi, .php, .html) that the server would run or
// render: the extension comes from the content.
it('stores an equipment photo with the extension of its content', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $png = UploadedFile::fake()->image('photo.png')->get();
    // The fake reports the MIME type of its name; on the server it is read from
    // the content, which is what mimeType() stands in for.

    Livewire::actingAs($user)
        ->test(CreateLens::class)
        ->set('name', 'Barlow')
        ->set('factor', 2)
        ->set('photo', UploadedFile::fake()->createWithContent('photo.fcgi', $png)->mimeType('image/png'))
        ->call('save')
        ->assertHasNoErrors();

    $picture = Lens::where('name', 'Barlow')->value('picture');

    expect($picture)->toEndWith('.png');
    Storage::disk('public')->assertExists($picture);
    expect(Storage::disk('public')->allFiles())->each->toEndWith('.png');
});

it('does not store an equipment photo that is not an image', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateLens::class)
        ->set('name', 'Barlow')
        ->set('factor', 2)
        ->set('photo', UploadedFile::fake()->createWithContent('photo.png', '<script>alert(1)</script>')->mimeType('text/html'))
        ->call('save');

    expect(Storage::disk('public')->allFiles())->toBe([])
        ->and(Lens::where('name', 'Barlow')->value('picture'))->toBeNull();
});
