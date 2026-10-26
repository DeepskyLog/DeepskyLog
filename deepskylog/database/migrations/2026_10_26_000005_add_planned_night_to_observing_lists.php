<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The night (the date of its evening) and the location a list with times
     * is planned for, so a time typed for an object falls in that night. The
     * lists already made with times get the night of their first time.
     */
    public function up(): void
    {
        Schema::table('observing_lists', function (Blueprint $table) {
            $table->date('planned_night')->nullable();
            $table->unsignedInteger('planned_location_id')->nullable();
        });

        $first = DB::table('observing_list_items')
            ->whereNotNull('planned_at')
            ->groupBy('observing_list_id')
            ->selectRaw('observing_list_id, MIN(planned_at) as first_at')
            ->pluck('first_at', 'observing_list_id');
        foreach ($first as $listId => $firstAt) {
            $timezone = DB::table('observing_lists')->where('id', $listId)->value('planned_timezone') ?: 'UTC';
            // local time before noon belongs to the night before
            $night = Carbon::parse($firstAt, 'UTC')->setTimezone($timezone)->subHours(12)->toDateString();
            DB::table('observing_lists')->where('id', $listId)->update(['planned_night' => $night]);
        }
    }

    public function down(): void
    {
        Schema::table('observing_lists', function (Blueprint $table) {
            $table->dropColumn(['planned_night', 'planned_location_id']);
        });
    }
};
