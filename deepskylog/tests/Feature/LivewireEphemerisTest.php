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

it('ephemeris aside broadcasts the date and payload when the date changes', function () {
    Livewire::test(\App\Http\Livewire\EphemerisAside::class)
        ->set('date', '2026-10-01')
        ->assertDispatched('ephemerisDateChanged', date: '2026-10-01')
        ->assertDispatched('ephemerisPayloadUpdated', fn ($name, $params) => ($params['payload']['date'] ?? null) === '2026-10-01')
        ->assertDispatched('dsl-ephemeris-aside-updated', fn ($name, $params) => ($params['date'] ?? null) === '2026-10-01'
            && ! array_key_exists('illuminated_fraction', $params));
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

it('object ephemerides broadcasts the computed ephemerides', function () {
    DB::table('locations')->insert([
        'name' => 'Leuven',
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
        'slug' => 'leuven',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // CometDetails, MoonDetails, AladinPreviewInfo and ObjectConstellation
    // all wait for objectEphemeridesUpdated.
    Livewire::test(\App\Http\Livewire\ObjectEphemerides::class, ['objectId' => 'X'])
        ->call('recalculate', ['objectId' => 'X', 'ephemerides' => ['raDeg' => 10.68, 'decDeg' => 41.27], 'date' => '2026-10-01'])
        ->assertDispatched('objectEphemeridesUpdated', fn ($name, $params) => ($params['payload']['constellation'] ?? null) !== null)
        ->assertDispatched('aladin-preview-info-updated', fn ($name, $params) => array_key_exists('ephemerides', $params));
});
