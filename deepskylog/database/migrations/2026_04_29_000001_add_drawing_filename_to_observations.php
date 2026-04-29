<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add a drawing_filename column to the observations table so the image file
     * can be addressed independently of legacy_id or the new auto-increment id.
     *
     * Legacy observations (has_drawing = 1 and legacy_id IS NOT NULL) are
     * backfilled to "{legacy_id}.jpg" which matches the existing on-disk file
     * naming convention in public/images/drawings/ and public/images/cometdrawings/.
     *
     * New observations will set this column explicitly when a drawing is uploaded.
     */
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->string('drawing_filename', 191)->nullable()->after('has_drawing');
        });

        // Backfill legacy rows that already have a drawing on disk.
        DB::statement(
            "UPDATE observations
             SET drawing_filename = CONCAT(legacy_id, '.jpg')
             WHERE has_drawing = 1
               AND legacy_id IS NOT NULL
               AND drawing_filename IS NULL"
        );
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn('drawing_filename');
        });
    }
};
