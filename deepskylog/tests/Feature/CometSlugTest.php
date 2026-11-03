<?php

use Illuminate\Support\Facades\DB;

function cometSlugTestInsert(array $comet): int
{
    return DB::table('cometobjects')->insertGetId($comet + ['icqname' => '', 'timestamp' => '']);
}

it('gives the comets without a slug one, so their page opens', function () {
    $hartley = cometSlugTestInsert(['name' => '161P/Hartley-IRAS']);
    $empty = cometSlugTestInsert(['name' => 'C/2020 F3 (NEOWISE)', 'slug' => '']);
    cometSlugTestInsert(['name' => '12P/Pons-Brooks', 'slug' => '12p-pons-brooks']);
    // A second comet whose name gives a slug that is already taken
    $twin = cometSlugTestInsert(['name' => '12P Pons Brooks']);

    (require database_path('migrations/2026_11_03_000000_fill_missing_comet_slugs.php'))->up();

    expect(DB::table('cometobjects')->where('id', $hartley)->value('slug'))->toBe('161phartley-iras')
        ->and(DB::table('cometobjects')->where('id', $empty)->value('slug'))->toBe('c2020-f3-neowise')
        ->and(DB::table('cometobjects')->where('name', '12P/Pons-Brooks')->value('slug'))->toBe('12p-pons-brooks')
        ->and(DB::table('cometobjects')->where('id', $twin)->value('slug'))->toBe('12p-pons-brooks-'.$twin);

    $this->get('/object/161phartley-iras')->assertOk();
});
