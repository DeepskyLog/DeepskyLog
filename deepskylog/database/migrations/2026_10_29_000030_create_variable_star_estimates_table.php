<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The magnitude estimates of variable stars, novae and supernovae
     * (App\Models\VariableStarEstimate): one per observation, with the
     * comparison stars and chart of the AAVSO, as the AAVSO Visual File
     * Format exports them. users.aavso_code is the observer's AAVSO observer
     * code, written in the header of that file.
     */
    public function up(): void
    {
        Schema::create('variable_star_estimates', function (Blueprint $table) {
            $table->unsignedBigInteger('observation_id')->primary();
            $table->decimal('magnitude', 5, 2);
            $table->boolean('fainter')->default(false);
            $table->string('comparison1', 20)->nullable();
            $table->string('comparison2', 20)->nullable();
            $table->string('chart', 20)->nullable();
            $table->string('comment_codes', 12)->nullable();

            $table->foreign('observation_id')->references('id')->on('observations')->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('aavso_code', 8)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('aavso_code');
        });
        Schema::dropIfExists('variable_star_estimates');
    }
};
