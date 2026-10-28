<?php

// Times the object list AdvancedObjectSearchTable builds (count + one page,
// sorted by name, RA and magnitude), with the search_index rows grouped by
// name as the table used to do ("grouped") and with only the rows sharing a
// name grouped (name_unique, "current"). The observation statistics are left
// out, so only the listing of search_index is compared.
// Usage: php scripts/bench_advanced_search.php [category]

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SearchIndex;
use Illuminate\Support\Facades\DB;

$category = $argv[1] ?? null;
$key = SearchIndex::NAME_KEY;
$aggregates = 'MIN(id) as search_index_id, '
    . "{$key} as canonical_name_norm, "
    . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN source_table END), MIN(source_table)) as source_table, "
    . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN source_pk END), MIN(source_pk)) as source_pk, "
    . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN display_name END), COALESCE(MIN(CASE WHEN source_type <> 'alias' THEN display_name END), MIN(display_name))) as display_name, "
    . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN name END), COALESCE(MIN(CASE WHEN source_type <> 'alias' THEN name END), MIN(name))) as name, "
    . 'MAX(ra) as ra, MAX(decl) as decl, '
    . "COALESCE(MIN(CASE WHEN source_type <> 'alias' THEN source_type END), MIN(source_type)) as source_type, "
    . "COALESCE(MIN(CASE WHEN source_type <> 'alias' THEN metadata END), MIN(metadata)) as metadata";

$rows = fn () => DB::table('search_index')->where('source_type', '<>', 'alias')
    ->when($category, fn ($q) => $q->where('source_table', $category));

$subs = [
    'grouped' => fn () => $rows()->selectRaw($aggregates)->groupBy(DB::raw($key)),
    'current' => fn () => $rows()->where('name_unique', true)
        ->selectRaw("id as search_index_id, {$key} as canonical_name_norm, source_table, source_pk, display_name, name, ra, decl, source_type, metadata")
        ->unionAll($rows()->where('name_unique', false)->selectRaw($aggregates)->groupBy(DB::raw($key))),
];

$sorts = [
    'name' => function ($query) {
        $prefix = "LOWER(REGEXP_REPLACE(COALESCE(search_index.display_name, ''), '[0-9]', ''))";
        $num = "(CASE WHEN REGEXP_REPLACE(COALESCE(search_index.display_name, ''), '[^0-9]', '') = '' THEN 0 ELSE CAST(REGEXP_REPLACE(COALESCE(search_index.display_name, ''), '[^0-9]', '') AS UNSIGNED) END)";
        $query->orderByRaw("{$prefix} asc, {$num} asc, search_index.display_name asc");
    },
    'ra' => fn ($query) => $query->orderByRaw('(CASE WHEN COALESCE(objects.ra, search_index.ra) > 24 THEN COALESCE(objects.ra, search_index.ra) / 15.0 ELSE COALESCE(objects.ra, search_index.ra) END) asc'),
    'mag' => fn ($query) => $query->orderBy('objects.mag'),
];

foreach ($sorts as $label => $sort) {
    foreach ($subs as $variant => $sub) {
        $times = [];
        for ($i = 0; $i < 3; $i++) {
            $query = SearchIndex::query()->fromSub($sub(), 'search_index')
                ->leftJoin('objects', function ($join) {
                    $join->on('search_index.source_pk', '=', 'objects.slug')
                        ->where('search_index.source_table', '=', 'objects');
                })
                ->leftJoin('deepskytypes', 'deepskytypes.code', '=', 'objects.type')
                ->leftJoin('constellations', 'constellations.id', '=', 'objects.con')
                ->selectRaw('search_index.*, objects.name as obj_name, deepskytypes.name as type_name, constellations.name as constellation, objects.mag');
            $sort($query);
            $query->orderBy('search_index_id');

            $start = microtime(true);
            $page = $query->paginate(25, ['*'], 'page', 3);
            $times[] = microtime(true) - $start;
        }

        printf("%-4s %-7s total=%d  %.3f s (best of 3)  ids %s\n",
            $label,
            $variant,
            $page->total(),
            min($times),
            md5(implode(',', collect($page->items())->pluck('search_index_id')->all())));
    }
}
