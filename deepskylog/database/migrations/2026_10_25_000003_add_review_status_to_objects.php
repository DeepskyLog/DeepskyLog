<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The review of a deep-sky object by an administrator or database expert
     * (#526): new (added and not yet checked), checked or attention (needs
     * attention, with a note); null for the objects nobody has reviewed yet.
     * Reviewed on the object edit page and listed on /admin/object-reviews.
     */
    public function up(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->string('review_status', 16)->nullable()->index();
            $table->string('review_note', 255)->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->dropIndex(['review_status']);
            $table->dropColumn(['review_status', 'review_note', 'reviewed_by', 'reviewed_at']);
        });
    }
};
