<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToObjectsAndObjectnames extends Migration
{
    public function up()
    {
        Schema::table('objects', function (Blueprint $table) {
            if (! Schema::hasColumn('objects', 'slug')) {
                $table->string('slug', 191)->nullable()->unique()->after('name');
            }
        });

        Schema::table('objectnames', function (Blueprint $table) {
            if (! Schema::hasColumn('objectnames', 'slug')) {
                $table->string('slug', 191)->nullable()->unique()->after('objectname');
            }
        });
    }

    public function down()
    {
        Schema::table('objects', function (Blueprint $table) {
            if (Schema::hasColumn('objects', 'slug')) {
                $table->dropColumn('slug');
            }
        });

        Schema::table('objectnames', function (Blueprint $table) {
            if (Schema::hasColumn('objectnames', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
}
