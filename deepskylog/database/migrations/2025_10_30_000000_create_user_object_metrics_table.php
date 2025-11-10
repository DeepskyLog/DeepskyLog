<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_object_metrics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('instrument_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('object_name')->index();
            $table->decimal('contrast_reserve', 8, 4)->nullable();
            $table->string('contrast_reserve_category')->nullable();
            $table->integer('optimum_detection_magnification')->nullable();
            $table->json('optimum_eyepieces')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'instrument_id', 'location_id', 'object_name'], 'uom_user_instrument_location_object_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_object_metrics');
    }
};
