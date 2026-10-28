<?php

use App\Jobs\ComputeContrastReserveForObject;
use App\Jobs\ComputeContrastReserveForObjectsBatch;
use App\Livewire\AdvancedObjectSearch;
use App\Livewire\AdvancedObjectSearchTable;
use App\Models\DeepskyObject;
use App\Models\Instrument;
use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\SearchIndex;
use App\Models\User;
use App\Services\ContrastReserveCalculator;
use App\Services\DoubleStarResolvability;
use App\Services\FieldViews\FieldViewBuilder;
use App\Services\TonightsBest;
use App\Services\WashingtonDoubleStars;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;

// Whether a telescope splits a double star (App\Services\DoubleStarResolvability).

/**
 * A double star: the separation in diam1 (arcseconds), the companion's
 * magnitude in subr.
 */
function doubleStar(string $name, float $separation, float $primary, float $companion = 99.9, float $decl = 40.0, float $ra = 18.7): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => 'DS', 'con' => 'LYR',
        'ra'            => $ra, 'decl' => $decl, 'mag' => $primary, 'subr' => $companion, 'pa' => 100,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $separation, 'diam2' => 0,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    DB::table('search_index')->insert([
        'name'        => $name, 'name_normalized' => mb_strtolower($name), 'source_table' => 'objects', 'source_pk' => Str::slug($name),
        'display_name' => $name, 'source_type' => 'DS', 'ra' => $ra, 'decl' => $decl, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

/**
 * An observer with a 120 mm refractor (focal length 900 mm), eyepieces of
 * 25 and 6 mm (36x and 150x) and a location with a limiting magnitude of 6.
 */
function doubleStarObserver(): User
{
    $user      = User::factory()->create();
    $refractor = InstrumentType::firstOrCreate(['name' => 'Refractor']);
    $altaz     = MountType::firstOrCreate(['name' => 'Alt/Azimuth']);
    $telescope = InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => 'Refractor', 'aperture_mm' => 120, 'focal_length_mm' => 900,
        'obstruction_perc'   => 0, 'instrument_type_id' => $refractor->id, 'mount_type_id' => $altaz->id,
    ]);
    foreach ([25, 6] as $focalLength) {
        EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'focal_length_mm' => $focalLength, 'apparentFOV' => 52, 'active' => true]);
    }
    $location = LocationFactory::new()->create([
        'user_id'           => $user->id, 'observer' => $user->username, 'latitude' => 50.8, 'longitude' => 4.7, 'timezone' => 'Europe/Brussels',
        'limitingMagnitude' => 6.0, 'skyBackground' => -999,
    ]);
    $user->forceFill(['stdtelescope' => $telescope->id, 'stdlocation' => $location->id])->save();

    return $user->fresh();
}

function doubleStarResults(array $filters): array
{
    SearchIndex::markUniqueNames();
    $table = new AdvancedObjectSearchTable();
    $table->mount($filters);

    return $table->datasource()->orderBy('search_index.name')->pluck('search_index.name')->all();
}

it('splits an equal pair down to the Dawes limit and needs a wider separation for a fainter companion', function () {
    expect(DoubleStarResolvability::dawes(116.0))->toBe(1.0)
        ->and(DoubleStarResolvability::limit(116.0, null))->toBe(1.0)
        ->and(DoubleStarResolvability::limit(116.0, 1.0))->toBe(1.0)
        ->and(round(DoubleStarResolvability::limit(116.0, 3.0), 3))->toBe(round(sqrt(10), 3))
        ->and(round(DoubleStarResolvability::limit(116.0, 5.0), 6))->toBe(10.0)
        // The pair has to appear 4′ apart: 240 / separation
        ->and(DoubleStarResolvability::minimumMagnification(2.0))->toBe(120)
        ->and(DoubleStarResolvability::minimumMagnification(0.7))->toBe(343);
});

it('gives a verdict from the separation against the limit and the brightness of the companion', function () {
    $pair = fn (float $separation, ?float $primary = 6.0, ?float $companion = 6.5) => ['separation' => $separation, 'pa' => 10, 'primary' => $primary, 'companion' => $companion];

    // 116 mm: a limit of 1″ for these nearly equal stars
    expect(DoubleStarResolvability::assess($pair(3.0), 116.0)['verdict'])->toBe('easy')
        ->and(DoubleStarResolvability::assess($pair(1.5), 116.0)['verdict'])->toBe('split')
        ->and(DoubleStarResolvability::assess($pair(1.1), 116.0)['verdict'])->toBe('difficult')
        ->and(DoubleStarResolvability::assess($pair(0.9), 116.0)['verdict'])->toBe('not_split')
        // Below 1.5″ only on a steady night
        ->and(DoubleStarResolvability::assess($pair(1.1), 116.0)['steady_seeing'])->toBeTrue()
        ->and(DoubleStarResolvability::assess($pair(3.0), 116.0)['steady_seeing'])->toBeFalse()
        // A companion 4 magnitudes fainter needs 10^(3/4) = 5.6″
        ->and(DoubleStarResolvability::assess($pair(5.0, 6.0, 10.0), 116.0)['verdict'])->toBe('not_split')
        ->and(DoubleStarResolvability::assess($pair(6.0, 6.0, 10.0), 116.0)['verdict'])->toBe('difficult')
        ->and(DoubleStarResolvability::assess($pair(7.5, 6.0, 10.0), 116.0)['verdict'])->toBe('split')
        // An unknown companion counts as an equal pair
        ->and(DoubleStarResolvability::assess($pair(1.5, 6.0, null), 116.0)['delta_mag'])->toBeNull()
        ->and(DoubleStarResolvability::assess($pair(1.5, 6.0, null), 116.0)['limit'])->toBe(1.0);

    // Wide enough, but the companion is fainter than LM − 2.4 + 1.6 log(ρ / R0)
    $faint = DoubleStarResolvability::assess($pair(20.0, 8.0, 13.0), 116.0, 13.0);
    expect(round($faint['companion_limit'], 2))->toBe(round(13.0 - 2.4 + 1.6 * log10(20.0), 2))
        ->and($faint['verdict'])->toBe('companion_too_faint')
        ->and(DoubleStarResolvability::assess($pair(20.0, 8.0, 13.0), 116.0, 14.5)['verdict'])->toBe('easy');
});

it('reads only double stars with a separation, and 99.9 as an unknown magnitude', function () {
    doubleStar('STF 1', 9.0, 8.0);
    doubleStar('STT 1', 2.0, 7.5, 9.5);
    doubleStar('STF 0', 0.0, 7.0);

    expect(DoubleStarResolvability::pair(DeepskyObject::where('name', 'STF 1')->first()))
        ->toBe(['separation' => 9.0, 'pa' => 100, 'primary' => 8.0, 'companion' => null])
        ->and(DoubleStarResolvability::pair(DeepskyObject::where('name', 'STT 1')->first())['companion'])->toBe(9.5)
        ->and(DoubleStarResolvability::pair(DeepskyObject::where('name', 'STF 0')->first()))->toBeNull()
        ->and(DoubleStarResolvability::pair((object) ['type' => 'GALXY', 'diam1' => 60.0]))->toBeNull();
});

it('chooses the lowest of the observer\'s magnifications that shows the pair apart', function () {
    $user = doubleStarObserver();
    doubleStar('STF 1', 9.0, 8.0);
    doubleStar('STT 1', 2.0, 7.5, 9.5);
    doubleStar('BU 1', 0.8, 7.0, 7.2);

    $for = fn (string $name) => DoubleStarResolvability::forObject(DeepskyObject::where('name', $name)->first(), $user->standardInstrument, $user->standardLocation, $user);

    // 240 / 9 = 27x: the 25 mm eyepiece (36x)
    $wide = $for('STF 1');
    expect($wide['verdict'])->toBe('easy')
        ->and($wide['magnification'])->toBe(36)
        ->and($wide['magnification_too_low'])->toBeFalse()
        ->and($wide['eyepieces'])->toHaveCount(1)
        ->and($wide['companion_limit'])->toBeFloat();

    // 240 / 2 = 120x: the 6 mm eyepiece (150x); limit 116 / 120 × 10^(1/4) = 1.72″
    $close = $for('STT 1');
    expect($close['verdict'])->toBe('difficult')
        ->and($close['magnification'])->toBe(150)
        ->and(round($close['limit'], 2))->toBe(1.72);

    // 0.8″ needs 300x, more than the 150x of the observer, and 120 mm (0.97″) does not split it
    $tight = $for('BU 1');
    expect($tight['verdict'])->toBe('not_split')
        ->and($tight['magnification'])->toBe(150)
        ->and($tight['magnification_too_low'])->toBeTrue();

    // A pair that never rises at the location
    doubleStar('S 1', 20.0, 5.0, 6.0, -60.0);
    expect($for('S 1')['not_visible'])->toBeTrue()
        ->and($for('S 1')['companion_limit'])->toBeNull();

    $presented = DoubleStarResolvability::present($close);
    expect($presented['label'])->toBe('Just split, at the limit of the telescope')
        ->and($presented['instrument'])->toContain('Refractor');
});

it('says in the finder and telescope views whether the pair is split at their magnification', function () {
    $user = doubleStarObserver();
    doubleStar('STT 1', 2.0, 7.5, 9.5);
    $object = DeepskyObject::where('name', 'STT 1')->first();

    $eyepiece = $user->eyepieces()->where('focal_length_mm', 25)->first();
    $views = app(FieldViewBuilder::class)->build($user, $object, ['datetime' => '2026-07-01T23:30', 'eyepiece' => $eyepiece->id])['views'];
    // 36x is too low for a 2″ pair
    $double = $views['telescope']['double'];
    expect($double['verdict'])->toBe('difficult')
        ->and($double['magnification_too_low'])->toBeTrue()
        ->and($double['minimum_magnification'])->toBe(120);

    $galaxy = (object) ['type' => 'GALXY', 'diam1' => 60.0];
    expect(DoubleStarResolvability::atMagnification($galaxy, 120.0, 100, 12.0))->toBeNull();
});

it('finds the double stars the standard telescope splits, or not yet', function () {
    $user = doubleStarObserver();
    doubleStar('STF 1', 9.0, 8.0);
    doubleStar('STT 1', 2.0, 7.5, 9.5);
    doubleStar('BU 1', 0.5, 7.0, 7.2);
    // Wide, but the companion is far too faint for 120 mm
    doubleStar('HJ 1', 10.0, 8.0, 15.5);

    $this->actingAs($user);
    expect(doubleStarResults(['double_stars' => 'split']))->toBe(['STF 1', 'STT 1'])
        ->and(doubleStarResults(['double_stars' => 'not_split']))->toBe(['BU 1', 'HJ 1']);

    // Without a standard telescope nothing is found
    $this->actingAs(User::factory()->create());
    expect(doubleStarResults(['double_stars' => 'split']))->toBe([]);
});

it('offers the double star filter in the search form to observers', function () {
    $user = doubleStarObserver();

    Livewire::actingAs($user)->test(AdvancedObjectSearch::class)
        ->call('addFilter', 'doubleStars')
        ->set('doubleStars', 'not_split')
        ->call('search')
        ->assertRedirect(route('search.advanced.results', ['double_stars' => 'not_split']));
});

it('gives a double star no contrast reserve', function () {
    $user = doubleStarObserver();
    doubleStar('STF 1', 9.0, 8.0);

    $values = ComputeContrastReserveForObject::valuesFor(DeepskyObject::where('name', 'STF 1')->first(), $user, $user->standardInstrument, $user->standardLocation, null);
    expect(ContrastReserveCalculator::appliesTo('DS'))->toBeFalse()
        ->and(ContrastReserveCalculator::appliesTo('GALXY'))->toBeTrue()
        ->and($values['contrast_reserve'])->toBeNull()
        ->and($values['optimum_detection_magnification'])->toBeNull();

    // Nor in the field views
    $views = app(FieldViewBuilder::class)->build($user, DeepskyObject::where('name', 'STF 1')->first(), ['datetime' => '2026-07-01T23:30'])['views'];
    expect($views['telescope']['contrast'])->toBeNull()
        ->and($views['telescope']['double']['verdict'])->toBe('easy');
});

it('proposes the chosen double stars the telescope splits for a night', function () {
    Queue::fake();
    $user = doubleStarObserver();
    // Well placed in the evening of November at 51° N
    doubleStar('STF 1', 9.0, 8.0, 9.0, 37.0, 0.15);
    doubleStar('BU 9', 0.3, 7.0, 7.5, 40.0, 1.0);
    DB::table('object_observation_stats')->insert([['object_name' => 'STF 1', 'observations' => 10], ['object_name' => 'BU 9', 'observations' => 20]]);

    $best = TonightsBest::for($user, ['night' => '2026-11-09', 'types' => ['DS'], 'planets' => false, 'comets' => false]);
    $names = collect($best['objects'])->pluck('name')->all();
    expect($names)->toContain('STF 1')
        ->and($names)->not->toContain('BU 9');
    // A double star needs no stored metrics
    Queue::assertNotPushed(ComputeContrastReserveForObjectsBatch::class);
});

it('reads the discoverer designation of a double star', function () {
    expect(WashingtonDoubleStars::designation('STF 2382'))->toBe(['STF', 2382, null])
        ->and(WashingtonDoubleStars::designation('STTA 89'))->toBe(['STTA', 89, null])
        ->and(WashingtonDoubleStars::designation('HJ 1735 AD'))->toBe(['HJ', 1735, 'AD'])
        ->and(WashingtonDoubleStars::designation('H 5 79'))->toBe(['H 5', 79, null])
        ->and(WashingtonDoubleStars::designation('Alpha CMa'))->toBeNull()
        ->and(WashingtonDoubleStars::designation('17 Dra'))->toBeNull();
});

it('fills in the magnitudes of the companions from the Washington Double Star Catalog', function () {
    // By designation: STF 1, 9.9" in the WDS, 8.91 and 10.71
    doubleStar('STF 1', 9.0, 8.9, 99.9, 37.2154, 0.14764);
    // By the Bayer name through the star: Sirius, AGC 1 AB, 11.1", -1.47 and 8.44
    DB::table('stars')->insert(['source_key' => sha1('sirius'), 'source_table' => 'stars8', 'constellation_code' => 'CMa', 'bayer' => 'Alp',
        'ra_degrees' => 101.28705, 'decl_degrees' => -16.7161, 'vmag' => -1.46]);
    doubleStar('Alpha CMa', 7.5, -1.5, 99.9, -16.7161, 6.7525);
    // A separation of another pair: STF 793 is 2.4" at 283°
    doubleStar('STF 793', 11.0, 9.6, 99.9, 71.5636, 5.98);
    // Agrees with the catalog: left alone
    doubleStar('STT 1', 2.0, 7.5, 9.5, 66.1263, 0.19735);

    $plan = WashingtonDoubleStars::plan();
    $matches = collect($plan['matches'])->keyBy('name');
    expect($matches['STF 1']['how'])->toBe('name')
        ->and($matches['STF 1']['changes'])->toBe(['subr' => 10.71])
        ->and($matches['Alpha CMa']['how'])->toBe('star')
        ->and($matches['Alpha CMa']['changes'])->toBe(['subr' => 8.44])
        ->and($matches['STF 793']['changes'])->toBe(['diam1' => 2.4, 'pa' => 283, 'subr' => 11.6])
        ->and($plan['rejected'])->toBe([])
        ->and($matches)->not->toHaveKey('STT 1');

    $this->artisan('objects:fill-double-star-magnitudes')->assertSuccessful();
    expect((float) DB::table('objects')->where('name', 'STF 1')->value('subr'))->toBe(99.9);

    $this->artisan('objects:fill-double-star-magnitudes', ['--apply' => true])->assertSuccessful();
    expect((float) DB::table('objects')->where('name', 'STF 1')->value('subr'))->toBe(10.71)
        ->and((float) DB::table('objects')->where('name', 'Alpha CMa')->value('subr'))->toBe(8.44)
        ->and((float) DB::table('objects')->where('name', 'STF 793')->value('diam1'))->toBe(2.4)
        ->and((float) DB::table('objects')->where('name', 'STT 1')->value('subr'))->toBe(9.5);
    // Nothing more to do
    expect(WashingtonDoubleStars::plan()['matches'])->toBe([]);
});

it('moves a double star at a wrong position to that of the Washington Double Star Catalog', function () {
    // BU 1212 with the declination of the wrong sign (-0° 03′)
    doubleStar('BU 1212', 0.5, 6.9, 8.4, 0.0511, 21.6586);
    DB::table('user_object_metrics')->insert(['user_id' => 1, 'instrument_id' => 1, 'location_id' => 1, 'lens_id' => null,
        'object_name' => 'BU 1212', 'created_at' => now(), 'updated_at' => now()]);

    $match = collect(WashingtonDoubleStars::plan()['matches'])->firstWhere('name', 'BU 1212');
    expect($match['how'])->toBe('name')
        ->and($match['changes'])->toMatchArray(['decl' => -0.051139]);

    $this->artisan('objects:fill-double-star-magnitudes', ['--apply' => true])->assertSuccessful();
    $object = DB::table('objects')->where('name', 'BU 1212')->first();
    expect(round((float) $object->decl, 4))->toBe(-0.0511)
        ->and($object->con)->toBe('AQR')
        ->and(DB::table('user_object_metrics')->where('object_name', 'BU 1212')->exists())->toBeFalse()
        ->and(round((float) DB::table('search_index')->where('name', 'BU 1212')->value('decl'), 4))->toBe(-0.0511);
});

it('leaves a double star alone that agrees with the catalog in neither separation nor primary', function () {
    // HU 559 is 0.6" with a primary of 9.6
    doubleStar('HU 559', 41.0, 6.4, 99.9, 51.573, 6.075);

    $plan = WashingtonDoubleStars::plan();
    expect(collect($plan['rejected'])->pluck('reason', 'name')->all())->toBe(['HU 559' => 'separation and primary'])
        ->and($plan['matches'])->toBe([]);
});
