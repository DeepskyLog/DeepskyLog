<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The message with the year in review (observers:send-year-review): the
     * setting on the profile page that turns it off, and the last year it was
     * sent for, so an observer gets it once a year.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('year_review')->default(true);
            $table->smallInteger('year_review_sent_year')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['year_review', 'year_review_sent_year']);
        });
    }
};
