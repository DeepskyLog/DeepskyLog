<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Prefix existing drawing_filename values with their correct subdirectory so
     * the column now holds a full relative path (e.g. "drawings/12345.jpg" or
     * "cometdrawings/67890.jpg") that can be rendered as /images/{drawing_filename}.
     */
    public function up(): void
    {
        // Deepsky observations: prefix bare filenames that don't yet have a subdirectory
        DB::statement(
            "UPDATE observations
             SET drawing_filename = CONCAT('drawings/', drawing_filename)
             WHERE has_drawing = 1
               AND legacy_source = 'deepsky'
               AND drawing_filename IS NOT NULL
               AND drawing_filename NOT LIKE 'drawings/%'"
        );

        // Comet observations
        DB::statement(
            "UPDATE observations
             SET drawing_filename = CONCAT('cometdrawings/', drawing_filename)
             WHERE has_drawing = 1
               AND legacy_source = 'comet'
               AND drawing_filename IS NOT NULL
               AND drawing_filename NOT LIKE 'cometdrawings/%'"
        );
    }

    public function down(): void
    {
        // Strip the subdirectory prefix
        DB::statement(
            "UPDATE observations
             SET drawing_filename = SUBSTRING(drawing_filename, LENGTH('drawings/') + 1)
             WHERE drawing_filename LIKE 'drawings/%'"
        );

        DB::statement(
            "UPDATE observations
             SET drawing_filename = SUBSTRING(drawing_filename, LENGTH('cometdrawings/') + 1)
             WHERE drawing_filename LIKE 'cometdrawings/%'"
        );
    }
};
