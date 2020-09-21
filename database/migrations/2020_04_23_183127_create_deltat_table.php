<?php

use deepskylog\AstronomyLibrary\Imports\DeltaTImport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class CreateDeltaTTable extends Migration
{
    /**
     * Run the migrations.
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
     */
    public function down()
    {
        Schema::dropIfExists('delta_t');
    }
}
