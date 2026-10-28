<?php

use App\Livewire\AdvancedObjectSearchTable;
use App\Models\SearchIndex;
use App\Services\ObjectSearchIndex;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// AdvancedObjectSearchTable merges the search_index rows of a name into one,
// grouping only the rows flagged as sharing their name (name_unique).

function groupingIndexRow(string $name, string $sourceTable, string $sourcePk, string $sourceType, ?float $ra = null): int
{
    return DB::table('search_index')->insertGetId([
        'name' => $name,
        'name_normalized' => mb_strtolower($name),
        'source_table' => $sourceTable,
        'source_pk' => $sourcePk,
        'display_name' => $name,
        'source_type' => $sourceType,
        'ra' => $ra,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function groupingIndexRows(): void
{
    groupingIndexRow('Moon', 'moons', '1', 'moon', 1.5);
    groupingIndexRow('moon', 'lunar_features', '4', 'other', 2.5);
    groupingIndexRow('Mars', 'planets', '2', 'planet', 3.0);
    groupingIndexRow('NGC 7000', 'objects', 'ngc-7000', 'EMINB', 20.98);
    groupingIndexRow('North America Nebula', 'objects', 'ngc-7000', 'alias');
}

/**
 * The rows of the table: each column of the search_index part, in id order.
 */
function groupingTableRows(array $categories = []): array
{
    $table = new AdvancedObjectSearchTable();
    $table->objectCategories = $categories;

    return $table->datasource()->orderBy('search_index_id')->get()
        ->map(fn ($r) => [$r->search_index_id, $r->canonical_name_norm, $r->source_table, $r->source_pk, $r->display_name, $r->name, (float) $r->ra, $r->source_type])
        ->all();
}

/**
 * The rows the table listed when it grouped the whole index by name.
 */
function groupingAllRows(array $categories = []): array
{
    return DB::table('search_index')
        ->where('source_type', '<>', 'alias')
        ->when($categories, fn ($q) => $q->whereIn('source_table', $categories))
        ->selectRaw(
            'MIN(id) as search_index_id, '
            . 'LOWER(COALESCE(name, display_name)) as canonical_name_norm, '
            . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN source_table END), MIN(source_table)) as source_table, "
            . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN source_pk END), MIN(source_pk)) as source_pk, "
            . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN display_name END), COALESCE(MIN(CASE WHEN source_type <> 'alias' THEN display_name END), MIN(display_name))) as display_name, "
            . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN name END), COALESCE(MIN(CASE WHEN source_type <> 'alias' THEN name END), MIN(name))) as name, "
            . 'MAX(ra) as ra, '
            . "COALESCE(MIN(CASE WHEN source_type <> 'alias' THEN source_type END), MIN(source_type)) as source_type"
        )
        ->groupBy(DB::raw('LOWER(COALESCE(name, display_name))'))
        ->orderBy('search_index_id')
        ->get()
        ->map(fn ($r) => [$r->search_index_id, $r->canonical_name_norm, $r->source_table, $r->source_pk, $r->display_name, $r->name, (float) $r->ra, $r->source_type])
        ->all();
}

it('flags the rows whose name no other row has, case-insensitively', function () {
    groupingIndexRows();

    SearchIndex::markUniqueNames();

    expect(DB::table('search_index')->orderBy('id')->pluck('name_unique', 'name')->map(fn ($v) => (bool) $v)->all())->toBe([
        'Moon' => false,
        'moon' => false,
        'Mars' => true,
        'NGC 7000' => true,
        'North America Nebula' => true,
    ]);
});

it('only updates the given names', function () {
    groupingIndexRows();
    SearchIndex::markUniqueNames();
    DB::table('search_index')->where('name', 'Mars')->update(['name_unique' => false]);
    DB::table('search_index')->where('source_table', 'lunar_features')->delete();

    SearchIndex::markUniqueNames(['MOON']);

    expect(DB::table('search_index')->where('name', 'Moon')->value('name_unique'))->toBe(1)
        ->and(DB::table('search_index')->where('name', 'Mars')->value('name_unique'))->toBe(0);
});

it('lists the same rows as grouping the whole index by name', function (array $categories) {
    groupingIndexRows();
    SearchIndex::markUniqueNames();

    expect(groupingTableRows($categories))->toBe(groupingAllRows($categories));
})->with([
    'all categories' => [[]],
    'the moon of both tables' => [['moons', 'lunar_features']],
    'one table of the moon' => [['lunar_features']],
    'planets and deep-sky objects' => [['planets', 'objects']],
]);

it('merges the rows of a shared name into one', function () {
    groupingIndexRows();
    SearchIndex::markUniqueNames();

    $moon = collect(groupingTableRows())->firstWhere(1, 'moon');

    expect(collect(groupingTableRows())->where(1, 'moon'))->toHaveCount(1)
        ->and($moon[6])->toBe(2.5)
        ->and(collect(groupingTableRows(['lunar_features']))->pluck(3)->all())->toBe(['4']);
});

it('lists the same rows before the names are flagged', function () {
    groupingIndexRows();

    expect(DB::table('search_index')->where('name_unique', true)->count())->toBe(0)
        ->and(groupingTableRows())->toBe(groupingAllRows());
});

it('flags the names in search:reindex and when an object is saved or deleted', function () {
    $storage = sys_get_temp_dir() . '/dsl-search-grouping-' . uniqid();
    mkdir($storage . '/app', 0777, true);
    $this->app->useStoragePath($storage);

    DB::table('objects')->insert([
        'name' => 'Mars', 'slug' => 'mars-object', 'type' => 'GALXY', 'con' => 'LEO', 'ra' => 1.0, 'decl' => 2.0,
        'mag' => 9.3, 'subr' => 12.4, 'pa' => 174, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
        'diam1' => 600.0, 'diam2' => 180.0, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0', 'DSLDP' => '0',
        'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0', 'Interstellarum' => '0',
        'timestamp' => '2026-01-01 00:00:00',
    ]);
    DB::table('planets')->insert(['name' => 'Mars']);

    Artisan::call('search:reindex');
    expect(DB::table('search_index')->where('name', 'Mars')->pluck('name_unique')->all())->toBe([0, 0]);

    DB::table('planets')->delete();
    DB::table('search_index')->where('source_table', 'planets')->delete();
    ObjectSearchIndex::syncObject('Mars');
    expect(DB::table('search_index')->where('name', 'Mars')->pluck('name_unique')->all())->toBe([1]);

    groupingIndexRow('Mars', 'planets', '2', 'planet');
    ObjectSearchIndex::removeObject('Mars', 'mars-object');
    expect(DB::table('search_index')->where('name', 'Mars')->pluck('name_unique')->all())->toBe([1]);

    @unlink($storage . '/app/search_index_last_reindex.txt');
    @rmdir($storage . '/app');
    @rmdir($storage);
});
