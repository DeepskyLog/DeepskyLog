<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The observing goals of the observers: a number of objects (null = all)
     * of a catalog or observing list to see or draw in a period.
     */
    public function up(): void
    {
        Schema::create('observing_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 120)->nullable();
            $table->string('catalog', 50)->nullable();
            $table->foreignId('observing_list_id')->nullable()->constrained('observing_lists')->nullOnDelete();
            $table->string('source_label', 255);
            $table->unsignedInteger('target_count')->nullable();
            $table->string('mode', 16)->default('observed');
            $table->boolean('new_only')->default(false);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('public')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'ends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observing_goals');
    }
};
