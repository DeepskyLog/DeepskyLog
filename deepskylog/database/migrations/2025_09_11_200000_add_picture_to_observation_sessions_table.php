<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observation_sessions', function (Blueprint $table) {
            // Add nullable picture path for session images (compatible with storage/app/public/...)
            $table->string('picture')->nullable()->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('observation_sessions', function (Blueprint $table) {
            $table->dropColumn('picture');
        });
    }
};
