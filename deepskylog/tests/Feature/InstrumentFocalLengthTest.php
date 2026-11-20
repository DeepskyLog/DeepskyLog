<?php

use App\Models\Instrument;
use App\Models\User;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;

// The old site stored 1 mm (and the form 0 mm) as the focal length of
// binoculars, finders and the naked eye: an unknown focal length is null.

it('reads and stores a placeholder focal length as unknown', function () {
    $user = User::factory()->create();
    $binoculars = InstrumentFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'name' => '10x50',
        'aperture_mm' => 50, 'focal_length_mm' => 0, 'fixedMagnification' => 10,
    ]);

    expect(DB::table('instruments')->where('id', $binoculars->id)->value('focal_length_mm'))->toBeNull();

    // As the old site still writes it.
    DB::table('instruments')->where('id', $binoculars->id)->update(['focal_length_mm' => 1]);

    expect(Instrument::find($binoculars->id)->focal_length_mm)->toBeNull()
        ->and(Instrument::find($binoculars->id)->toArray()['focal_length_mm'])->toBeNull();
});

it('keeps a real focal length', function () {
    $user = User::factory()->create();
    $telescope = InstrumentFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'name' => 'Dobson',
        'aperture_mm' => 200, 'focal_length_mm' => 1200, 'fixedMagnification' => null,
    ]);

    expect(Instrument::find($telescope->id)->focal_length_mm)->toBe(1200.0);
});

it('clears the placeholder focal lengths of the old site', function () {
    $user = User::factory()->create();
    $binoculars = InstrumentFactory::new()->create([
        'user_id' => $user->id, 'observer' => $user->username, 'name' => '20x80',
        'aperture_mm' => 80, 'fixedMagnification' => 20,
    ]);
    DB::table('instruments')->where('id', $binoculars->id)->update(['focal_length_mm' => 1]);

    (require database_path('migrations/2026_11_20_000010_clear_placeholder_focal_lengths_of_instruments.php'))->up();

    expect(DB::table('instruments')->where('id', $binoculars->id)->value('focal_length_mm'))->toBeNull();
});
