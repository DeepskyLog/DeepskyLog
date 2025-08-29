<?php

use App\Models\InstrumentSet;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

it('defines many-to-many relations on the instrument set model', function () {
    $set = new InstrumentSet();

    expect($set->instruments())->toBeInstanceOf(BelongsToMany::class)
        ->and($set->eyepieces())->toBeInstanceOf(BelongsToMany::class)
        ->and($set->filters())->toBeInstanceOf(BelongsToMany::class)
        ->and($set->lenses())->toBeInstanceOf(BelongsToMany::class)
        ->and($set->locations())->toBeInstanceOf(BelongsToMany::class);
});
