<?php

use App\Support\UploadedPhoto;
use Illuminate\Support\Facades\Storage;

it('gives the public disk URL of a stored picture', function () {
    Storage::fake('public');
    Storage::disk('public')->put('photos/instruments/a.jpg', 'x');

    expect(UploadedPhoto::url('photos/instruments/a.jpg'))->toBe(asset('storage/photos/instruments/a.jpg'))
        ->and(UploadedPhoto::url('/storage/photos/instruments/a.jpg'))->toBe(asset('storage/photos/instruments/a.jpg'))
        ->and(UploadedPhoto::url('https://example.org/a.jpg'))->toBe('https://example.org/a.jpg');
});

it('gives the default without a picture', function () {
    expect(UploadedPhoto::url(null, '/images/telescope.png'))->toBe(asset('/images/telescope.png'))
        ->and(UploadedPhoto::url('', null))->toBeNull();
});

it('gives the default when the file of the picture is missing', function () {
    Storage::fake('public');

    expect(UploadedPhoto::url('photos/instruments/wim-de-meester-43-145.png', '/images/telescope.png'))->toBe(asset('/images/telescope.png'))
        ->and(UploadedPhoto::url('photos/instruments/wim-de-meester-43-145.png'))->toBeNull();
});
