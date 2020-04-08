<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstrumentTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'instrument_types', function (Blueprint $table) {
                $table->integer('id')->primary();
                $table->string('type');
            }
        );

        // Insert the instrument types
        DB::table('instrument_types')->insert(
            array(
                'id' => 0,
                'type' => "Naked Eye"
            )
        );

        DB::table('instrument_types')->insert(
            array(
                'id' => 1,
                'type' => "Binoculars"
            )
        );

        DB::table('instrument_types')->insert(
            array(
                'id' => 2,
                'type' => "Refractor"
            )
        );

        DB::table('instrument_types')->insert(
            array(
                'id' => 3,
                'type' => "Reflector"
            )
        );

        DB::table('instrument_types')->insert(
            array(
                'id' => 4,
                'type' => "Finderscope"
            )
        );

        DB::table('instrument_types')->insert(
            array(
                'id' => 5,
                'type' => "Other"
            )
        );

        DB::table('instrument_types')->insert(
            array(
                'id' => 6,
                'type' => "Cassegrain"
            )
        );

        DB::table('instrument_types')->insert(
            array(
                'id' => 7,
                'type' => "Kutter"
            )
        );

        DB::table('instrument_types')->insert(
            array(
                'id' => 8,
                'type' => "Maksutov"
            )
        );

        DB::table('instrument_types')->insert(
            array(
                'id' => 9,
                'type' => "Schmidt Cassegrain"
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
        Schema::dropIfExists('instrument_types');
    }
}
