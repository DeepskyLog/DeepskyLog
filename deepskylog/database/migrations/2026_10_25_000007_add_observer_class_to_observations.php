<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The class an observer gives their own observation (#321): A for a top
     * observation that got a lot of time and attention, B for a good one,
     * C for a record that the object was seen; null when not graded, as all
     * older observations are.
     */
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->char('observer_class', 1)->nullable()->after('sqm');
            $table->index(['observer_username', 'observer_class']);
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropIndex(['observer_username', 'observer_class']);
            $table->dropColumn('observer_class');
        });
    }
};
