<?php

use App\Livewire\LocationTable;
use App\Models\Location;
use App\Models\User;
use Database\Factories\LocationFactory;
use Livewire\Livewire;

function locationTableRows(User $user)
{
    test()->actingAs($user);

    return (new LocationTable)->datasource()->get()->keyBy('id');
}

it('computes the SQM, NELM and Bortle in SQL as the location model does', function () {
    $user = User::factory()->create(['fstOffset' => 0.3]);

    $values = [
        // SQM only, including the Bortle boundaries
        ...array_map(fn ($sqm) => [$sqm, -999], [10.0, 17.5, 17.51, 18.0, 18.5, 19.1, 19.11, 20.4, 21.3, 21.5, 21.7, 21.71, 22.0]),
        // NELM only, including the Bortle boundaries (above 7.93 the library gives NAN as SQM, the SQL 22)
        ...array_map(fn ($nelm) => [-999, $nelm], [2.5, 3.59, 3.6, 3.9, 4.4, 4.9, 5.8, 6.3, 6.4, 6.5, 7.2, 7.93]),
        // Both, and neither
        [20.5, 5.0],
        [-999, -999],
    ];

    $ids = [];
    foreach ($values as [$sqm, $nelm]) {
        $ids[] = LocationFactory::new()->create([
            'user_id' => $user->id,
            'observer' => $user->username,
            'skyBackground' => $sqm,
            'limitingMagnitude' => $nelm,
        ])->id;
    }

    $rows = locationTableRows($user);

    foreach ($ids as $id) {
        $location = Location::find($id);
        $row = $rows[$id];
        $context = "SQM {$location->skyBackground}, NELM {$location->limitingMagnitude}";

        expect($row->bortle === null ? null : (int) $row->bortle)->toBe($location->getBortle(), $context);
        expect($row->sqm === null ? null : round((float) $row->sqm, 2))
            ->toBe($location->getSqm() === null ? null : round((float) $location->getSqm(), 2), $context);
        expect($row->nelm === null ? null : round((float) $row->nelm, 2))
            ->toBe($location->getNelm(0.3) === null ? null : round((float) $location->getNelm(0.3), 2), $context);
    }
});

it('sorts the location table on SQM, NELM and Bortle', function () {
    $user = User::factory()->create();
    $make = fn (string $name, float $sqm, float $nelm) => LocationFactory::new()->create([
        'user_id' => $user->id,
        'observer' => $user->username,
        'name' => $name,
        'skyBackground' => $sqm,
        'limitingMagnitude' => $nelm,
    ]);

    // Only a NELM: its SQM (about 21.3) lies between the other two
    $make('Dark', 21.8, -999);
    $make('Medium', -999, 6.2);
    $make('Bright', 18.2, -999);

    $this->actingAs($user);

    foreach (['sqm' => ['Bright', 'Medium', 'Dark'], 'nelm' => ['Bright', 'Medium', 'Dark'], 'bortle' => ['Dark', 'Medium', 'Bright']] as $field => $order) {
        Livewire::test(LocationTable::class)
            ->call('sortBy', $field, 'asc')
            ->assertSeeInOrder($order);
    }
});
