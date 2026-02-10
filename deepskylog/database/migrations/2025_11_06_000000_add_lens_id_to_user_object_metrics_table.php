<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_object_metrics', function (Blueprint $table) {
            // Add nullable lens_id to allow storing instrument+lens combinations
            $table->unsignedBigInteger('lens_id')->nullable()->after('location_id');

            // Add an index that includes lens_id so lookups that include lens are fast.
            $table->index(['user_id', 'instrument_id', 'location_id', 'lens_id', 'object_name'], 'uom_user_instrument_location_lens_object_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_object_metrics', function (Blueprint $table) {
            $table->dropIndex('uom_user_instrument_location_lens_object_idx');
            $table->dropColumn('lens_id');
        });
    }
};
