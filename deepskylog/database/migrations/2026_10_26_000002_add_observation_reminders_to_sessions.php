<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The reminder of a session without observations of its observer
     * (sessions:send-observation-reminders): when it was sent, so a session
     * gets one, and the setting on the profile page that turns it off.
     */
    public function up(): void
    {
        Schema::table('observation_sessions', function (Blueprint $table) {
            $table->timestamp('observation_reminder_sent_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('session_reminder')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('observation_sessions', function (Blueprint $table) {
            $table->dropColumn('observation_reminder_sent_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('session_reminder');
        });
    }
};
