<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLegacyObjectsTable extends Migration
{
    public function up()
    {
        Schema::create('objects', function (Blueprint $table) {
            $table->string('name', 255)->primary();
            $table->string('type', 8)->default('');
            $table->string('con', 5)->default('');
            $table->float('ra')->default(0);
            $table->float('decl')->default(0);
            $table->float('mag')->default(0);
            $table->float('subr')->default(0);
            $table->integer('pa')->nullable();
            $table->integer('urano')->default(0);
            $table->integer('urano_new')->default(0);
            $table->integer('sky')->default(0);
            $table->string('millenium', 9)->default('');
            $table->float('diam1')->default(0);
            $table->float('diam2')->default(0);
            $table->string('datasource', 50)->nullable();
            $table->string('taki', 3)->default('');
            $table->float('SBObj')->default(0);
            $table->string('description', 1024)->default('');
            $table->string('psa', 3)->default('');
            $table->string('torresB', 3)->default('');
            $table->string('torresBC', 3)->default('');
            $table->string('torresC', 3)->default('');
            $table->string('milleniumbase', 4);
            $table->string('DSLDL', 4)->default('0');
            $table->string('DSLDP', 4)->default('0');
            $table->string('DSLLL', 4)->default('0');
            $table->string('DSLLP', 4)->default('0');
            $table->string('DSLOL', 4)->default('0');
            $table->string('DSLOP', 4)->default('0');
            $table->string('DeepskyHunter', 4)->default('0');
            $table->string('Interstellarum', 4)->default('0');
            // convert old char(14) timestamp to proper datetime
            $table->dateTime('timestamp');
        });
    }

    public function down()
    {
        Schema::dropIfExists('objects');
    }
}
