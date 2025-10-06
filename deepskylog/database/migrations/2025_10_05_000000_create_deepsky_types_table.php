<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates a small lookup table mapping legacy type codes to human-friendly names.
     */
    public function up(): void
    {
        Schema::create('deepskytypes', function (Blueprint $table) {
            $table->string('code', 8)->primary();
            $table->string('name', 191)->nullable(false);
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deepskytypes');
    }
};
