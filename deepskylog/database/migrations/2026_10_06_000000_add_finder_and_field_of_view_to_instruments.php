<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The finderscope mounted on a telescope, and the true field of view of an
 * instrument with a fixed magnification (finders, binoculars), which has no
 * eyepiece to calculate it from. Both are used by the field views of an
 * object (naked eye, finder, telescope).
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('instruments', function (Blueprint $table) {
            $table->foreignId('finder_id')->nullable()->after('mount_type_id')
                ->constrained('instruments')->nullOnDelete();
            $table->float('field_of_view_deg')->nullable()->after('fixedMagnification');
        });
    }

    public function down(): void
    {
        Schema::table('instruments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('finder_id');
            $table->dropColumn('field_of_view_deg');
        });
    }
};
