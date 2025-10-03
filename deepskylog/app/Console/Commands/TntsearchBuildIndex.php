<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TeamTNT\TNTSearch\TNTSearch;
use App\Models\SearchIndex;

class TntsearchBuildIndex extends Command
{
    protected $signature = 'tntsearch:build-index {--storage=}';
    protected $description = 'Build TNTSearch index from the search_index table';

    public function handle()
    {
        $storage = $this->option('storage') ?: storage_path('tnt');
        if (!is_dir($storage)) mkdir($storage, 0775, true);

        $this->info('Initializing TNTSearch...');
        $tnt = new TNTSearch();
        $dbFile = $storage . DIRECTORY_SEPARATOR . 'tnt.sqlite';
        // Ensure directory
        if (!file_exists(dirname($dbFile))) mkdir(dirname($dbFile), 0775, true);
        // Ensure sqlite DB file exists
        if (!file_exists($dbFile)) {
            // create an empty sqlite file
            $handle = fopen($dbFile, 'w');
            if ($handle === false) {
                $this->error('Unable to create sqlite database file: ' . $dbFile);
                return 1;
            }
            fclose($handle);
        }

        $tnt->loadConfig([
            'driver' => 'sqlite',
            'database' => $dbFile,
            'storage' => $storage,
        ]);

        $indexName = 'search_index';
        $indexFile = $storage . DIRECTORY_SEPARATOR . $indexName;

        // Create index
        $index = $tnt->createIndex($indexName);
        $this->info('Indexing rows...');

        $chunk = 500;
        SearchIndex::chunk($chunk, function ($rows) use ($index) {
            foreach ($rows as $r) {
                $index->insert(['id' => $r->id, 'name' => $r->name]);
            }
        });

        $this->info('Done. Index stored in: ' . $storage);
        return 0;
    }
}
