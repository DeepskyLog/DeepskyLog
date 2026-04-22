<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('observing_list_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('observing_list_id');
            $table->string('object_name'); // Stable reference to deep-sky object
            $table->text('item_description')->nullable(); // Per-item user notes/description
            $table->string('source_mode')->default('manual'); // 'manual' or 'autofill'
            $table->unsignedBigInteger('source_observation_id')->nullable(); // Foreign ref to observation if autofilled
            $table->unsignedBigInteger('added_by_user_id'); // User who added this item to the list
            $table->timestamps();

            // Foreign keys
            $table->foreign('observing_list_id')->references('id')->on('observing_lists')->onDelete('cascade');
            $table->foreign('added_by_user_id')->references('id')->on('users')->onDelete('restrict');

            // Indexes
            $table->index('observing_list_id');
            $table->index('object_name');
            $table->index('added_by_user_id');

            // Unique constraint per list + object
            $table->unique(['observing_list_id', 'object_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observing_list_items');
    }
};
