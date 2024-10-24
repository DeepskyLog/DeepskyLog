<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instruments', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);
            $table->float('diameter');
            $table->foreignId('type')->constrained('instrument_types', 'id');
            $table->float('fd')->nullable();
            $table->unsignedInteger('fixedMagnification')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('user_id')->constrained();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instruments');
    }
};
