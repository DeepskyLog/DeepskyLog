<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * "Your own eye" (App\Services\DetectionThreshold): the contrast reserve
     * at which the observer sees an object half the time, fitted from their
     * observations, the details of the fit, and whether Tonight's best, Next
     * for you and What's new for me use it.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('detection_threshold', 6, 3)->nullable();
            $table->json('detection_threshold_fit')->nullable();
            $table->boolean('use_detection_threshold')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['detection_threshold', 'detection_threshold_fit', 'use_detection_threshold']);
        });
    }
};
