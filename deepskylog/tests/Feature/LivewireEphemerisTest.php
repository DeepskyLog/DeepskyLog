<?php

use Livewire\Livewire;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('ephemeris aside sets date on mount', function () {
    $date = Carbon::now()->toDateString();

    Livewire::test(\App\Http\Livewire\EphemerisAside::class)
        ->assertSet('date', $date);
});

it('ephemeris aside keeps the date of the session on mount', function () {
    session()->put('dsl_ephemeris_date', '2026-12-24');

    Livewire::test(\App\Http\Livewire\EphemerisAside::class)
        ->assertSet('date', '2026-12-24')
        ->assertDispatched('ephemerisDateChanged', date: '2026-12-24');

    expect(session('dsl_ephemeris_date'))->toBe('2026-12-24');
});

it('ephemeris aside stores a date set by an event in the session', function () {
    Livewire::test(\App\Http\Livewire\EphemerisAside::class)
        ->dispatch('setEphemerisDate', '2027-01-02')
        ->assertSet('date', '2027-01-02');

    expect(session('dsl_ephemeris_date'))->toBe('2027-01-02');
});

it('search results table sets ephemerisDate when handler called', function () {
    $date = Carbon::now()->toDateString();

    $comp = new \App\Livewire\SearchResultsTable();
    $comp->handleEphemerisDateChanged($date);

    expect($comp->ephemerisDate)->toBe($date);
});

it('ephemeris aside broadcasts the date on mount', function () {
    Livewire::test(\App\Http\Livewire\EphemerisAside::class)
        ->assertDispatched('ephemerisDateChanged', date: Carbon::now()->toDateString());
});

it('ephemeris aside broadcasts the date once when the date changes', function () {
    // Every component recalculates itself on ephemerisDateChanged; a second
    // payload event made ObjectEphemerides and MoonDetails recalculate again.
    Livewire::test(\App\Http\Livewire\EphemerisAside::class)
        ->set('date', '2026-10-01')
        ->assertDispatched('ephemerisDateChanged', date: '2026-10-01')
        ->assertNotDispatched('ephemerisPayloadUpdated')
        ->assertNotDispatched('objectEphemeridesUpdated')
        ->assertDispatched('dsl-ephemeris-aside-updated', fn ($name, $params) => ($params['date'] ?? null) === '2026-10-01'
            && ! array_key_exists('illuminated_fraction', $params));

    expect(session('dsl_ephemeris_date'))->toBe('2026-10-01');
});

it('aladin selects dispatches the selection as a browser event', function () {
    Livewire::test(\App\Http\Livewire\AladinSelects::class, ['objectId' => 'M 31'])
        ->set('instrument', '5')
        ->assertDispatched('aladin-selects-changed', instrument: '5', eyepiece: null, lens: null, objectId: 'M 31')
        ->assertDispatched('aladinUpdated');
});

it('object constellation takes the constellation from objectEphemeridesUpdated', function () {
    Livewire::test(\App\Http\Livewire\ObjectConstellation::class, ['initialConstellation' => 'AND', 'objectId' => '42'])
        ->dispatch('objectEphemeridesUpdated', payload: ['objectId' => '42', 'constellation' => 'PEG'])
        ->assertSet('constellation', 'PEG')
        ->dispatch('objectEphemeridesUpdated', payload: ['objectId' => '7', 'constellation' => 'ORI'])
        ->assertSet('constellation', 'PEG')
        ->dispatch('setConstellation', value: 'CAS')
        ->assertSet('constellation', 'CAS');
});

function ephemerisTestLocation(string $name = 'Leuven'): int
{
    return DB::table('locations')->insertGetId([
        'name' => $name,
        'longitude' => 4.7,
        'latitude' => 50.88,
        'timezone' => 'Europe/Brussels',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 0,
        'country' => 'BE',
        'active' => true,
        'user_id' => \App\Models\User::factory()->create()->id,
        'observer' => 'test-observer',
        'observations' => 0,
        'slug' => \Illuminate\Support\Str::slug($name),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('object ephemerides broadcasts the computed ephemerides', function () {
    ephemerisTestLocation();

    // CometDetails, MoonDetails, AladinPreviewInfo and ObjectConstellation
    // all wait for objectEphemeridesUpdated.
    Livewire::test(\App\Http\Livewire\ObjectEphemerides::class, ['objectId' => 'X'])
        ->call('recalculate', ['objectId' => 'X', 'ephemerides' => ['raDeg' => 10.68, 'decDeg' => 41.27], 'date' => '2026-10-01'])
        ->assertDispatched('objectEphemeridesUpdated', fn ($name, $params) => ($params['payload']['constellation'] ?? null) !== null)
        ->assertDispatched('aladin-preview-info-updated', fn ($name, $params) => array_key_exists('ephemerides', $params));
});

it('gives guests the oldest active location, and users their standard location', function () {
    $oldest = ephemerisTestLocation('Leuven');
    $newer = ephemerisTestLocation('Genk');
    DB::table('locations')->where('id', $oldest)->update(['active' => false]);
    $newest = ephemerisTestLocation('Brugge');

    expect(\App\Models\Location::forEphemerides()?->id)->toBe($newer);

    $user = \App\Models\User::factory()->create(['stdlocation' => $newest]);
    expect(\App\Models\Location::forEphemerides($user)?->id)->toBe($newest);
});

it('object ephemerides polls the cache key the queued job writes, and stops polling', function () {
    Illuminate\Support\Facades\Queue::fake();
    $locationId = ephemerisTestLocation();

    $component = Livewire::test(\App\Http\Livewire\ObjectEphemerides::class, ['objectId' => 'X'])
        ->call('recalculate', ['objectId' => 'X', 'ephemerides' => ['raDeg' => 10.68, 'decDeg' => 41.27], 'date' => '2026-10-01', 'computeEphemerides' => true]);

    $pushed = null;
    Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ComputeEphemerides::class, function ($job) use (&$pushed) {
        $pushed = $job;

        return true;
    });
    $key = \App\Jobs\ComputeEphemerides::cacheKey($pushed->objectIdentifier, $pushed->dateIso, $pushed->locationId);
    expect($pushed->objectIdentifier)->toBe('X')
        ->and($pushed->locationId)->toBe($locationId);
    $component->assertSet('pendingCacheKey', $key)
        ->assertSeeHtml('wire:poll.5s="pollCache"');

    Illuminate\Support\Facades\Cache::put($key, ['date' => '2026-10-01', 'rising' => '18:00'], 60);
    $component->call('pollCache')
        ->assertSet('ephemerides.rising', '18:00')
        ->assertSet('pendingCacheKey', null)
        ->assertDontSeeHtml('wire:poll');

    // Without a result the component gives up after MAX_POLL_ATTEMPTS polls
    $component->set('ephemerides', null)->set('pendingCacheKey', 'ephemerides:missing');
    foreach (range(1, \App\Http\Livewire\ObjectEphemerides::MAX_POLL_ATTEMPTS) as $_) {
        $component->call('pollCache');
    }
    $component->assertSet('pendingCacheKey', null)->assertDontSeeHtml('wire:poll');
});
