<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Observing requests: an observer asks the others to observe a deep-sky
     * object (keyed on objects.name like the finding notes), optionally with
     * at least an aperture; the observations of others recorded after it are
     * its answers.
     */
    public function up(): void
    {
        Schema::create('observing_requests', function (Blueprint $table) {
            $table->id();
            $table->string('object_name');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('note', 500)->nullable();
            $table->unsignedSmallInteger('min_aperture_mm')->nullable();
            $table->date('expires_on');
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            $table->index(['object_name', 'closed_at']);
            $table->index(['user_id', 'closed_at']);
            $table->index(['closed_at', 'expires_on']);
        });

        Schema::create('observing_request_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observing_request_id')->constrained('observing_requests')->cascadeOnDelete();
            $table->foreignId('observation_id')->constrained('observations')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['observing_request_id', 'observation_id'], 'observing_request_answers_unique');
            $table->index('observation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observing_request_answers');
        Schema::dropIfExists('observing_requests');
    }
};
