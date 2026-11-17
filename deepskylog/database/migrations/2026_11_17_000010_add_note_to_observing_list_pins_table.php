<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The observer's own note on a list they are working on ("for very dark
 * skies only", "binoculars", "sketch them all"): plain text, only for them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observing_list_pins', function (Blueprint $table) {
            $table->text('note')->nullable()->after('observing_list_id');
        });
    }

    public function down(): void
    {
        Schema::table('observing_list_pins', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
