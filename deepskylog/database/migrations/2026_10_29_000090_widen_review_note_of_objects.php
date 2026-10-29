<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Observers report errors in an object from its page, each report added
     * to the review note (DeepskyObject::reportError()), so the note holds
     * several of them.
     */
    public function up(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->string('review_note', 1000)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->string('review_note', 255)->nullable()->change();
        });
    }
};
