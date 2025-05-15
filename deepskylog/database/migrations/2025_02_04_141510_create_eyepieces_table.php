<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eyepieces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('make_id')->constrained('eyepiece_makes', 'id');
            $table->foreignId('type_id')->constrained('eyepiece_types', 'id');
            $table->string('name', 255);
            $table->float('focal_length_mm')->nullable();
            $table->float('focalLength')->virtualAs('focal_length_mm');
            $table->unsignedInteger('apparentFOV');
            $table->float('max_focal_length_mm')->nullable();
            $table->float('maxFocalLength')->virtualAs('max_focal_length_mm');
            $table->float('field_stop_mm')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('eyepieceactive')->virtualAs('active');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->string('observer');
            $table->integer('observations', unsigned: true)->default(0);
            $table->string('picture')->nullable();
            $table->string('slug');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eyepieces');
    }
};
