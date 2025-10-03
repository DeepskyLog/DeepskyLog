<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('index_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamp('last_indexed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('index_checkpoints');
    }
};
