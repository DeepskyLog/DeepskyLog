<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The warnings users:prune-inactive sends before it deletes an account
     * that recorded nothing: the date in the first warning (a month before),
     * and when the last warning (a week before) was sent.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('inactivity_deletion_at')->nullable();
            $table->timestamp('inactivity_final_warning_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['inactivity_deletion_at', 'inactivity_final_warning_at']);
        });
    }
};
