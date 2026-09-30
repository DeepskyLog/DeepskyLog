<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The names of objectnames without spaces and dashes, so that
     * ObjectController::show() can find /object/m31 or /object/ngc7000 with an
     * index instead of comparing REPLACE(objectname, ' ', '') with every row
     * (about 30 ms per candidate slug, most of the cost of an unknown slug).
     * Generated columns, so they need no upkeep; SQLite (the tests) cannot add
     * a stored column to an existing table, and indexes a virtual one as well.
     */
    public function up(): void
    {
        $generated = fn($column, string $expression) => Schema::getConnection()->getDriverName() === 'sqlite'
            ? $column->virtualAs($expression)
            : $column->storedAs($expression);

        Schema::table('objectnames', function (Blueprint $table) use ($generated) {
            $generated($table->string('objectname_compact', 128)->nullable(), "REPLACE(REPLACE(objectname, ' ', ''), '-', '')");
            $generated($table->string('altname_compact', 128)->nullable(), "REPLACE(REPLACE(altname, ' ', ''), '-', '')");
        });

        Schema::table('objectnames', function (Blueprint $table) {
            $table->index('objectname_compact');
            $table->index('altname_compact');
        });
    }

    public function down(): void
    {
        Schema::table('objectnames', function (Blueprint $table) {
            $table->dropIndex(['objectname_compact']);
            $table->dropIndex(['altname_compact']);
        });

        Schema::table('objectnames', function (Blueprint $table) {
            $table->dropColumn(['objectname_compact', 'altname_compact']);
        });
    }
};
