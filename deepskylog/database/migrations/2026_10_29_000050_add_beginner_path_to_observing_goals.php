<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The beginner path (App\Services\BeginnerPath) is an observing goal of
     * all objects of an observing list made for the observer, marked with
     * beginner_path.
     */
    public function up(): void
    {
        Schema::table('observing_goals', function (Blueprint $table) {
            $table->boolean('beginner_path')->default(false)->after('challenge_id');
            $table->index(['user_id', 'beginner_path']);
        });
    }

    public function down(): void
    {
        Schema::table('observing_goals', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'beginner_path']);
            $table->dropColumn('beginner_path');
        });
    }
};
