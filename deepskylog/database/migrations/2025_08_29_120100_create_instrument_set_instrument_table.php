<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrument_set_instrument', function (Blueprint $table) {
            $table->id();
            $table->foreignId(column: 'instrument_set_id')->constrained('instrument_sets');
            $table->foreignId(column: 'instrument_id')->constrained('instruments');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_set_instrument');
    }
};
