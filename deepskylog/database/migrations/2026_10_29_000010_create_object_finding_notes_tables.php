<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Finding notes: short tips of observers on how to find a deep-sky object
     * ("use the UHC", "hop from ζ Ori"), keyed on objects.name like the
     * observing list items, with the likes of other observers.
     */
    public function up(): void
    {
        Schema::create('object_finding_notes', function (Blueprint $table) {
            $table->id();
            $table->string('object_name');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('text', 500);
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();
            $table->index(['object_name', 'likes_count']);
            $table->index('user_id');
        });

        Schema::create('object_finding_note_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('object_finding_note_id')->constrained('object_finding_notes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['object_finding_note_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_finding_note_likes');
        Schema::dropIfExists('object_finding_notes');
    }
};
