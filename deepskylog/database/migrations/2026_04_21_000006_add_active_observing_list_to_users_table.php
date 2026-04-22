<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('active_observing_list_id')->nullable()->after('stdinstrumentset');
            $table->foreign('active_observing_list_id')->references('id')->on('observing_lists')->onDelete('set null');
            $table->index('active_observing_list_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_observing_list_id']);
            $table->dropIndex(['active_observing_list_id']);
            $table->dropColumn('active_observing_list_id');
        });
    }
};
