<?php

use Livewire\Livewire;
use Carbon\Carbon;

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
