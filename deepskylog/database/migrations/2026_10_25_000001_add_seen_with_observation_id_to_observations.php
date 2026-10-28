<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The observations of the objects seen in the same field (#831): the
     * observation entered with the form, which the observations of the
     * other objects point to. No foreign key: when that observation is
     * deleted, the others still share its id and stay together.
     */
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->unsignedBigInteger('seen_with_observation_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropIndex(['seen_with_observation_id']);
            $table->dropColumn('seen_with_observation_id');
        });
    }
};
