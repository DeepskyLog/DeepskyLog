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
        Schema::create('observations', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Preserve source provenance for idempotent legacy imports.
            $table->string('legacy_source', 32)->nullable();
            $table->unsignedInteger('legacy_id')->nullable();

            $table->string('observer_username', 255)->default('');
            $table->foreignId('observer_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedInteger('instrument_id')->nullable();
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('eyepiece_id')->nullable();
            $table->unsignedInteger('filter_id')->nullable();
            $table->unsignedInteger('lens_id')->nullable();

            $table->date('observed_on')->nullable();
            $table->time('observed_time')->nullable();
            $table->integer('legacy_date_int')->nullable();
            $table->integer('legacy_time_int')->nullable();
            $table->char('legacy_timestamp', 14)->nullable();

            $table->text('description')->nullable();
            $table->string('language', 10)->default('en');
            $table->boolean('has_drawing')->default(false);

            // Generic target description so one table supports deepsky/comets/planets/... .
            $table->string('target_type', 32)->nullable();
            $table->string('target_table', 64)->nullable();
            $table->string('target_key', 191)->nullable();
            $table->string('target_name', 255)->nullable();

            // Common observing conditions.
            $table->tinyInteger('seeing')->nullable();
            $table->decimal('limiting_magnitude', 5, 2)->nullable();
            $table->decimal('sqm', 5, 2)->nullable();

            $table->timestamps();

            $table->unique(['legacy_source', 'legacy_id']);
            $table->index(['target_type', 'target_name']);
            $table->index(['observer_username', 'observed_on']);
            $table->index(['location_id', 'observed_on']);
            $table->index(['instrument_id', 'observed_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
