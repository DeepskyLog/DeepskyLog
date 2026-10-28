<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The descriptions an observer wrote themselves in another language of
     * the site (#321), next to the translations of Google (#310). They are
     * shown instead of Google's, and neither the translation jobs nor an
     * edited description replace them.
     */
    public function up(): void
    {
        Schema::table('observation_translations', function (Blueprint $table) {
            $table->boolean('by_observer')->default(false)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('observation_translations', function (Blueprint $table) {
            $table->dropColumn('by_observer');
        });
    }
};
