<?php

use App\Models\DeepskyObject;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\InstrumentSet;
use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\User;
use App\Services\ContrastReserveCalculator;
use App\Services\FieldViews\FieldViewBuilder;
use App\Services\FilterAdvice;
use Database\Factories\EyepieceFactory;
use Database\Factories\FilterFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function filterAdviceObject(string $name, string $type, float $mag, float $diam): DeepskyObject
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'VUL',
        'ra'            => 19.9934, 'decl' => 22.7212, 'mag' => $mag, 'subr' => 0.0, 'pa' => 999,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $diam, 'diam2' => $diam,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);

    return DeepskyObject::where('name', $name)->firstOrFail();
}

/**
 * An observer with an 8" Dobsonian, an eyepiece, a location with a bright
 * sky and an O-III, a UHC and a Moon filter.
 */
function filterAdviceObserver(): User
{
    $user      = User::factory()->create();
    $telescope = InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => 'Dobson', 'aperture_mm' => 203, 'focal_length_mm' => 1200,
        'instrument_type_id' => InstrumentType::firstOrCreate(['name' => 'Reflector'])->id,
        'mount_type_id'      => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
    ]);
    EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'focal_length_mm' => 25, 'apparentFOV' => 52]);
    $location = LocationFactory::new()->create([
        'user_id'           => $user->id, 'observer' => $user->username, 'latitude' => 50.8, 'longitude' => 4.7, 'timezone' => 'Europe/Brussels',
        'limitingMagnitude' => 5.0, 'skyBackground' => -999,
    ]);
    foreach (['Lumicon OIII' => 3, 'Astronomik UHC' => 2, 'Orion maanfilter' => 2] as $name => $type) {
        FilterFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => $name, 'type_id' => $type, 'slug' => Str::slug($name)]);
    }
    $user->forceFill(['stdtelescope' => $telescope->id, 'stdlocation' => $location->id])->save();

    return $user->fresh();
}

it('recognises the passband of a filter from its name before its type', function (string $name, int $type, ?string $passband) {
    expect(FilterAdvice::passband(new Filter(['name' => $name, 'type_id' => $type])))->toBe($passband);
})->with([
    'UHC stored as broadband'    => ['Lumicon UHC', 2, 'uhc'],
    'O-III stored as narrowband' => ['Baader O-III', 3, 'oiii'],
    'H-beta stored as other'     => ['H-ß (Astronomik)', 1, 'hbeta'],
    'light pollution filter'     => ['Celestron LPR', 1, 'broadband'],
    'Moon filter as broadband'   => ['Orion maanfilter', 2, null],
    'comet filter'               => ['Lumicon Swan Band Kometenfilter', 1, null],
    'unknown name, O-III type'   => ['My nebula filter', 4, 'oiii'],
    'colour filter'              => ['Wratten 80A', 7, null],
]);

it('gives a spectrum only to emission nebulae', function () {
    expect(FilterAdvice::spectrum('GALXY'))->toBeNull()
        ->and(FilterAdvice::spectrum('OPNCL'))->toBeNull()
        ->and(FilterAdvice::spectrum('PLNNB')['oiii'])->toBeGreaterThan(FilterAdvice::spectrum('PLNNB')['hbeta'])
        ->and(FilterAdvice::spectrum('HII', 'IC 5146')['hbeta'])->toBeGreaterThan(FilterAdvice::spectrum('HII', 'IC 5146')['oiii']);
});

/**
 * An 8" f/6 telescope with eyepieces of 40 to 6 mm (30x to 200x).
 *
 * @return array{0: Instrument, 1: array<int, list<\App\Models\Eyepiece>>, 2: array<int, float>}
 */
function filterAdviceTelescope(): array
{
    $instrument     = new Instrument(['aperture_mm' => 203, 'focal_length_mm' => 1200, 'fixedMagnification' => 0]);
    $eyepieces      = array_map(fn (int $focal) => new \App\Models\Eyepiece(['name' => $focal.' mm', 'focal_length_mm' => $focal, 'apparentFOV' => 52]), [40, 32, 25, 17, 13, 9, 6]);
    $magnifications = ContrastReserveCalculator::eyepieceMagnifications($instrument, $eyepieces);

    return [$instrument, $magnifications, ContrastReserveCalculator::fields($instrument, $magnifications)];
}

it('finds the O-III filter best for a planetary nebula and the H-beta filter for an H-beta nebula', function () {
    [$instrument, $magnifications, $fields] = filterAdviceTelescope();
    $filters                                = collect(['UHC', 'OIII', 'H-beta'])->map(fn (string $name) => new Filter(['name' => $name, 'type_id' => 1]));

    $planetary = FilterAdvice::advise(ContrastReserveCalculator::target(400, 400, 7.4), 'PLNNB', 'M 27', $filters, $instrument, 19.0, $magnifications, $fields);
    expect(array_column($planetary, 'passband'))->toBe(['oiii', 'uhc', 'hbeta'])
        ->and($planetary[0]['gain'])->toBeGreaterThan(0.3)
        ->and($planetary[2]['gain'])->toBeLessThan(0);

    $hbeta = FilterAdvice::advise(ContrastReserveCalculator::target(600, 600, 9.3), 'HII', 'IC 5146', $filters, $instrument, 19.0, $magnifications, $fields);
    expect($hbeta[0]['passband'])->toBe('hbeta');

    expect(FilterAdvice::advise(ContrastReserveCalculator::target(600, 600, 9.3), 'GALXY', 'NGC 1', $filters, $instrument, 19.0, $magnifications, $fields))->toBe([]);
});

it('advises a lower magnification with a filter', function () {
    [$instrument, $magnifications, $fields] = filterAdviceTelescope();
    $target                                 = ContrastReserveCalculator::target(86, 63, 8.8); // M 57
    $without                                = ContrastReserveCalculator::bestMagnification($target, $target->calculateSBObj(), 21.3, 203, array_keys($magnifications), $fields);

    $advice = FilterAdvice::advise($target, 'PLNNB', 'M 57', [new Filter(['name' => 'OIII', 'type_id' => 4])], $instrument, 21.3, $magnifications, $fields);
    expect($advice[0]['magnification'])->toBeLessThan($without)
        ->and($advice[0]['exit_pupil'])->toBe(203 / $advice[0]['magnification'])
        ->and(array_map(fn ($eyepiece) => $eyepiece->name, $advice[0]['eyepieces']))->not->toBeEmpty()
        ->and($advice[0]['ideal_exit_pupil'])->toBeGreaterThan(203 / $without)
        ->and($advice[0]['ideal_exit_pupil'])->toBeLessThanOrEqual(ContrastReserveCalculator::EYE_PUPIL_MM);
});

it('gets no brighter sky at an exit pupil larger than the eye pupil', function () {
    $target = ContrastReserveCalculator::target(3600, 3600, 5.0);
    $sbobj  = $target->calculateSBObj();

    // Below 29x the exit pupil of 203 mm exceeds 7 mm: the library alone
    // would keep improving the contrast reserve towards the lowest magnification.
    expect($target->calculateContrastReserve($sbobj, 21.0, 203, 10))->toBeGreaterThan($target->calculateContrastReserve($sbobj, 21.0, 203, 29))
        ->and(ContrastReserveCalculator::contrastAt($target, $sbobj, 21.0, 203, 10))->toBeLessThan(ContrastReserveCalculator::contrastAt($target, $sbobj, 21.0, 203, 29))
        ->and(ContrastReserveCalculator::contrastAt($target, $sbobj, 21.0, 203, 50))->toBe($target->calculateContrastReserve($sbobj, 21.0, 203, 50));
});

it('advises the nebula filters of the observer on an emission nebula', function () {
    $user   = filterAdviceObserver();
    $object = filterAdviceObject('M 27', 'PLNNB', 7.4, 400);
    $target = ContrastReserveCalculator::target($object->diam1, $object->diam2, $object->mag);

    $advice = FilterAdvice::present(FilterAdvice::forObject($target, $object->type, $object->name, Instrument::find($user->stdtelescope), $user->standardLocation, $user));

    // the Moon filter is no nebula filter
    expect(array_column($advice, 'name'))->toBe(['Lumicon OIII', 'Astronomik UHC'])
        ->and($advice[0]['kind'])->toBe(__('O-III'))
        ->and($advice[0]['gain'])->toBeGreaterThan(0)
        ->and($advice[0]['url'])->toContain('/filter/');
});

it('takes the filters of the instrument set when it has any', function () {
    $user = filterAdviceObserver();
    $set  = InstrumentSet::create(['user_id' => $user->id, 'name' => 'Travel', 'active' => 1]);
    $set->filters()->attach(Filter::where('name', 'Astronomik UHC')->value('id'));

    expect(FilterAdvice::filters($user, $set)->pluck('name')->all())->toBe(['Astronomik UHC'])
        ->and(FilterAdvice::filters($user)->pluck('name')->all())->toBe(['Astronomik UHC', 'Lumicon OIII']);
});

it('shows the filters in the telescope view of an emission nebula only', function () {
    $user = filterAdviceObserver();

    $nebula  = app(FieldViewBuilder::class)->build($user, filterAdviceObject('M 27', 'PLNNB', 7.4, 400), ['datetime' => '2026-08-15T23:00']);
    $filters = collect($nebula['views']['telescope']['filters'])->keyBy('name');
    expect($filters->keys()->all())->toContain('Lumicon OIII')
        ->and($filters['Lumicon OIII']['current']['contrast_reserve'])->toBeFloat()
        ->and($filters['Lumicon OIII']['magnification'])->toBe(48)
        ->and($filters['Lumicon OIII']['eyepieces'])->toHaveCount(1);

    $galaxy = app(FieldViewBuilder::class)->build($user, filterAdviceObject('NGC 6946', 'GALXY', 8.8, 690), ['datetime' => '2026-08-15T23:00']);
    expect($galaxy['views']['telescope']['filters'])->toBe([]);
});
