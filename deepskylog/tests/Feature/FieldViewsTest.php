<?php

use App\Livewire\CreateInstrument;
use App\Livewire\ObjectFieldViews;
use App\Models\DeepskyObject;
use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\InstrumentSet;
use App\Models\InstrumentType;
use App\Models\Location;
use App\Models\MountType;
use App\Models\User;
use App\Services\ContrastReserveCalculator;
use App\Services\FieldViews\FieldViewBuilder;
use App\Services\FieldViews\FieldViewRenderer;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\LensFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

function fieldViewObject(): void
{
    DB::table('objects')->insert([
        'name'          => 'M 13', 'slug' => 'm-13', 'type' => 'GLOCL', 'con' => 'HER',
        'ra'            => 16.6949, 'decl' => 36.4603, 'mag' => 5.8, 'subr' => 0.0, 'pa' => 999,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 1392.0, 'diam2' => 1392.0,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

/**
 * An observer with an 8" Dobsonian, a 9x50 finder on it, an eyepiece and a
 * location with a limiting magnitude.
 *
 * @return array{0: User, 1: Instrument, 2: Instrument, 3: Eyepiece, 4: Location}
 */
function fieldViewObserver(): array
{
    $user       = User::factory()->create();
    $reflector  = InstrumentType::firstOrCreate(['name' => 'Reflector']);
    $finderType = InstrumentType::firstOrCreate(['name' => 'Finderscope']);
    $altaz      = MountType::firstOrCreate(['name' => 'Alt/Azimuth']);

    $finder = InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => '9x50 finder', 'aperture_mm' => 50, 'focal_length_mm' => 180,
        'fixedMagnification' => 9, 'instrument_type_id' => $finderType->id, 'mount_type_id' => $altaz->id,
    ]);
    $telescope = InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => 'Dobson', 'aperture_mm' => 203, 'focal_length_mm' => 1200,
        'instrument_type_id' => $reflector->id, 'mount_type_id' => $altaz->id, 'finder_id' => $finder->id,
    ]);
    $eyepiece = EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'focal_length_mm' => 25, 'apparentFOV' => 52]);
    $location = LocationFactory::new()->create([
        'user_id'           => $user->id, 'observer' => $user->username, 'latitude' => 50.8, 'longitude' => 4.7, 'timezone' => 'Europe/Brussels',
        'limitingMagnitude' => 6.2, 'skyBackground' => -999,
    ]);
    $user->forceFill(['stdtelescope' => $telescope->id, 'stdlocation' => $location->id])->save();

    return [$user->fresh(), $telescope, $finder, $eyepiece, $location];
}

it('turns the view so that the zenith is up', function () {
    $renderer = new FieldViewRenderer();
    // Something low in the east: the zenith must be straight above it.
    [$ra, $dec] = FieldViewRenderer::horizontalToEquatorial(20.0, 100.0, 120.0, 50.0);
    $renderer->render(['ra' => $ra, 'dec' => $dec, 'radius_deg' => 45, 'star_limit' => 1, 'lst' => 120.0, 'latitude' => 50.0, 'up' => 'zenith']);

    [$x, $y] = $renderer->project(120.0, 50.0);
    expect(abs($x - 300))->toBeLessThan(0.01)
        ->and($y)->toBeLessThan(300);

    // And the horizon below the object lies below the centre.
    [$horizonRa, $horizonDec] = FieldViewRenderer::horizontalToEquatorial(0.0, 100.0, 120.0, 50.0);
    expect($renderer->project($horizonRa, $horizonDec)[1])->toBeGreaterThan(300);
});

it('draws east to the left with north up, and mirrors it when the image is flopped', function () {
    $renderer = new FieldViewRenderer();
    $renderer->render(['ra' => 100.0, 'dec' => 10.0, 'radius_deg' => 2, 'star_limit' => 1]);
    expect($renderer->project(101.0, 10.0)[0])->toBeLessThan(300)
        ->and($renderer->project(100.0, 11.0)[1])->toBeLessThan(300);

    $renderer->render(['ra' => 100.0, 'dec' => 10.0, 'radius_deg' => 2, 'star_limit' => 1, 'flop' => true, 'flip' => true]);
    expect($renderer->project(101.0, 10.0)[0])->toBeGreaterThan(300)
        ->and($renderer->project(100.0, 11.0)[1])->toBeGreaterThan(300);
});

it('converts horizontal coordinates to equatorial ones and back', function () {
    [$ra, $dec] = FieldViewRenderer::horizontalToEquatorial(35.0, 210.0, 77.0, 51.0);

    expect(round(FieldViewRenderer::altitude($ra, $dec, 77.0, 51.0), 6))->toBe(35.0);
});

it('builds the naked-eye, finder and telescope views', function () {
    fieldViewObject();
    [$user, $telescope, $finder, $eyepiece] = fieldViewObserver();
    DB::table('stars')->insert([
        'source_key' => sha1('m13-star'), 'source_table' => 'stars8', 'name' => null,
        'ra_hours'   => 16.69, 'ra_degrees' => 250.35, 'decl_degrees' => 36.5, 'vmag_int' => 700, 'vmag' => 7.0,
    ]);

    $object = DeepskyObject::where('name', 'M 13')->first();
    $result = app(FieldViewBuilder::class)->build($user, $object, ['datetime' => '2026-07-01T23:30']);

    expect($result['instrument']->id)->toBe($telescope->id)
        ->and($result['finder']->id)->toBe($finder->id)
        ->and($result['eyepiece']->id)->toBe($eyepiece->id)
        ->and($result['nelm'])->toBe(6.2)
        ->and($result['altitude'])->toBeGreaterThan(50)
        ->and($result['orientation'])->toBe('zenith')
        ->and($result['views']['naked_eye']['limit'])->toBe(6.2)
        ->and($result['views']['naked_eye']['svg'])->toContain('<svg')
        // 9x with 50°: an estimated field of 5.6°.
        ->and($result['views']['finder']['fov'])->toContain('5.6°')
        ->and($result['views']['finder']['limit'])->toBeGreaterThan(9.0)
        // 1200 / 25 = 48x, 52° / 48 = 1.1°.
        ->and($result['views']['telescope']['fov'])->toBe('1.1°')
        ->and($result['views']['telescope']['limit'])->toBeGreaterThan($result['views']['finder']['limit'])
        ->and($result['views']['telescope']['svg'])->toContain('<circle')
        // The name of the object on each view.
        ->and($result['views']['naked_eye']['svg'])->toContain('>M 13</text>')
        ->and($result['views']['finder']['svg'])->toContain('>M 13</text>')
        ->and($result['views']['telescope']['svg'])->toContain('>M 13</text>');
});

it('gives the contrast reserve at the magnification of the finder and the telescope, dimmed by the extinction', function () {
    fieldViewObject();
    [$user]  = fieldViewObserver();
    $object  = DeepskyObject::where('name', 'M 13')->first();
    $builder = app(FieldViewBuilder::class);

    $high      = $builder->build($user, $object, ['datetime' => '2026-07-01T23:30']);
    $finder    = $high['views']['finder']['contrast'];
    $telescope = $high['views']['telescope']['contrast'];
    expect($finder['value'])->toBeFloat()
        ->and($telescope['value'])->toBeFloat()
        ->and($telescope['category'])->toBe(ContrastReserveCalculator::category($telescope['value']))
        ->and($high['views'])->not->toHaveKey('naked_eye.contrast');

    // Lower in the sky, the object is fainter; below the horizon not visible.
    $low = $builder->build($user, $object, ['datetime' => '2026-07-02T04:30']);
    expect($low['altitude'])->toBeGreaterThan(0)->toBeLessThan($high['altitude'] - 20)
        ->and($low['views']['telescope']['contrast']['value'])->toBeLessThan($telescope['value']);
    $below = $builder->build($user, $object, ['datetime' => '2026-07-02T12:00']);
    expect($below['altitude'])->toBeLessThan(0)
        ->and($below['views']['telescope']['contrast'])->toBe(['value' => null, 'category' => 'not_visible']);

    Livewire::actingAs($user)->test(ObjectFieldViews::class, ['object' => $object])
        ->set('datetime', '2026-07-01T23:30')
        ->assertSee(number_format($telescope['value'], 2).' — ');
});

it('draws the outline of the object from the database instead of its symbol', function () {
    fieldViewObject();
    [$user] = fieldViewObserver();
    // A square of 0.4° around M 13.
    foreach ([[250.0, 36.26], [250.5, 36.26], [250.5, 36.66], [250.0, 36.66]] as $i => [$ra, $dec]) {
        DB::table('object_contours')->insert(['object_name' => 'M 13', 'contour_index' => 0, 'point_index' => $i, 'ra_degrees' => $ra, 'decl_degrees' => $dec]);
    }

    $object = DeepskyObject::where('name', 'M 13')->first();
    $result = app(FieldViewBuilder::class)->build($user, $object, ['datetime' => '2026-07-01T23:30']);

    // The outline in the target colour, closed; no circle of the globular cluster.
    expect($result['views']['telescope']['svg'])->toMatch('/<path d="M[^"]+Z" fill="none" stroke="#f87171"/')
        ->and($result['views']['telescope']['svg'])->not->toContain('<g fill="none" stroke="#f87171" stroke-width="1.6"><circle')
        ->and($result['views']['telescope']['svg'])->toContain('>M 13</text>');
});

it('draws objects without a magnitude only in the telescope, and never placeholders or objects larger than the field', function () {
    fieldViewObject();
    [$user] = fieldViewObserver();
    $base   = DB::table('objects')->where('name', 'M 13')->first();
    $add    = fn (string $name, float $mag, float $diam, float $raOffset) => DB::table('objects')->insert(
        ['name' => $name, 'slug' => Str::slug($name), 'type' => 'OPNCL', 'mag' => $mag, 'diam1' => $diam, 'diam2' => $diam, 'ra' => $base->ra + $raOffset]
        + collect((array) $base)->except(['id', 'name', 'name_normalized', 'slug', 'type', 'mag', 'diam1', 'diam2', 'ra'])->all()
    );
    // About 0.04° from M 13: inside both the finder and the telescope field.
    $add('Bright Cl', 5.0, 300.0, 0.002);
    $add('Unknown Cl', 99.9, 600.0, -0.002);
    $add('Zero Cl', 0.0, 600.0, 0.003);
    $add('Huge Ast', 1.0, 43200.0, -0.003);

    $object = DeepskyObject::where('name', 'M 13')->first();
    $views  = app(FieldViewBuilder::class)->build($user, $object, ['datetime' => '2026-07-01T23:30'])['views'];

    expect($views['finder']['svg'])->toContain('>Bright Cl</text>')
        ->not->toContain('>Unknown Cl</text>')
        ->not->toContain('>Zero Cl</text>')
        ->not->toContain('>Huge Ast</text>')
        ->and($views['telescope']['svg'])->toContain('>Unknown Cl</text>')
        ->toContain('>Zero Cl</text>')
        ->not->toContain('>Huge Ast</text>');
});

it('shows the field views page and the pdf to the observer', function () {
    fieldViewObject();
    [$user] = fieldViewObserver();

    $this->actingAs($user)->get(route('object.field-views', ['slug' => 'm-13']))
        ->assertOk()
        ->assertSee('M 13')
        ->assertSeeLivewire(ObjectFieldViews::class);

    $response = $this->actingAs($user)->get(route('object.field-views.pdf', ['slug' => 'm-13', 'datetime' => '2026-07-01T23:30']));
    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
});

it('sends guests to the login page', function () {
    fieldViewObject();

    $this->get(route('object.field-views', ['slug' => 'm-13']))->assertRedirect(route('login'));
});

it('gives a 404 for an unknown object', function () {
    [$user] = fieldViewObserver();

    $this->actingAs($user)->get(route('object.field-views', ['slug' => 'no-such-object']))->assertNotFound();
});

it('couples the finder chosen on the page to the telescope', function () {
    fieldViewObject();
    [$user, $telescope, $finder] = fieldViewObserver();
    $telescope->update(['finder_id' => null]);
    $other = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'fixedMagnification' => 6, 'name' => '6x30']);

    Livewire::actingAs($user)
        ->test(ObjectFieldViews::class, ['object' => DeepskyObject::where('name', 'M 13')->first()])
        ->set('instrument', (string) $telescope->id)
        ->set('finder', (string) $other->id)
        ->call('coupleFinder');

    expect($telescope->fresh()->finder_id)->toBe($other->id);
});

it('saves the finder and field of view of an instrument, but only an own finder', function () {
    [$user, $telescope, $finder] = fieldViewObserver();
    $owner                       = User::factory()->create();
    $stranger                    = InstrumentFactory::new()->create(['user_id' => $owner->id, 'observer' => $owner->username, 'fixedMagnification' => 8]);

    Livewire::actingAs($user)
        ->test(CreateInstrument::class, ['instrument' => $finder])
        ->set('field_of_view_deg', 6.5)
        ->call('save')
        ->assertHasNoErrors();
    expect($finder->fresh()->field_of_view_deg)->toBe(6.5)
        ->and($finder->fresh()->fixedFieldOfViewDeg())->toBe(6.5)
        ->and($finder->fresh()->hasEstimatedFieldOfView())->toBeFalse();

    Livewire::actingAs($user)
        ->test(CreateInstrument::class, ['instrument' => $telescope])
        ->set('finder_id', $stranger->id)
        ->call('save')
        ->assertHasErrors('finder_id');

    Livewire::actingAs($user)
        ->test(CreateInstrument::class, ['instrument' => $telescope])
        ->set('finder_id', $telescope->id)
        ->call('save')
        ->assertHasErrors('finder_id');
});

it('only offers the equipment of the chosen instrument set, with the finders mounted on its telescopes', function () {
    fieldViewObject();
    [$user, $telescope, $finder, $eyepiece, $location] = fieldViewObserver();
    $otherScope                                        = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Refractor', 'aperture_mm' => 80, 'focal_length_mm' => 500]);
    $otherEyepiece                                     = EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'focal_length_mm' => 10, 'apparentFOV' => 60]);

    $set = InstrumentSet::create(['user_id' => $user->id, 'name' => 'Dobson set', 'active' => true]);
    $set->instruments()->attach($telescope->id);
    $set->eyepieces()->attach($eyepiece->id);

    $object  = DeepskyObject::where('name', 'M 13')->first();
    $builder = app(FieldViewBuilder::class);

    $inSet = $builder->build($user, $object, ['set' => $set->id, 'instrument' => $otherScope->id, 'eyepiece' => $otherEyepiece->id, 'datetime' => '2026-07-01T23:30']);
    expect($inSet['set']->id)->toBe($set->id)
        ->and($inSet['choices']['instruments']->pluck('id')->all())->toBe([$telescope->id])
        ->and($inSet['choices']['eyepieces']->pluck('id')->all())->toBe([$eyepiece->id])
        ->and($inSet['choices']['finders']->pluck('id')->all())->toBe([$finder->id])
        // A set without locations keeps all of them.
        ->and($inSet['choices']['locations']->pluck('id')->all())->toBe([$location->id])
        ->and($inSet['instrument']->id)->toBe($telescope->id)
        ->and($inSet['eyepiece']->id)->toBe($eyepiece->id);

    // The standard set is the default, 'all' shows everything.
    $user->forceFill(['stdinstrumentset' => $set->id])->save();
    expect($builder->build($user->fresh(), $object)['set']?->id)->toBe($set->id)
        ->and($builder->build($user->fresh(), $object, ['set' => 'all'])['choices']['eyepieces'])->toHaveCount(2);
});

it('opens with a set that has the standard telescope', function () {
    fieldViewObject();
    [$user, $telescope] = fieldViewObserver();
    $refractor          = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Refractor', 'aperture_mm' => 80, 'focal_length_mm' => 500]);

    $refractorSet = InstrumentSet::create(['user_id' => $user->id, 'name' => 'A refractor set', 'active' => true]);
    $refractorSet->instruments()->attach($refractor->id);
    $dobsonSet = InstrumentSet::create(['user_id' => $user->id, 'name' => 'Dobson set', 'active' => true]);
    $dobsonSet->instruments()->attach($telescope->id);

    $object  = DeepskyObject::where('name', 'M 13')->first();
    $builder = app(FieldViewBuilder::class);

    // The standard set does not have the standard telescope: the set that has it is chosen.
    $user->forceFill(['stdinstrumentset' => $refractorSet->id])->save();
    $result = $builder->build($user->fresh(), $object, ['datetime' => '2026-07-01T23:30']);
    expect($result['set']->id)->toBe($dobsonSet->id)
        ->and($result['instrument']->id)->toBe($telescope->id);

    // No set has it: all equipment.
    $dobsonSet->instruments()->detach();
    $result = $builder->build($user->fresh(), $object, ['datetime' => '2026-07-01T23:30']);
    expect($result['set'])->toBeNull()
        ->and($result['instrument']->id)->toBe($telescope->id);

    // A chosen set is kept.
    expect($builder->build($user->fresh(), $object, ['set' => $refractorSet->id, 'datetime' => '2026-07-01T23:30'])['instrument']->id)->toBe($refractor->id);
});

it('shows the object at its highest during the night unless a time is chosen', function () {
    fieldViewObject();
    [$user]  = fieldViewObserver();
    $object  = DeepskyObject::where('name', 'M 13')->first();
    $builder = app(FieldViewBuilder::class);

    // In Brussels early in July it only gets nautically dark; M 13 transits
    // in that night at 90° - (50.8° - 36.46°).
    $result = $builder->build($user, $object, ['night' => '2026-07-01']);
    expect($result['time_mode'])->toBe('highest')
        ->and($result['darkness'])->toBe('nautical')
        ->and($result['night'])->toBe('2026-07-01')
        ->and(abs($result['altitude'] - 75.66))->toBeLessThan(0.3)
        ->and($result['moment']->timezoneName)->toBe('Europe/Brussels')
        ->and($result['moment']->between(Carbon\Carbon::parse('2026-07-01 22:00', 'Europe/Brussels'), Carbon\Carbon::parse('2026-07-02 05:00', 'Europe/Brussels')))->toBeTrue();

    $chosen = $builder->build($user, $object, ['datetime' => '2026-07-01T21:15']);
    expect($chosen['time_mode'])->toBe('chosen')
        ->and($chosen['moment']->format('Y-m-d H:i'))->toBe('2026-07-01 21:15');
});

it('switches between the current time, a chosen time and the highest point on the page', function () {
    fieldViewObject();
    [$user, $telescope] = fieldViewObserver();
    $object             = DeepskyObject::where('name', 'M 13')->first();

    $component = Livewire::actingAs($user)->test(ObjectFieldViews::class, ['object' => $object])
        ->call('now');
    expect($component->get('datetime'))->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/');

    $component->call('highest', '2026-07-01')
        ->assertSet('datetime', null)
        ->assertSet('night', '2026-07-01')
        ->assertSee('highest during the night of 2026-07-01');

    $component->set('instrumentSet', 'all')
        ->assertSet('instrumentSet', 'all')
        ->assertSet('instrument', (string) $telescope->id);
});

it('uses the lens that is always used with the telescope, unless no lens is chosen', function () {
    fieldViewObject();
    [$user, $telescope, $finder, $eyepiece] = fieldViewObserver();
    $paracorr                               = LensFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Paracorr', 'factor' => 1.15]);
    $telescope->update(['lens_id' => $paracorr->id]);

    $object  = DeepskyObject::where('name', 'M 13')->first();
    $builder = app(FieldViewBuilder::class);

    $result = $builder->build($user, $object, ['datetime' => '2026-07-01T23:30']);
    // 1200 mm x 1.15 / 25 mm = 55x.
    expect($result['lens']->id)->toBe($paracorr->id)
        ->and($result['views']['telescope']['subtitle'])->toContain('Paracorr · 55x');

    expect($builder->build($user, $object, ['lens' => 'none', 'datetime' => '2026-07-01T23:30'])['lens'])->toBeNull();

    // In an instrument set without the lens, the lens of its telescope is still offered.
    $set = InstrumentSet::create(['user_id' => $user->id, 'name' => 'Set', 'active' => true]);
    $set->instruments()->attach($telescope->id);
    expect($builder->build($user, $object, ['set' => $set->id])['lens']?->id)->toBe($paracorr->id);

    // Deleting the lens takes it off the telescope.
    $paracorr->delete();
    expect($telescope->fresh()->lens_id)->toBeNull();
});

it('saves the lens of an instrument, but only an own lens', function () {
    [$user, $telescope] = fieldViewObserver();
    $lens               = LensFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    $owner              = User::factory()->create();
    $foreign            = LensFactory::new()->create(['user_id' => $owner->id, 'observer' => $owner->username]);

    Livewire::actingAs($user)->test(CreateInstrument::class, ['instrument' => $telescope])
        ->set('lens_id', $lens->id)->call('save')->assertHasNoErrors();
    expect($telescope->fresh()->lens_id)->toBe($lens->id)
        ->and($user->fresh()->standardLensId())->toBe($lens->id);

    Livewire::actingAs($user)->test(CreateInstrument::class, ['instrument' => $telescope])
        ->set('lens_id', $foreign->id)->call('save')->assertHasErrors('lens_id');
});

it('gives the instrument options of the observation form the lens that is always used with them, for own instruments only', function () {
    [$user, $telescope] = fieldViewObserver();
    $lens               = LensFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Paracorr']);
    LensFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Barlow']);
    $telescope->update(['lens_id' => $lens->id]);
    $owner   = User::factory()->create();
    $foreign = InstrumentFactory::new()->create(['user_id' => $owner->id, 'observer' => $owner->username, 'lens_id' => LensFactory::new()->create(['user_id' => $owner->id, 'observer' => $owner->username])->id]);

    $options = collect($this->actingAs($user)->getJson(route('instrument.select.api'))->assertOk()->json());
    $own     = $options->firstWhere('id', $telescope->id);
    expect($own['lens_id'])->toBe($lens->id)
        ->and($own['lens_name'])->toContain('Paracorr');

    // An instrument without a lens says so, so that the form clears the lens.
    $finderOption = $options->first(fn ($option) => $option['id'] !== $telescope->id);
    expect(array_key_exists('lens_id', $finderOption))->toBeTrue()
        ->and($finderOption['lens_id'])->toBeNull();

    // The lens of someone else's instrument is not given away.
    $selected = $this->actingAs($user)->getJson(route('instrument.select.api', ['selected' => $foreign->id]))->assertOk()->json();
    expect($selected[0])->not->toHaveKey('lens_id');
});
