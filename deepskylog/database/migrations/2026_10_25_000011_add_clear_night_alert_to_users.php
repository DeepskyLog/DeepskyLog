<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The message when a clear, dark night is forecast at the observer's
     * standard location (forecast:send-clear-night-alerts), and the night the
     * last one was sent for, so a night gets at most one.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('clear_night_alert')->default(false);
            $table->date('clear_night_alert_night')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['clear_night_alert', 'clear_night_alert_night']);
        });
    }
};
