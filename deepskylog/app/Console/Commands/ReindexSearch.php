<?php

namespace App\Console\Commands;

use App\Models\SearchIndex;
use App\Services\ObjectSearchIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReindexSearch extends Command
{
    protected $signature = 'search:reindex {--incremental : Only reindex rows changed since last run}';
    protected $description = 'Rebuild the search_index table from source tables';

    public function handle()
    {
        $incremental = $this->option('incremental');
        $lastRunFile = storage_path('app/search_index_last_reindex.txt');
        $lastRun = null;
        if ($incremental && file_exists($lastRunFile)) {
            $lastRun = trim(file_get_contents($lastRunFile));
            if ($lastRun === '')
                $lastRun = null;
        }
        // Without a previous run there is nothing to compare with.
        if ($incremental && $lastRun === null) {
            $this->warn('No last-run timestamp found; running a full reindex.');
            $incremental = false;
        }
        $this->info($incremental ? 'Running incremental reindex...' : 'Truncating search_index...');

        if (!$incremental) {
            DB::table('search_index')->truncate();
        }

        // Capture timestamp once for the entire run instead of calling now() per row.
        $now = now()->toDateTimeString();

        $this->info('Indexing objects...');
        $objQuery = DB::table('objects')->select(['name', 'slug', 'ra', 'decl', 'type', 'description', 'timestamp']);
        if ($incremental) {
            $objQuery->where('timestamp', '>', $lastRun);
        }

        $objQuery->orderBy('name')->chunk(1000, function ($objects) use ($incremental, $now) {
            $rows = [];
            $pks = [];
            foreach ($objects as $o) {
                $row = ObjectSearchIndex::objectRow($o, $now);
                $pks[] = $row['source_pk'];
                $rows[] = $row;
            }
            // Only delete existing entries when running incrementally; a full
            // reindex already truncated the table so the DELETE is a no-op that
            // still pays the index maintenance cost.
            if ($incremental && !empty($pks)) {
                DB::table('search_index')
                    ->where('source_table', 'objects')
                    ->whereIn('source_pk', $pks)
                    ->where(fn ($q) => $q->whereNull('source_type')->orWhere('source_type', '!=', 'alias'))
                    ->delete();
            }
            if (!empty($rows))
                DB::table('search_index')->insert($rows);
        });

        $this->info('Indexing object aliases...');
        if ($incremental) {
            // An alias row carries the slug of its object, not of the alias, so
            // the alias rows of an object can only be replaced all at once. Take
            // every object with a changed alias or a changed object row, delete
            // its alias rows and index all of its aliases again.
            $changedObjects = DB::table('objects')->where('timestamp', '>', $lastRun)->pluck('name');
            $changedAliases = DB::table('objectnames')->where('timestamp', '>', $lastRun)->distinct()->pluck('objectname');
            $names = $changedObjects->merge($changedAliases)->unique()->values();

            foreach ($names->chunk(500) as $chunk) {
                $chunk = $chunk->values()->all();
                $rows = ObjectSearchIndex::aliasRows(
                    ObjectSearchIndex::aliasQuery()->whereIn('n.objectname', $chunk)->get(),
                    $now
                );
                $pks = DB::table('objects')->whereIn('name', $chunk)->get(['name', 'slug'])
                    ->map(fn ($o) => $o->slug ?? $o->name)
                    ->merge(array_column($rows, 'source_pk'))
                    ->unique()
                    ->values()
                    ->all();

                DB::transaction(function () use ($pks, $rows) {
                    if (!empty($pks)) {
                        DB::table('search_index')->where('source_table', 'objects')->where('source_type', 'alias')->whereIn('source_pk', $pks)->delete();
                    }
                    foreach (array_chunk($rows, 1000) as $batch) {
                        DB::table('search_index')->insert($batch);
                    }
                });
            }
        } else {
            // Join on the raw column so MariaDB can use the primary-key index on
            // objects.name.  The previous LOWER()-wrapped join forced a Block Nested
            // Loop (BNL) over 83K × 50K rows, and chunk()/OFFSET re-ran that full
            // scan for every page — the dominant cause of the 10+ hour runtime.
            // Using cursor() fetches the joined result set as a stream without
            // re-executing the query per page.
            $batch = [];
            foreach (ObjectSearchIndex::aliasQuery()->orderBy('n.id')->cursor() as $a) {
                $batch[] = $a;
                if (count($batch) >= 1000) {
                    $rows = ObjectSearchIndex::aliasRows($batch, $now);
                    if (!empty($rows))
                        DB::table('search_index')->insert($rows);
                    $batch = [];
                }
            }
            $rows = ObjectSearchIndex::aliasRows($batch, $now);
            if (!empty($rows))
                DB::table('search_index')->insert($rows);
        }

        $this->info('Indexing comets...');
        $this->clearSource('cometobjects', $incremental);
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
        $this->clearSource('planets', $incremental);
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
        $this->clearSource('moons', $incremental);
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
        $this->clearSource('lunar_features', $incremental);
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
        $this->clearSource('asteroids', $incremental);
        $rows = [];
        foreach (DB::table('asteroids')->select(['id', 'name', 'designation', 'body_type'])->cursor() as $a) {
            $rows[] = [
                'name' => $a->name,
                'name_normalized' => mb_strtolower($a->name),
                'source_table' => 'asteroids',
                'source_pk' => (string) $a->id,
                'display_name' => $a->name,
                'source_type' => $a->body_type ?? 'asteroid',
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

        // Flag the rows whose name no other row has, which the advanced search
        // lists without grouping them.
        $this->info('Marking unique names...');
        SearchIndex::markUniqueNames();

        // Store the start of this run, so rows changed while it ran are picked
        // up by the next incremental run. A full run counts as well.
        try {
            file_put_contents($lastRunFile, $now);
            $this->info('Last-run timestamp updated.');
        } catch (\Exception $e) {
            $this->error('Failed to write last-run timestamp: ' . $e->getMessage());
        }

        $this->info('Reindex complete.');
        return 0;
    }

    /**
     * Remove the rows of a source table before it is indexed again. These
     * tables have no change timestamp and are small, so an incremental run
     * replaces them completely; a full run already truncated the index.
     */
    private function clearSource(string $table, bool $incremental): void
    {
        if ($incremental) {
            DB::table('search_index')->where('source_table', $table)->delete();
        }
    }
}
