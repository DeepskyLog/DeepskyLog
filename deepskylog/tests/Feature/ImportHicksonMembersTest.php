<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// objects:import-hickson-members adds the member galaxies of the Hickson
// compact groups from database/hickson_galaxies.csv (here: Hickson 92,
// Stephan's Quintet, the only group in the test database).

function hicksonObject(string $name, string $type, float $ra, float $decl): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => Str::slug($name),
        'type' => $type,
        'con' => 'PEG',
        'ra' => $ra,
        'decl' => $decl,
        'mag' => 14.0,
        'subr' => 99.9,
        'pa' => 999,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 0.0,
        'diam2' => 0.0,
        'datasource' => null,
        'taki' => '',
        'SBObj' => -999,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '0',
        'DSLDL' => '0',
        'DSLDP' => '0',
        'DSLLL' => '0',
        'DSLLP' => '0',
        'DSLOL' => '0',
        'DSLOP' => '0',
        'DeepskyHunter' => '0',
        'Interstellarum' => '0',
        'timestamp' => now(),
    ]);
    hicksonName($name, $name);
}

function hicksonName(string $object, string $altname): void
{
    [$catalog, $index] = App\Models\ObjectName::splitCatalogDesignation($altname);
    DB::table('objectnames')->insert([
        'objectname' => $object,
        'catalog' => $catalog,
        'catindex' => $index,
        'altname' => $altname,
        'slug' => Str::slug($altname),
        'timestamp' => now(),
    ]);
}

beforeEach(function () {
    hicksonObject('Hickson 92', 'GALCL', 22.5995, 33.96);
    // 92A by its NGC name, 92D by position (3" off; NGC 7318A is not in the
    // database), 92C is a member already, with an old lower case letter and
    // no part-of link; 92B and 92E are not in the database.
    hicksonObject('NGC 7320', 'GALXY', 22.600969, 33.94844);
    hicksonObject('PGC 69260', 'GALXY', 22.599139, 33.96556 + 3 / 3600);
    hicksonObject('NGC 7319', 'GALXY', 22.601069, 33.97533);
    hicksonName('NGC 7319', 'Hickson 92c');
});

it('only lists the members in a dry run', function () {
    $this->artisan('objects:import-hickson-members')->assertSuccessful();

    expect(DB::table('objectnames')->where('catalog', 'Hickson')->count())->toBe(2)
        ->and(DB::table('objectpartof')->count())->toBe(0);
});

it('adds the members as alternative names or new galaxies of their group', function () {
    $this->artisan('objects:import-hickson-members --apply')->assertSuccessful();

    $members = DB::table('objectnames')->where('catalog', 'Hickson')->where('catindex', '!=', '92')
        ->pluck('objectname', 'altname')->all();
    expect($members)->toEqual([
        'Hickson 92A' => 'NGC 7320',
        'Hickson 92B' => 'Hickson 92B',
        'Hickson 92c' => 'NGC 7319',
        'Hickson 92D' => 'PGC 69260',
        'Hickson 92E' => 'Hickson 92E',
    ]);

    expect(DB::table('objectpartof')->where('partofname', 'Hickson 92')->pluck('objectname')->sort()->values()->all())
        ->toBe(['Hickson 92B', 'Hickson 92E', 'NGC 7319', 'NGC 7320', 'PGC 69260']);

    // A new galaxy with the data of the catalog: BT 14.01, 66.6" x 32.9".
    $new = DB::table('objects')->where('name', 'Hickson 92B')->first();
    expect($new->type)->toBe('GALXY')
        ->and((float) $new->mag)->toBe(14.01)
        ->and((float) $new->diam1)->toEqualWithDelta(66.6, 0.01)
        ->and((float) $new->diam2)->toEqualWithDelta(32.9, 0.01)
        ->and($new->con)->toBe('PEG')
        ->and($new->datasource)->toBe('Hickson 1989 (VizieR VII/213)');

    // The members are found by the search.
    expect(DB::table('search_index')->where('name', 'Hickson 92A')->value('source_pk'))->not->toBeNull();

    // A second run changes nothing.
    $this->artisan('objects:import-hickson-members --apply')->assertSuccessful();
    expect(DB::table('objectnames')->where('catalog', 'Hickson')->count())->toBe(6)
        ->and(DB::table('objectpartof')->count())->toBe(5);
});
