<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReindexSearch extends Command
{
    protected $signature = 'search:reindex {--incremental : Only reindex rows changed since last run}';
    protected $description = 'Rebuild the search_index table from source tables';

    public function handle()
    {
        $incremental = $this->option('incremental');
        $this->info($incremental ? 'Running incremental reindex...' : 'Truncating search_index...');
        $lastRunFile = storage_path('app/search_index_last_reindex.txt');
        $lastRun = null;
        if ($incremental && file_exists($lastRunFile)) {
            $lastRun = trim(file_get_contents($lastRunFile));
            if ($lastRun === '')
                $lastRun = null;
        }

        if (!$incremental) {
            DB::table('search_index')->truncate();
        }

        // Capture timestamp once for the entire run instead of calling now() per row.
        $now = now()->toDateTimeString();

        $this->info('Indexing objects...');
        $objQuery = DB::table('objects')->select(['name', 'slug', 'ra', 'decl', 'type', 'description', 'timestamp']);
        if ($incremental && $lastRun) {
            $objQuery->where('timestamp', '>', $lastRun);
        }

        $objQuery->orderBy('name')->chunk(1000, function ($objects) use ($incremental, $now) {
            $rows = [];
            $pks = [];
            foreach ($objects as $o) {
                $pk = $o->slug ?? $o->name;
                $pks[] = $pk;
                $rows[] = [
                    'name' => $o->name,
                    'name_normalized' => mb_strtolower($o->name),
                    'source_table' => 'objects',
                    'source_pk' => $pk,
                    'display_name' => $o->name,
                    'source_type' => $o->type,
                    'ra' => $o->ra,
                    'decl' => $o->decl,
                    'metadata' => json_encode(['description' => $o->description]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            // Only delete existing entries when running incrementally; a full
            // reindex already truncated the table so the DELETE is a no-op that
            // still pays the index maintenance cost.
            if ($incremental && !empty($pks)) {
                DB::table('search_index')->where('source_table', 'objects')->whereIn('source_pk', $pks)->delete();
            }
            if (!empty($rows))
                DB::table('search_index')->insert($rows);
        });

        $this->info('Indexing object aliases...');
        // Join on the raw column so MariaDB can use the primary-key index on
        // objects.name.  The previous LOWER()-wrapped join forced a Block Nested
        // Loop (BNL) over 83K × 50K rows, and chunk()/OFFSET re-ran that full
        // scan for every page — the dominant cause of the 10+ hour runtime.
        // Using cursor() fetches the joined result set as a stream without
        // re-executing the query per page.
        $aliasQuery = DB::table('objectnames as n')
            ->leftJoin('objects as o', 'n.objectname', '=', 'o.name')
            ->select(['n.id', 'n.objectname', 'n.slug as alias_slug', 'n.altname', 'n.catalog', 'n.catindex', 'o.slug as canonical_slug', 'n.timestamp as ntimestamp'])
            ->orderBy('n.id');

        if ($incremental && $lastRun) {
            $aliasQuery->where('n.timestamp', '>', $lastRun);
        }

        $rows = [];
        $pks = [];
        foreach ($aliasQuery->cursor() as $a) {
            $display = $a->altname ?? $a->objectname;
            $pk = $a->canonical_slug ?? $a->alias_slug ?? $a->objectname;

            // Skip alias entries that would be identical to the canonical object
            // (i.e., no altname provided, or altname equals the canonical object
            // name). These rows duplicate the canonical object row and cause
            // duplicate results in the search UI.
            $alt = trim((string) ($a->altname ?? ''));
            $canonicalName = trim((string) ($a->objectname ?? ''));
            $altEmpty = ($alt === '');
            $altMatchesCanonical = (mb_strtolower($alt) === mb_strtolower($canonicalName));
            if (!empty($a->canonical_slug) && ($altEmpty || $altMatchesCanonical)) {
                continue;
            }

            $pks[] = $pk;
            $rows[] = [
                'name' => $display,
                'name_normalized' => mb_strtolower($display),
                'source_table' => 'objects',
                'source_pk' => $pk,
                'display_name' => $display,
                'source_type' => 'alias',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (count($rows) >= 1000) {
                if ($incremental && !empty($pks)) {
                    DB::table('search_index')->where('source_table', 'objects')->whereIn('source_pk', $pks)->delete();
                }
                DB::table('search_index')->insert($rows);
                $rows = [];
                $pks = [];
            }
        }
        if (!empty($rows)) {
            if ($incremental && !empty($pks)) {
                DB::table('search_index')->where('source_table', 'objects')->whereIn('source_pk', $pks)->delete();
            }
            DB::table('search_index')->insert($rows);
        }

        $this->info('Indexing comets...');
        $rows = [];
        // Use cursor() to stream large tables instead of loading all rows into memory.
        foreach (DB::table('cometobjects')->select(['id', 'name'])->cursor() as $c) {
            $rows[] = [
                'name' => $c->name,
                'name_normalized' => mb_strtolower($c->name),
                'source_table' => 'cometobjects',
                'source_pk' => (string) $c->id,
                'display_name' => $c->name,
                'source_type' => 'comet',
                'ra' => null,
                'decl' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (count($rows) >= 1000) {
                DB::table('search_index')->insert($rows);
                $rows = [];
            }
        }
        if (!empty($rows))
            DB::table('search_index')->insert($rows);

        $this->info('Indexing planets...');
        $rows = [];
        foreach (DB::table('planets')->select(['id', 'name', 'ra', 'decl', 'body_type'])->cursor() as $p) {
            $rows[] = [
                'name' => $p->name,
                'name_normalized' => mb_strtolower($p->name),
                'source_table' => 'planets',
                'source_pk' => (string) $p->id,
                'display_name' => $p->name,
                'source_type' => $p->body_type ?? 'planet',
                'ra' => $p->ra,
                'decl' => $p->decl,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (count($rows) >= 1000) {
                DB::table('search_index')->insert($rows);
                $rows = [];
            }
        }
        if (!empty($rows))
            DB::table('search_index')->insert($rows);

        $this->info('Indexing moons...');
        $rows = [];
        foreach (DB::table('moons')->select(['id', 'name', 'planet_id', 'body_type'])->cursor() as $m) {
            $rows[] = [
                'name' => $m->name,
                'name_normalized' => mb_strtolower($m->name),
                'source_table' => 'moons',
                'source_pk' => (string) $m->id,
                'display_name' => $m->name,
                'source_type' => $m->body_type ?? 'moon',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (count($rows) >= 1000) {
                DB::table('search_index')->insert($rows);
                $rows = [];
            }
        }
        if (!empty($rows))
            DB::table('search_index')->insert($rows);

        $this->info('Indexing lunar features...');
        $rows = [];
        foreach (DB::table('lunar_features')->select(['id', 'name', 'feature_type'])->cursor() as $f) {
            $rows[] = [
                'name' => $f->name,
                'name_normalized' => mb_strtolower($f->name),
                'source_table' => 'lunar_features',
                'source_pk' => (string) $f->id,
                'display_name' => $f->name,
                'source_type' => $f->feature_type,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (count($rows) >= 1000) {
                DB::table('search_index')->insert($rows);
                $rows = [];
            }
        }
        if (!empty($rows))
            DB::table('search_index')->insert($rows);

        $this->info('Indexing asteroids...');
        $rows = [];
        foreach (DB::table('asteroids')->select(['id', 'name', 'designation', 'ra', 'decl', 'body_type'])->cursor() as $a) {
            $rows[] = [
                'name' => $a->name,
                'name_normalized' => mb_strtolower($a->name),
                'source_table' => 'asteroids',
                'source_pk' => (string) $a->id,
                'display_name' => $a->name,
                'source_type' => $a->body_type ?? 'asteroid',
                'ra' => $a->ra,
                'decl' => $a->decl,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (count($rows) >= 1000) {
                DB::table('search_index')->insert($rows);
                $rows = [];
            }
        }
        if (!empty($rows))
            DB::table('search_index')->insert($rows);

        if ($incremental) {
            try {
                file_put_contents($lastRunFile, now()->toDateTimeString());
                $this->info('Incremental reindex finished; last-run timestamp updated.');
            } catch (\Exception $e) {
                $this->error('Failed to write last-run timestamp: ' . $e->getMessage());
            }
        }

        $this->info('Reindex complete.');
        return 0;
    }
}
