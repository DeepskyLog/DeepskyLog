<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugsToPlanetsMoonsLunarAsteroids extends Migration
{
    public function up()
    {
        Schema::table('planets', function (Blueprint $table) {
            if (! Schema::hasColumn('planets', 'slug')) {
                $table->string('slug', 191)->nullable()->unique()->after('name');
            }
        });

        Schema::table('moons', function (Blueprint $table) {
            if (! Schema::hasColumn('moons', 'slug')) {
                $table->string('slug', 191)->nullable()->unique()->after('name');
            }
        });

        Schema::table('lunar_features', function (Blueprint $table) {
            if (! Schema::hasColumn('lunar_features', 'slug')) {
                $table->string('slug', 191)->nullable()->unique()->after('name');
            }
        });

        Schema::table('asteroids', function (Blueprint $table) {
            if (! Schema::hasColumn('asteroids', 'slug')) {
                $table->string('slug', 191)->nullable()->unique()->after('name');
            }
        });
    }

    public function down()
    {
        Schema::table('planets', function (Blueprint $table) {
            if (Schema::hasColumn('planets', 'slug')) {
                $table->dropColumn('slug');
            }
        });

        Schema::table('moons', function (Blueprint $table) {
            if (Schema::hasColumn('moons', 'slug')) {
                $table->dropColumn('slug');
            }
        });

        Schema::table('lunar_features', function (Blueprint $table) {
            if (Schema::hasColumn('lunar_features', 'slug')) {
                $table->dropColumn('slug');
            }
        });

        Schema::table('asteroids', function (Blueprint $table) {
            if (Schema::hasColumn('asteroids', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
}
