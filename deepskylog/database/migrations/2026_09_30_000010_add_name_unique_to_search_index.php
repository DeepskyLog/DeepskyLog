<?php

use App\Models\SearchIndex;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Whether no other search_index row (aliases left out) has the same name,
     * case-insensitively. AdvancedObjectSearchTable merges the rows of a name
     * into one; with this flag it only has to group the few rows that share a
     * name, instead of grouping the whole index on every sort or page click
     * (about 2.5 s). New rows get false, so a writer that does not update the
     * flag only makes its rows take the grouped path; search:reindex and the
     * object create/edit/delete actions set it (SearchIndex::markUniqueNames()).
     */
    public function up(): void
    {
        Schema::table('search_index', function (Blueprint $table) {
            $table->boolean('name_unique')->default(false)->after('name_normalized');
            $table->index('name_unique');
        });

        SearchIndex::markUniqueNames();
    }

    public function down(): void
    {
        Schema::table('search_index', function (Blueprint $table) {
            $table->dropIndex(['name_unique']);
            $table->dropColumn('name_unique');
        });
    }
};
