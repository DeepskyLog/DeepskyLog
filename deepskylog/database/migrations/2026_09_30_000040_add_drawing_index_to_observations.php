<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * An index for the newest drawings (the homepage and /drawings), which
     * are sorted on observed_on and id: without it all deep-sky observations
     * were read to find the twenty newest with a drawing.
     */
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->index(['has_drawing', 'observed_on', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropIndex(['has_drawing', 'observed_on', 'id']);
        });
    }
};
