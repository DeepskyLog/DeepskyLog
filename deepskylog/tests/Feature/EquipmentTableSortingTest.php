<?php

use App\Livewire\EyepieceTable;
use App\Livewire\FilterTable;
use App\Livewire\InstrumentTable;
use App\Livewire\LensTable;
use App\Models\EyepieceMake;
use App\Models\FilterMake;
use App\Models\InstrumentMake;
use App\Models\InstrumentType;
use App\Models\LensMake;
use App\Models\MountType;
use App\Models\User;
use Database\Factories\EyepieceFactory;
use Database\Factories\FilterFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\LensFactory;
use Livewire\Livewire;

/**
 * The make of a piece of equipment is in its own table, which the equipment
 * tables join so that the Make column can be searched and sorted on. Rows with
 * an empty cell come last whichever way a column is sorted.
 */
beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create(['username' => 'tester']);
    $this->actingAs($this->user);
    Livewire::withoutLazyLoading();
});

test('the equipment tables sort on the name of the make, with empty makes last', function (string $component, string $makeModel, string $factory, string $sortField) {
    foreach (['Zeiss' => 'First item', '' => 'Unbranded item', 'Astro-Physics' => 'Second item'] as $make => $name) {
        $factory::new()->create([
            'user_id' => $this->user->id,
            'observer' => $this->user->username,
            'name' => $name,
            'make_id' => $makeModel::firstOrCreate(['name' => $make])->id,
        ]);
    }

    Livewire::test($component)
        ->call('sortBy', $sortField)
        ->assertOk()
        ->assertSeeInOrder(['Second item', 'First item', 'Unbranded item'])
        ->call('sortBy', $sortField)
        ->assertOk()
        ->assertSeeInOrder(['First item', 'Second item', 'Unbranded item']);
})->with([
    'instruments' => [InstrumentTable::class, InstrumentMake::class, InstrumentFactory::class, 'instrument_makes.name'],
    'eyepieces' => [EyepieceTable::class, EyepieceMake::class, EyepieceFactory::class, 'eyepiece_makes.name'],
    'lenses' => [LensTable::class, LensMake::class, LensFactory::class, 'lens_makes.name'],
    'filters' => [FilterTable::class, FilterMake::class, FilterFactory::class, 'filter_makes.name'],
]);

test('the equipment tables sort on their other joined columns', function (string $component, string $sortField) {
    Livewire::test($component)
        ->call('sortBy', $sortField)
        ->assertOk();
})->with([
    [InstrumentTable::class, 'instrument_types.name'],
    [InstrumentTable::class, 'focal_length_mm'],
    [InstrumentTable::class, 'fixedMagnification'],
    [InstrumentTable::class, 'fd'],
    [InstrumentTable::class, 'obstruction_perc'],
    [EyepieceTable::class, 'apparentFOV'],
    [EyepieceTable::class, 'max_focal_length_mm'],
    [EyepieceTable::class, 'field_stop_mm'],
    [LensTable::class, 'factor'],
    [EyepieceTable::class, 'eyepiece_types.name'],
    [FilterTable::class, 'filter_types.name'],
    [FilterTable::class, 'filter_colors.name'],
    [FilterTable::class, 'wratten'],
    [FilterTable::class, 'schott'],
]);

test('the instrument table sorts on the mount as shown, with empty mounts last', function () {
    $type = fn (string $name) => InstrumentType::unguarded(fn () => InstrumentType::firstOrCreate(['name' => $name]))->id;
    $mount = fn (string $name) => MountType::unguarded(fn () => MountType::firstOrCreate(['name' => $name]))->id;

    foreach ([
        ['Equatorial scope', 'Reflector', 'Equatorial'],
        ['Binocular pair', 'Binoculars', 'Alt-Az'],
        ['Dobson scope', 'Reflector', 'Dobsonian'],
    ] as [$name, $instrumentType, $mountType]) {
        InstrumentFactory::new()->create([
            'user_id' => $this->user->id,
            'observer' => $this->user->username,
            'name' => $name,
            'instrument_type_id' => $type($instrumentType),
            'mount_type_id' => $mount($mountType),
        ]);
    }

    Livewire::test(InstrumentTable::class)
        ->call('sortBy', 'mount_types.name')
        ->assertOk()
        ->assertSeeInOrder(['Dobson scope', 'Equatorial scope', 'Binocular pair'])
        ->call('sortBy', 'mount_types.name')
        ->assertOk()
        ->assertSeeInOrder(['Equatorial scope', 'Dobson scope', 'Binocular pair']);
});

test('the number columns put the cells shown empty last', function () {
    foreach (['Short tube' => 400, 'No focal length' => 0, 'Long tube' => 2000] as $name => $focalLength) {
        InstrumentFactory::new()->create([
            'user_id' => $this->user->id,
            'observer' => $this->user->username,
            'name' => $name,
            'focal_length_mm' => $focalLength,
        ]);
    }

    Livewire::test(InstrumentTable::class)
        ->call('sortBy', 'focal_length_mm')
        ->assertSeeInOrder(['Short tube', 'Long tube', 'No focal length'])
        ->call('sortBy', 'focal_length_mm')
        ->assertSeeInOrder(['Long tube', 'Short tube', 'No focal length']);

    foreach (['Wide stop' => 40, 'Unknown stop' => 0, 'Narrow stop' => 10] as $name => $fieldStop) {
        EyepieceFactory::new()->create([
            'user_id' => $this->user->id,
            'observer' => $this->user->username,
            'name' => $name,
            'field_stop_mm' => $fieldStop,
        ]);
    }

    Livewire::test(EyepieceTable::class)
        ->call('sortBy', 'field_stop_mm')
        ->assertSeeInOrder(['Narrow stop', 'Wide stop', 'Unknown stop'])
        ->call('sortBy', 'field_stop_mm')
        ->assertSeeInOrder(['Wide stop', 'Narrow stop', 'Unknown stop']);
});
