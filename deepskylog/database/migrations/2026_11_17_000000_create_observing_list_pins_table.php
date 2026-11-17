<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The observing lists an observer is working on ("Working on"): a personal
 * mark on their own lists and the public lists they subscribed to, shown
 * first on /observing-lists, at the eyepiece and in the list pickers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observing_list_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('observing_list_id')->constrained('observing_lists')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'observing_list_id']);
            $table->index('observing_list_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observing_list_pins');
    }
};
