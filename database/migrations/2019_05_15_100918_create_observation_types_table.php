<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObservationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('observation_types', function (Blueprint $table) {
            $table->string('type')->primary();
            $table->string('name');
        });

        // Insert the observation types
        DB::table('observation_types')->insert(
            array(
                'type' => 'ds',
                'name' => "Deepsky"
            )
        );

        DB::table('observation_types')->insert(
            array(
                'type' => 'comets',
                'name' => "Comets"
            )
        );

        DB::table('observation_types')->insert(
            array(
                'type' => 'planets',
                'name' => "Planets"
            )
        );

        DB::table('observation_types')->insert(
            array(
                'type' => 'double',
                'name' => "Double Stars"
            )
        );

        DB::table('observation_types')->insert(
            array(
                'type' => 'sun',
                'name' => "Sun"
            )
        );

        DB::table('observation_types')->insert(
            array(
                'type' => 'moon',
                'name' => "Moon"
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('observation_types');
    }
}
