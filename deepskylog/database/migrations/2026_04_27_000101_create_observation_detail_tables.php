<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deepsky_observation_details', function (Blueprint $table) {
            $table->unsignedBigInteger('observation_id')->primary();

            $table->tinyInteger('visibility')->nullable();
            $table->decimal('small_diameter', 8, 2)->nullable();
            $table->decimal('large_diameter', 8, 2)->nullable();
            $table->tinyInteger('stellar')->nullable();
            $table->tinyInteger('extended')->nullable();
            $table->tinyInteger('resolved')->nullable();
            $table->tinyInteger('mottled')->nullable();
            $table->char('cluster_type', 1)->nullable();
            $table->tinyInteger('unusual_shape')->nullable();
            $table->tinyInteger('partly_unresolved')->nullable();
            $table->tinyInteger('color_contrasts')->nullable();
            $table->string('magnification', 16)->nullable();
            $table->tinyInteger('equal_brightness')->nullable();
            $table->tinyInteger('nice_field')->nullable();
            $table->integer('component1')->nullable();
            $table->integer('component2')->nullable();
            $table->float('date_dec')->nullable(); // legacy float column (values up to ~1.2e8)

            $table->foreign('observation_id')
                ->references('id')
                ->on('observations')
                ->cascadeOnDelete();
        });

        Schema::create('comet_observation_details', function (Blueprint $table) {
            $table->unsignedBigInteger('observation_id')->primary();

            $table->unsignedInteger('comet_object_id')->nullable();
            $table->char('method', 1)->nullable();
            $table->decimal('magnitude', 6, 2)->nullable();
            $table->char('chart', 2)->nullable();
            $table->string('magnification', 8)->nullable();
            $table->boolean('magnitude_uncertain')->default(false);
            $table->boolean('less_magnitude')->default(false);
            $table->char('dc', 1)->nullable();
            $table->integer('coma')->nullable();
            $table->integer('tail')->nullable();
            $table->integer('position_angle')->nullable();

            $table->foreign('observation_id')
                ->references('id')
                ->on('observations')
                ->cascadeOnDelete();

            $table->index('comet_object_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comet_observation_details');
        Schema::dropIfExists('deepsky_observation_details');
    }
};
