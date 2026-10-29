<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The observations that look wrong (App\Services\Observations\SuspectObservations):
     * the object below the horizon, far too faint for the instrument, or a
     * duplicate of another observation (related_observation_id). Filled by
     * observations:check-suspect; dismissed_at is set when the observer or an
     * administrator says the observation is right.
     */
    public function up(): void
    {
        Schema::create('suspect_observations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('observation_id');
            $table->string('reason', 16);
            $table->json('details')->nullable();
            $table->unsignedBigInteger('related_observation_id')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->foreignId('dismissed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('observation_id')->references('id')->on('observations')->cascadeOnDelete();
            $table->unique(['observation_id', 'reason']);
            $table->index(['reason', 'dismissed_at']);
            $table->index('related_observation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suspect_observations');
    }
};
