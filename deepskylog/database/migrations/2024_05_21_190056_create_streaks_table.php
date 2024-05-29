<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId(column: config('level-up.user.foreign_key'))->constrained()->onDelete('cascade');
            $table->foreignId(column: 'activity_id')->constrained('streak_activities')->onDelete('cascade');
            $table->integer(column: 'count')->default(1);
            $table->timestamp(column: 'activity_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streaks');
    }
};
