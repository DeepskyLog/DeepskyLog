<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReindexSearch extends Command
{
    protected $signature = 'search:reindex';
    protected $description = 'Rebuild the search_index table from source tables';

    public function handle()
    {
        $this->info('Truncating search_index...');
        DB::table('search_index')->truncate();

        $this->info('Indexing objects...');
        $objects = DB::table('objects')->select(['name','ra','decl','type','description'])->get();
        $rows = [];
        foreach ($objects as $o) {
            $rows[] = [
                'name' => $o->name,
                'name_normalized' => mb_strtolower($o->name),
                'source_table' => 'objects',
                'source_pk' => $o->name,
                'display_name' => $o->name,
                'source_type' => $o->type,
                'ra' => $o->ra,
                'decl' => $o->decl,
                'metadata' => json_encode(['description' => $o->description]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (count($rows) >= 500) {
                DB::table('search_index')->insert($rows);
                $rows = [];
            }
        }
        if (!empty($rows)) DB::table('search_index')->insert($rows);

        $this->info('Indexing object aliases...');
        $rows = [];
        $aliases = DB::table('objectnames')->select(['objectname','catalog','catindex','altname'])->get();
        foreach ($aliases as $a) {
            $display = $a->altname;
            $rows[] = [
                'name' => $a->altname,
                'name_normalized' => mb_strtolower($a->altname),
                'source_table' => 'objects',
                'source_pk' => $a->objectname,
                'display_name' => $display,
                'source_type' => 'alias',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (count($rows) >= 500) { DB::table('search_index')->insert($rows); $rows = []; }
        }
        if (!empty($rows)) DB::table('search_index')->insert($rows);

        $this->info('Indexing comets...');
        $rows = [];
    // cometobjects in this schema don't have RA/DEC columns - select id and name only
    $comets = DB::table('cometobjects')->select(['id','name'])->get();
        foreach ($comets as $c) {
            $rows[] = [
                'name' => $c->name,
                'name_normalized' => mb_strtolower($c->name),
                'source_table' => 'cometobjects',
                'source_pk' => (string)$c->id,
                'display_name' => $c->name,
                'source_type' => 'comet',
                'ra' => null,
                'decl' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (count($rows) >= 500) { DB::table('search_index')->insert($rows); $rows = []; }
        }
        if (!empty($rows)) DB::table('search_index')->insert($rows);

        $this->info('Indexing planets...');
        $rows = [];
        $planets = DB::table('planets')->select(['id','name','ra','decl','body_type'])->get();
        foreach ($planets as $p) {
            $rows[] = [
                'name' => $p->name,
                'name_normalized' => mb_strtolower($p->name),
                'source_table' => 'planets',
                'source_pk' => (string)$p->id,
                'display_name' => $p->name,
                'source_type' => $p->body_type ?? 'planet',
                'ra' => $p->ra,
                'decl' => $p->decl,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (count($rows) >= 500) { DB::table('search_index')->insert($rows); $rows = []; }
        }
        if (!empty($rows)) DB::table('search_index')->insert($rows);

        $this->info('Indexing moons...');
        $rows = [];
        $moons = DB::table('moons')->select(['id','name','planet_id','body_type'])->get();
        foreach ($moons as $m) {
            $rows[] = [
                'name' => $m->name,
                'name_normalized' => mb_strtolower($m->name),
                'source_table' => 'moons',
                'source_pk' => (string)$m->id,
                'display_name' => $m->name,
                'source_type' => $m->body_type ?? 'moon',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (count($rows) >= 500) { DB::table('search_index')->insert($rows); $rows = []; }
        }
        if (!empty($rows)) DB::table('search_index')->insert($rows);

        $this->info('Indexing lunar features...');
        $rows = [];
        $features = DB::table('lunar_features')->select(['id','name','feature_type'])->get();
        foreach ($features as $f) {
            $rows[] = [
                'name' => $f->name,
                'name_normalized' => mb_strtolower($f->name),
                'source_table' => 'lunar_features',
                'source_pk' => (string)$f->id,
                'display_name' => $f->name,
                'source_type' => $f->feature_type,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (count($rows) >= 500) { DB::table('search_index')->insert($rows); $rows = []; }
        }
        if (!empty($rows)) DB::table('search_index')->insert($rows);

        $this->info('Indexing asteroids...');
        $rows = [];
        $asteroids = DB::table('asteroids')->select(['id','name','designation','ra','decl','body_type'])->get();
        foreach ($asteroids as $a) {
            $rows[] = [
                'name' => $a->name,
                'name_normalized' => mb_strtolower($a->name),
                'source_table' => 'asteroids',
                'source_pk' => (string)$a->id,
                'display_name' => $a->name,
                'source_type' => $a->body_type ?? 'asteroid',
                'ra' => $a->ra,
                'decl' => $a->decl,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (count($rows) >= 500) { DB::table('search_index')->insert($rows); $rows = []; }
        }
        if (!empty($rows)) DB::table('search_index')->insert($rows);

        $this->info('Reindex complete.');
        return 0;
    }
}
