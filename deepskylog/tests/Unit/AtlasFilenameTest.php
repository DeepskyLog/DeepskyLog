<?php

use App\Http\Controllers\AtlasController;

it('names a whole atlas after its orientation, paper and type', function () {
    expect(AtlasController::filenameFor('detail', 'A4', 'portrait'))
        ->toBe('dsl-atlas-portrait-a4-detail.pdf');

    expect(AtlasController::filenameFor('Overview', 'A3', 'LANDSCAPE'))
        ->toBe('dsl-atlas-landscape-a3-overview.pdf');
});

it('appends the centre object for a single page', function () {
    expect(AtlasController::filenameFor('detail', 'A4', 'portrait', 'M 31'))
        ->toBe('dsl-atlas-portrait-a4-detail-m-31.pdf');
});
