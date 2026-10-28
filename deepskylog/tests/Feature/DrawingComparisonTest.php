<?php

use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\DrawingComparison;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;

function drawingComparisonObject(): void
{
    DB::table('objects')->insert([
        'name' => 'M 51', 'slug' => 'm-51', 'type' => 'GALXY', 'con' => 'CVN',
        'ra' => 13.4980, 'decl' => 47.1953, 'mag' => 8.4, 'subr' => 13.0, 'pa' => 163,
        'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 660.0, 'diam2' => 420.0,
        'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL' => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function drawingComparisonDrawing(User $user, ?int $apertureMm, string $date, bool $drawing = true): Observation
{
    $instrumentId = null;
    if ($apertureMm !== null) {
        $instrumentId = InstrumentFactory::new()->create([
            'user_id' => $user->id, 'observer' => $user->username, 'name' => $apertureMm.' mm Dobson', 'aperture_mm' => $apertureMm,
            'focal_length_mm' => $apertureMm * 5, 'instrument_type_id' => InstrumentType::firstOrCreate(['name' => 'Reflector'])->id,
            'mount_type_id' => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
        ])->id;
    }

    return Observation::query()->create([
        'observer_username' => $user->username, 'observer_user_id' => $user->id,
        'observed_on' => $date, 'observed_time' => '22:00:00', 'language' => 'en', 'description' => 'Spiral arms',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 51', 'target_name' => 'M 51',
        'instrument_id' => $instrumentId, 'has_drawing' => $drawing, 'drawing_filename' => $drawing ? 'drawings/m51-'.$date.'.jpg' : null,
    ]);
}

it('puts apertures in the classes of the usual telescope sizes', function () {
    expect(DrawingComparison::classFor(203))->toBe('8-inch')
        ->and(DrawingComparison::classFor(200))->toBe('8-inch')
        ->and(DrawingComparison::classFor(406))->toBe('14-16')
        ->and(DrawingComparison::classFor(80))->toBe('small')
        ->and(DrawingComparison::classFor(600))->toBe('large')
        ->and(DrawingComparison::classFor(null))->toBe(DrawingComparison::UNKNOWN)
        ->and(DrawingComparison::label('8-inch', true))->toBe('8″')
        ->and(DrawingComparison::label('14-16', false))->toBe('330–429 mm')
        ->and(DrawingComparison::aperture(406, true))->toBe('16″');
});

it('groups the drawings of an object by aperture and compares the smallest with the largest', function () {
    drawingComparisonObject();
    $user = User::factory()->create();
    $small = drawingComparisonDrawing($user, 203, '2024-03-01');
    $medium = drawingComparisonDrawing($user, 254, '2024-03-02');
    $large = drawingComparisonDrawing($user, 406, '2024-03-03');
    drawingComparisonDrawing($user, null, '2024-03-04');
    drawingComparisonDrawing($user, 406, '2024-03-05', false);

    $classes = (new DrawingComparison)->forObject('M 51');
    expect(array_keys($classes))->toBe(['8-inch', '10-12', '14-16', DrawingComparison::UNKNOWN])
        ->and($classes['14-16']->pluck('id')->all())->toBe([$large->id])
        ->and(DrawingComparison::defaultPair($classes))->toBe(['8-inch', '14-16']);

    $this->get('/object/m-51/drawings/compare')
        ->assertOk()
        ->assertSee('m51-2024-03-01.jpg')
        ->assertSee('m51-2024-03-03.jpg')
        ->assertDontSee('m51-2024-03-02.jpg');

    $this->get('/object/m-51/drawings/compare?left=10-12&right=unknown')
        ->assertOk()
        ->assertSee('m51-2024-03-02.jpg')
        ->assertSee('m51-2024-03-04.jpg')
        ->assertDontSee('m51-2024-03-01.jpg');
});

it('shows one drawing per aperture on the object page, the most liked', function () {
    drawingComparisonObject();
    $user = User::factory()->create();
    drawingComparisonDrawing($user, 203, '2024-03-01');
    $liked = drawingComparisonDrawing($user, 203, '2024-02-01');
    drawingComparisonDrawing($user, 406, '2024-03-03');
    DB::table('observation_likes')->insert([
        'user_id' => $user->id, 'observation_type' => 'observation', 'observation_id' => $liked->id,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $comparison = new DrawingComparison;
    $highlights = $comparison->highlights($comparison->forObject('M 51'));
    expect(array_keys($highlights))->toBe(['8-inch', '14-16'])
        ->and($highlights['8-inch']->id)->toBe($liked->id);

    $this->blade('<x-drawing-apertures object-name="M 51" slug="m-51" />')
        ->assertSee('Drawings by aperture')
        ->assertSee('/object/m-51/drawings/compare');
});

it('answers 404 for an unknown object', function () {
    $this->get('/object/nothing-here/drawings/compare')->assertNotFound();
});
