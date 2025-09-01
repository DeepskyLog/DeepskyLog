<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrument_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId(column: 'user_id')->constrained();
            $table->string('name');
            $table->string('slug')->nullable()->index();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->string('picture')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_sets');
    }
};
