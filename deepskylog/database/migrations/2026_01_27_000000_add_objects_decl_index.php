<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds indexes to speed up nearby-object bounding-box queries.
     *
     * Note: `decl` is the high-impact index. The composite index
     * (`decl`, `ra`) may help in future when RA is normalized.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            // If the index already exists the DB will error; use raw SQL guarded by existence check
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = array_map(fn($i) => $i->getName(), $sm->listTableIndexes('objects'));
            } catch (\Throwable $_) {
                $indexes = [];
            }

            if (! in_array('idx_decl', $indexes, true)) {
                $table->index('decl', 'idx_decl');
            }

            if (! in_array('idx_decl_ra', $indexes, true)) {
                // Create composite index on (decl, ra). This can help when RA
                // comparisons are normalized to degrees in future patches.
                $table->index(['decl', 'ra'], 'idx_decl_ra');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->dropIndex('idx_decl');
            $table->dropIndex('idx_decl_ra');
        });
    }
};
