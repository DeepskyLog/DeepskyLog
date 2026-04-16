<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use TeamTNT\TNTSearch\Exceptions\IndexNotFoundException;
use TeamTNT\TNTSearch\TNTSearch;
use Carbon\Carbon;

class TntsearchIncrementalIndex extends Command
{
    protected $signature = 'tntsearch:incremental-index {--storage=}';
    protected $description = 'Incrementally update the TNTSearch index from new/updated search_index rows.';

    public function handle()
    {
        $storage = $this->option('storage') ?: storage_path('tnt');
        if (!is_dir($storage))
            mkdir($storage, 0775, true);

        // simple checkpoint row name
        $name = 'search_index';

        $checkpoint = DB::table('index_checkpoints')->where('name', $name)->first();
        $lastIndexedAt = $checkpoint ? Carbon::parse($checkpoint->last_indexed_at) : null;

        $this->info('Starting incremental indexing. Last indexed at: ' . ($lastIndexedAt ? $lastIndexedAt->toDateTimeString() : 'never'));

        $query = DB::table('search_index');
        if ($lastIndexedAt) {
            $query->where('updated_at', '>', $lastIndexedAt);
        }

        $rows = $query->orderBy('updated_at')->limit(1000)->get();
        if ($rows->isEmpty()) {
            $this->info('No new rows to index.');
            return 0;
        }

        $tnt = new TNTSearch();
        $dbFile = $storage . DIRECTORY_SEPARATOR . 'tnt.sqlite';
        if (!file_exists($dbFile)) {
            touch($dbFile);
        }
        $tnt->loadConfig(['driver' => 'sqlite', 'database' => $dbFile, 'storage' => $storage]);
        try {
            $index = $tnt->selectIndex('search_index');
        } catch (IndexNotFoundException $e) {
            $this->info('Index not found, creating new index `search_index`.');
            $index = $tnt->createIndex('search_index');
        }

        foreach ($rows as $r) {
            // insert or update the document in the TNTSearch index
            $index->insert(['id' => $r->id, 'name' => $r->name]);
            $last = Carbon::parse($r->updated_at);
        }

        // update checkpoint
        $now = now();
        if ($checkpoint) {
            DB::table('index_checkpoints')->where('name', $name)->update(['last_indexed_at' => $now, 'updated_at' => $now]);
        } else {
            DB::table('index_checkpoints')->insert(['name' => $name, 'last_indexed_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        }

        $this->info('Indexed ' . count($rows) . ' rows. Checkpoint updated to ' . $now);
        return 0;
    }
}
