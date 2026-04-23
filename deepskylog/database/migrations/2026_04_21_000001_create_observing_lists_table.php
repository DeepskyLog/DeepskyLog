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
        Schema::create('observing_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('public')->default(false);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();

            // Foreign key
            $table->foreign('owner_user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('owner_user_id');
            $table->index('public');
            $table->index('created_at');
            $table->index('likes_count');

            // Unique constraint per owner + name
            $table->unique(['owner_user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observing_lists');
    }
};
