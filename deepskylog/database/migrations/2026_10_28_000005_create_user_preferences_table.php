<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The settings of the old site that users has no column for (legacy_ut,
     * legacy_used_languages, legacy_club, legacy_last_read_observation),
     * one row per observer and key: read by the legacy_observers view
     * (2026_10_28_000010), written by LegacyObserverSettingsSeeder and the
     * old site. The development databases got the table from a migration
     * that was later emptied and removed, so it is only created when missing.
     */
    public function up(): void
    {
        if (Schema::hasTable('user_preferences')) {
            return;
        }

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('key', 191)->index();
            $table->text('value');
            $table->timestamps();
            $table->unique(['user_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
