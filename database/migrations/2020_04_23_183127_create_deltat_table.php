<?php

use deepskylog\AstronomyLibrary\Imports\DeltaTImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeltaTTable extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        Schema::create(
            'delta_t',
            function (Blueprint $table) {
                $table->integer('year');
                $table->float('deltat');
            }
        );

        Excel::import(new DeltaTImport(), 'database/deltat.csv');
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down()
    {
        Schema::dropIfExists('delta_t');
    }
}
