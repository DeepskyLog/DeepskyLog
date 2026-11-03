<?php

use App\Support\UploadedPhoto;

it('gives the public disk URL of a stored picture', function () {
    expect(UploadedPhoto::url('photos/instruments/a.jpg'))->toBe(asset('storage/photos/instruments/a.jpg'))
        ->and(UploadedPhoto::url('/storage/photos/instruments/a.jpg'))->toBe(asset('storage/photos/instruments/a.jpg'))
        ->and(UploadedPhoto::url('https://example.org/a.jpg'))->toBe('https://example.org/a.jpg');
});

it('gives the default without a picture', function () {
    expect(UploadedPhoto::url(null, '/images/telescope.png'))->toBe(asset('/images/telescope.png'))
        ->and(UploadedPhoto::url('', null))->toBeNull();
});
