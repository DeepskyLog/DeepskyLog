<?php

use App\Models\InstrumentSet;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

it('can instantiate an instrument set without persisting', function () {
    $set = new InstrumentSet(['name' => 'Test Set']);

    expect($set)->toBeInstanceOf(InstrumentSet::class)
        ->and($set->name)->toBe('Test Set');

    // relationships should be defined and return relation objects
    expect($set->instruments())->toBeInstanceOf(BelongsToMany::class)
        ->and($set->eyepieces())->toBeInstanceOf(BelongsToMany::class)
        ->and($set->filters())->toBeInstanceOf(BelongsToMany::class)
        ->and($set->lenses())->toBeInstanceOf(BelongsToMany::class)
        ->and($set->locations())->toBeInstanceOf(BelongsToMany::class);
});
