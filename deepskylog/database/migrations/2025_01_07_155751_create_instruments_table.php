<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrument', function (Blueprint $table) {
            $table->id();

            $table->foreignId('make_id')->constrained('instrument_makes', 'id');
            $table->string('name', 255);
            $table->integer('aperture_mm');
            $table->integer('diameter')->virtualAs('aperture_mm');
            $table->foreignId('instrument_type_id')->constrained('instrument_types', 'id');
            $table->integer('type')->virtualAs('instrument_type_id - 1');
            $table->float('focal_length_mm')->nullable();
            $table->unsignedInteger('fixedMagnification')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('instrumentactive')->virtualAs('active');
            $table->float('fd')->virtualAs('ROUND(focal_length_mm / aperture_mm, 1)');
            $table->float('obstruction_perc')->nullable();
            $table->boolean('flip_image')->default(true);
            $table->boolean('flop_image')->default(true);
            $table->foreignId('mount_type_id')->constrained('mount_types', 'id');
            $table->foreignId('user_id')->constrained();
            $table->string('observer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument');
    }
};
