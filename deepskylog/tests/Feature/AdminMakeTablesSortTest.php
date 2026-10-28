<?php

use App\Livewire\AdminEyepieceTable;
use App\Livewire\AdminInstrumentTable;
use App\Models\EyepieceMake;
use App\Models\InstrumentMake;
use App\Models\User;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;
use Livewire\Livewire;

test('the instrument makes can be sorted on their number of instruments', function () {
    test()->actingAs(User::factory()->create());

    $few = InstrumentMake::firstOrCreate(['name' => 'Few Optics']);
    $many = InstrumentMake::firstOrCreate(['name' => 'Many Optics']);
    InstrumentFactory::new()->create(['make_id' => $few->id, 'observer' => '']);
    InstrumentFactory::new()->count(3)->create(['make_id' => $many->id, 'observer' => '']);

    Livewire::test(AdminInstrumentTable::class)
        ->set('search', 'Optics')
        ->call('sortBy', 'number_of_instruments')
        ->assertOk()
        ->assertSeeInOrder(['Few Optics', 'Many Optics'])
        ->call('sortBy', 'number_of_instruments')
        ->assertSeeInOrder(['Many Optics', 'Few Optics']);
});

test('the eyepiece makes can be sorted on their number of eyepieces', function () {
    test()->actingAs(User::factory()->create());

    $few = EyepieceMake::firstOrCreate(['name' => 'Few Oculars']);
    $many = EyepieceMake::firstOrCreate(['name' => 'Many Oculars']);
    EyepieceFactory::new()->create(['make_id' => $few->id, 'observer' => '']);
    EyepieceFactory::new()->count(3)->create(['make_id' => $many->id, 'observer' => '']);

    Livewire::test(AdminEyepieceTable::class)
        ->set('search', 'Oculars')
        ->call('sortBy', 'number_of_eyepieces')
        ->assertOk()
        ->assertSeeInOrder(['Few Oculars', 'Many Oculars'])
        ->call('sortBy', 'number_of_eyepieces')
        ->assertSeeInOrder(['Many Oculars', 'Few Oculars']);
});
