<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Naked-eye limiting magnitudes measured by counting the stars in a
     * figure of bright stars (App\Services\StarCountAreas): the count, the
     * limit in the figure, its altitude and the limit at the zenith.
     */
    public function up(): void
    {
        Schema::create('sky_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->dateTime('measured_at');
            $table->string('area', 40);
            $table->unsignedSmallInteger('star_count');
            $table->decimal('area_magnitude', 4, 2);
            $table->decimal('altitude', 4, 1);
            $table->decimal('nelm', 3, 1);
            $table->timestamps();
            $table->index(['location_id', 'measured_at']);
            $table->index(['user_id', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sky_measurements');
    }
};
