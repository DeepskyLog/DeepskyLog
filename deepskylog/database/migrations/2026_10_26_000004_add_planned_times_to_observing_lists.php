<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The times of a list made for one night (the Messier marathon planner,
     * Tonight's best): when to observe each object and when it stands high
     * enough (UTC), shown in the time zone of the site it was planned for.
     */
    public function up(): void
    {
        Schema::table('observing_list_items', function (Blueprint $table) {
            $table->dateTime('planned_at')->nullable();
            $table->dateTime('planned_from')->nullable();
            $table->dateTime('planned_until')->nullable();
        });
        Schema::table('observing_lists', function (Blueprint $table) {
            $table->string('planned_timezone', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('observing_list_items', function (Blueprint $table) {
            $table->dropColumn(['planned_at', 'planned_from', 'planned_until']);
        });
        Schema::table('observing_lists', function (Blueprint $table) {
            $table->dropColumn('planned_timezone');
        });
    }
};
