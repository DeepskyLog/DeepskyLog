TNTSearch (direct usage)
================================

Overview
--------
TNTSearch is a pure-PHP full-text search engine. It does not require Docker or a separate service process — indices live on disk and are searched using PHP.

When to use
-----------
- You cannot run external services and want reasonable fuzzy/partial search.
- Small to medium datasets where indexing on disk is acceptable.

Pros / Cons
-----------
- Pros: No extra service, easy Composer install.
- Cons: Not distributed, disk I/O and memory use for large indexes; slower than dedicated search engines for very large datasets.

Install (direct TNTSearch usage)
-------
1. Require packages (no Scout):

    composer require teamtnt/tntsearch teamtnt/tntsearch-laravel

2. Configure storage path in `.env` (optional):

    TNTSEARCH_STORAGE=/var/www/DeepskyLog/deepskylog/storage/tnt

3. Ensure the storage path exists and is writable.

Indexing and searching (project-specific)
----------------------------------------
This project includes a console command that builds a TNTSearch index directly from the
`search_index` table:

    php artisan tntsearch:build-index --storage=/var/www/DeepskyLog/deepskylog/storage/tnt

The command stores the index files in the given storage path. To search the built index from PHP
you can use the TNTSearch client directly, for example:

    $tnt = new TeamTNT\TNTSearch\TNTSearch();
    $tnt->loadConfig(['storage' => storage_path('tnt')]);
    $index = $tnt->selectIndex('search_index');
    $res = $index->search('m31');


Tips
----
- Schedule indexing for large tables using queues or artisan commands.
- Regularly rebuild indexes after large data changes.
- If using MySQL/Postgres alongside TNTSearch, consider combining DB filters with search queries to reduce result sets before searching.

Further reading
---------------
-- TNTSearch library: https://github.com/teamtnt/tntsearch

Project-specific: importing existing `search_index`
------------------------------------------------
This project contains a `search_index` table populated by `search:reindex`.
To import that table into TNTSearch directly use the provided command:

    php artisan tntsearch:build-index --storage=/var/www/DeepskyLog/deepskylog/storage/tnt

The command builds a TNTSearch index from the `search_index` table into the
given storage path.

Incremental indexing and scheduling
----------------------------------
The project provides an incremental indexing command which updates the index
only for rows changed since the last run. A migration creates an
`index_checkpoints` table used to store the checkpoint timestamp.

Run the migration:

    php artisan migrate

Then ensure your scheduler runs (cron) and the incremental job will execute
every five minutes:

    php artisan schedule:run

