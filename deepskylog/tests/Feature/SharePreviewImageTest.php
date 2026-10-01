<?php

use App\Services\SharePreviewImage;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
});

function sharePreviewTestImage(string $path, int $width, int $height): void
{
    $image = imagecreatetruecolor($width, $height);
    // Noise, so the JPEG is large, as a scanned sketch is.
    for ($y = 0; $y < $height; $y += 2) {
        for ($x = 0; $x < $width; $x += 2) {
            imagesetpixel($image, $x, $y, random_int(0, 0xFFFFFF));
        }
    }
    ob_start();
    imagejpeg($image, null, 95);
    Storage::disk('public')->put($path, ob_get_clean());
    imagedestroy($image);
}

it('shares a large sketch as a preview of at most 1200 pixels and 300 KB', function () {
    sharePreviewTestImage('sketches/big.jpg', 2400, 1800);
    expect(Storage::disk('public')->size('sketches/big.jpg'))->toBeGreaterThan(SharePreviewImage::MAX_BYTES);

    $preview = SharePreviewImage::for(Storage::disk('public')->url('sketches/big.jpg'));

    expect($preview)->toBe([
        'url'    => route('share-preview', ['path' => 'storage/sketches/big.jpg']),
        'width'  => 1200,
        'height' => 900,
    ]);

    $response = $this->get($preview['url'])->assertOk()->assertHeader('Content-Type', 'image/jpeg');
    $jpeg     = file_get_contents($response->baseResponse->getFile()->getPathname());
    $size     = getimagesizefromstring($jpeg);

    expect(strlen($jpeg))->toBeLessThanOrEqual(SharePreviewImage::MAX_BYTES)
        ->and(max($size[0], $size[1]))->toBeLessThanOrEqual(SharePreviewImage::MAX_SIDE);
});

it('shares a small sketch as it is', function () {
    sharePreviewTestImage('sketches/small.jpg', 400, 300);

    expect(SharePreviewImage::for('/storage/sketches/small.jpg'))
        ->toBe(['url' => url('storage/sketches/small.jpg'), 'width' => 400, 'height' => 300]);
});

it('remakes the preview when the sketch changes', function () {
    sharePreviewTestImage('sketches/big.jpg', 2400, 1800);
    $first = SharePreviewImage::render('storage/sketches/big.jpg');
    touch($first, time() - 3600);

    sharePreviewTestImage('sketches/big.jpg', 1800, 2400);
    $second = SharePreviewImage::render('storage/sketches/big.jpg');

    expect(getimagesize($second)[1])->toBe(1200);
});

it('makes no preview of files outside the sketch and session pictures', function (string $path) {
    $this->get('/share-preview/' . $path)->assertNotFound();
})->with([
    'climbing out'      => 'images/drawings/../../index.php',
    'no image'          => 'storage/notes.txt',
    'another directory' => 'images/logo2.png',
    'missing'           => 'images/drawings/does-not-exist.jpg',
]);

it('leaves images of other sites alone', function () {
    expect(SharePreviewImage::for('https://example.com/sketch.jpg'))
        ->toBe(['url' => 'https://example.com/sketch.jpg', 'width' => null, 'height' => null]);
});

it('gives the observation page the preview of its sketch as og:image', function () {
    sharePreviewTestImage('sketches/m65.jpg', 2400, 1800);
    $owner = \App\Models\User::factory()->create();
    \App\Models\Observation::create([
        'observer_username' => $owner->username,
        'observer_user_id'  => $owner->id,
        'observed_on'       => '2026-03-01',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => 'M 65',
        'target_name'       => 'M 65',
        'description'       => 'Seen',
        'has_drawing'       => true,
        'drawing_filename'  => 'sketches/m65.jpg',
    ]);

    $this->get(route('observations.detail', ['observer' => $owner->slug, 'date' => '2026-03-01', 'object' => 'm-65']))
        ->assertOk()
        ->assertSee('<meta property="og:image" content="' . route('share-preview', ['path' => 'storage/sketches/m65.jpg']) . '" />', false)
        ->assertSee('<meta property="og:image:width" content="1200" />', false)
        ->assertSee('<meta name="twitter:image" content="' . route('share-preview', ['path' => 'storage/sketches/m65.jpg']) . '" />', false);
});
