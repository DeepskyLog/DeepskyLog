<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            // Preserve legacy id values by allowing explicit inserts.
            $table->bigIncrements('id');
            $table->string('sender')->index();
            $table->string('receiver')->index();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            // Legacy used a flexible date column (Ymd or datetime strings)
            $table->string('date')->nullable();
            // Helpful Laravel timestamps for future use
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
