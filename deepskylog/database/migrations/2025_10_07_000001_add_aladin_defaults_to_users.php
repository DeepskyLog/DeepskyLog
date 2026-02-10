<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('users')) return;
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'stdeyepiece')) {
                $table->unsignedBigInteger('stdeyepiece')->nullable()->after('stdtelescope');
            }
            if (! Schema::hasColumn('users', 'stdlens')) {
                $table->unsignedBigInteger('stdlens')->nullable()->after('stdeyepiece');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('users')) return;
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'stdlens')) {
                $table->dropColumn('stdlens');
            }
            if (Schema::hasColumn('users', 'stdeyepiece')) {
                $table->dropColumn('stdeyepiece');
            }
        });
    }
};
