<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The site-wide observing challenges ("Hickson month"): a number of
     * objects (null = all) of a catalog or public observing list to see or
     * draw in a period. An observer who joins gets an observing goal linked
     * to the challenge (observing_goals.challenge_id).
     */
    public function up(): void
    {
        Schema::create('observing_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('catalog', 50)->nullable();
            $table->foreignId('observing_list_id')->nullable()->constrained('observing_lists')->nullOnDelete();
            $table->string('source_label', 255);
            $table->unsignedInteger('target_count')->nullable();
            $table->string('mode', 16)->default('observed');
            $table->boolean('new_only')->default(false);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('hidden')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['hidden', 'ends_on']);
        });

        Schema::table('observing_goals', function (Blueprint $table) {
            $table->foreignId('challenge_id')->nullable()->after('user_id')->constrained('observing_challenges')->cascadeOnDelete();
            $table->unique(['challenge_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('observing_goals', function (Blueprint $table) {
            $table->dropUnique(['challenge_id', 'user_id']);
            $table->dropConstrainedForeignId('challenge_id');
        });
        Schema::dropIfExists('observing_challenges');
    }
};
