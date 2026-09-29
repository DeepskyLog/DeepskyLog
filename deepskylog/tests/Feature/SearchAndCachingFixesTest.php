<?php

use App\Http\ResponseCache\TeamAwareCacheProfile;
use App\Http\ResponseCache\TeamAwareHasher;
use App\Livewire\AdvancedObjectSearchTable;
use App\Livewire\NearbyObjectsTable;
use App\Livewire\ObservationSearchTable;
use App\Livewire\SearchResultsTable;
use App\Models\Observation;
use App\Models\User;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\ResponseCache\Events\ClearingResponseCache;

it('filters the diameters in arcmin against the arcsec stored in the objects table', function (string $component) {
    $this->actingAs(User::factory()->withPersonalTeam()->create());
    Livewire::withoutLazyLoading();

    $table = Livewire::test($component, ['filters' => ['diam1_min' => '5', 'diam2_max' => '1.5']])->instance();
    $bindings = $table->datasource()->toBase()->getBindings();

    expect($bindings)->toContain(300.0)
        ->and($bindings)->toContain(90.0)
        ->and($bindings)->not->toContain(5.0)
        ->and($bindings)->not->toContain(1.5);
})->with([
    'advanced object search' => [AdvancedObjectSearchTable::class],
    'observation search' => [ObservationSearchTable::class],
]);

it('does not show the visual magnitude as the best magnification', function (string $component) {
    $user = User::factory()->withPersonalTeam()->create();
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'fixedMagnification' => null]);
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    $user->forceFill(['stdtelescope' => $instrument->id, 'stdlocation' => $location->id])->save();
    $this->actingAs($user->fresh());
    Livewire::withoutLazyLoading();

    $table = Livewire::test($component)->instance();
    $bestMag = $table->fields()->fields['computed_best_mag'];

    $row = (object) ['name' => 'M 57', 'mag' => 9.0, 'subr' => 12.0, 'diam1' => 86.0, 'diam2' => 62.0];
    expect($bestMag($row))->not->toContain('9x')->and($bestMag($row))->toContain('-');

    $row->typicalEyepieceFocal = 10.0;
    $expected = (int) round($instrument->focal_length_mm / 10.0);
    expect($bestMag($row))->toBe($expected . 'x');
})->with([
    'search results' => [SearchResultsTable::class],
    'nearby objects' => [NearbyObjectsTable::class],
]);

it('includes the locale in the response cache hash', function () {
    $hasher = app(TeamAwareHasher::class);
    $request = Request::create('/observers');

    app()->setLocale('en');
    $english = $hasher->getHashFor($request);
    app()->setLocale('nl');
    $dutch = $hasher->getHashFor($request);

    expect($dutch)->not->toBe($english);
});

it('does not use the response cache while flash data is present', function () {
    config(['responsecache.enabled' => true]);
    $profile = app(TeamAwareCacheProfile::class);

    $session = new Store('test', new ArraySessionHandler(60));
    $request = Request::create('/');
    $request->setLaravelSession($session);
    expect($profile->enabled($request))->toBeTrue();

    $session->flash('status', 'Saved');
    $session->ageFlashData();
    expect($profile->enabled($request))->toBeFalse();
});

it('only accepts the available locales in the language route', function () {
    $this->get('/language/nl')->assertRedirect();
    expect(session('locale'))->toBe('nl');

    $this->get('/language/xx')->assertNotFound();
    expect(session('locale'))->toBe('nl');
});

it('clears the response cache when observations and users change', function () {
    Event::fake([ClearingResponseCache::class]);
    $user = User::factory()->create();
    Event::assertDispatched(ClearingResponseCache::class);

    Event::fake([ClearingResponseCache::class]);
    $user->forceFill(['remember_token' => 'abc'])->save();
    Event::assertNotDispatched(ClearingResponseCache::class);

    $user->forceFill(['name' => 'Another Name'])->save();
    Event::assertDispatched(ClearingResponseCache::class);

    Event::fake([ClearingResponseCache::class]);
    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
    ]);
    Event::assertDispatched(ClearingResponseCache::class);
});
