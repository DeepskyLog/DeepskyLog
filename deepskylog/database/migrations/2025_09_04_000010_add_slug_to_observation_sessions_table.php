<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observation_sessions', function (Blueprint $table) {
            $table->string('slug', 200)->default('')->after('name')->index();
        });
    }

    public function down(): void
    {
        Schema::table('observation_sessions', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });
    }
};
