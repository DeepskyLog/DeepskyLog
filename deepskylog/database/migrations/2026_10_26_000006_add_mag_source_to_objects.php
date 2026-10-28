<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where an estimated magnitude of an object comes from ("LBN 953, class 1"),
 * null for a measured one. Set by objects:estimate-nebula-magnitudes, cleared
 * when the magnitude is edited.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->string('mag_source', 64)->nullable()->after('mag');
        });
    }

    public function down(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->dropColumn('mag_source');
        });
    }
};
