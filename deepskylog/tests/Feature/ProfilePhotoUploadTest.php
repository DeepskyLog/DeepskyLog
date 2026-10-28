<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

it('stores a new profile photo and deletes the previous one', function () {
    Storage::fake('public');
    Storage::disk('public')->put('profile-photos/old.jpg', 'old');
    $user = User::factory()->create(['profile_photo_path' => 'profile-photos/old.jpg']);

    $user->updateProfilePhoto(UploadedFile::fake()->image('photo.jpg'));

    $path = $user->fresh()->profile_photo_path;
    expect($path)->toStartWith('profile-photos/')->not->toBe('profile-photos/old.jpg');
    Storage::disk('public')->assertExists($path);
    Storage::disk('public')->assertMissing('profile-photos/old.jpg');
});

// The web server could not write to profile-photos/: the disk does not throw,
// so the path of a file that was never written ended up in the user record.
it('keeps the previous profile photo when the new one cannot be written', function () {
    $root = storage_path('framework/testing/readonly-public');
    File::ensureDirectoryExists($root.'/profile-photos');
    File::put($root.'/profile-photos/old.jpg', 'old');
    chmod($root.'/profile-photos', 0555);
    Storage::set('public', Storage::build(['driver' => 'local', 'root' => $root, 'throw' => false]));
    $user = User::factory()->create(['profile_photo_path' => 'profile-photos/old.jpg']);

    try {
        expect(fn () => $user->updateProfilePhoto(UploadedFile::fake()->image('photo.jpg')))
            ->toThrow(ValidationException::class);

        expect($user->fresh()->profile_photo_path)->toBe('profile-photos/old.jpg');
        expect(File::exists($root.'/profile-photos/old.jpg'))->toBeTrue();
    } finally {
        chmod($root.'/profile-photos', 0755);
        File::deleteDirectory($root);
    }
})->skip(fn () => function_exists('posix_getuid') && posix_getuid() === 0, 'root can write to a read-only directory');
