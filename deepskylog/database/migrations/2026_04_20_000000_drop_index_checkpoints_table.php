<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

// Drops the index_checkpoints table that was created to support TNTSearch
// incremental indexing. TNTSearch has been removed from the project; runtime
// search uses MySQL FULLTEXT on the search_index table directly.
return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('index_checkpoints');
    }

    public function down(): void
    {
        // No restore — TNTSearch is removed and the table is no longer needed.
    }
};
