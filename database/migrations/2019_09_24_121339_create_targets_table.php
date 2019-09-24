<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->string('icqname', 11)->nullable();
            $table->string('type', 8)->nullable();
            $table->string('con', 5)->nullable();
            $table->float('ra')->unsigned()->nullable();
            $table->float('decl')->nullable();
            $table->float('mag')->nullable();
            $table->float('subr')->nullable();
            $table->float('diam1')->unsigned()->nullable();
            $table->float('diam2')->unsigned()->nullable();
            $table->smallInteger('pa')->unsigned()->nullable();
            $table->float('SBObj')->nullable();
            $table->string('datasource', 50)->nullable();
            $table->string('description', 1024)->nullable();

            $table->smallInteger('urano')->unsigned()->nullable();
            $table->smallInteger('urano_new')->unsigned()->nullable();
            $table->smallInteger('sky')->unsigned()->nullable();
            $table->smallInteger('millenium')->unsigned()->nullable();
            $table->smallInteger('taki')->unsigned()->nullable();
            $table->smallInteger('psa')->unsigned()->nullable();
            $table->smallInteger('torresB')->unsigned()->nullable();
            $table->smallInteger('torresBC')->unsigned()->nullable();
            $table->smallInteger('torresC')->unsigned()->nullable();
            $table->smallInteger('milleniumbase')->unsigned()->nullable();
            $table->smallInteger('DSLDL')->unsigned()->nullable();
            $table->smallInteger('DSLDP')->unsigned()->nullable();
            $table->smallInteger('DSLLL')->unsigned()->nullable();
            $table->smallInteger('DSLLP')->unsigned()->nullable();
            $table->smallInteger('DSLOL')->unsigned()->nullable();
            $table->smallInteger('DSLOP')->unsigned()->nullable();
            $table->smallInteger('DeepskyHunter')->unsigned()->nullable();
            $table->smallInteger('Interstellarum')->unsigned()->nullable();

            $table->timestamps();

            $table->foreign('type')
                ->references('id')->on('target_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down()
    {
        Schema::dropIfExists('targets');
    }
}
