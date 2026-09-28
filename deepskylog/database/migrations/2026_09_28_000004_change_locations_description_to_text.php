<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The description is TinyMCE HTML and easily longer than 255 characters.
     * TEXT columns can't have a default value on MySQL, so the column is made
     * nullable instead of keeping the '' default of the string column.
     */
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('locations')->whereNull('description')->update(['description' => '']);

        Schema::table('locations', function (Blueprint $table) {
            $table->string('description')->default('')->change();
        });
    }
};
