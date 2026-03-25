<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('observation_sessions', function (Blueprint $table) {
            $table->text('weather')->default('')->change();
            $table->text('equipment')->default('')->change();
            $table->text('comments')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('observation_sessions', function (Blueprint $table) {
            $table->string('weather', 500)->default('')->change();
            $table->string('equipment', 500)->default('')->change();
            $table->string('comments', 5000)->nullable()->change();
        });
    }
};
