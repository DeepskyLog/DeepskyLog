<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilterTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'filter_types', function (Blueprint $table) {
                $table->integer('id');
                $table->string('type');
            }
        );

        // Insert the observation types
        DB::table('filter_types')->insert(
            array(
                'id' => 0,
                'type' => "Other filter"
            )
        );

        DB::table('filter_types')->insert(
            array(
                'id' => 1,
                'type' => "Broadband filter"
            )
        );

        DB::table('filter_types')->insert(
            array(
                'id' => 2,
                'type' => "Narrowband filter"
            )
        );

        DB::table('filter_types')->insert(
            array(
                'id' => 3,
                'type' => "O-III filter"
            )
        );

        DB::table('filter_types')->insert(
            array(
                'id' => 4,
                'type' => "H beta filter"
            )
        );

        DB::table('filter_types')->insert(
            array(
                'id' => 5,
                'type' => "H alpha filter"
            )
        );

        DB::table('filter_types')->insert(
            array(
                'id' => 6,
                'type' => "Color filter"
            )
        );

        DB::table('filter_types')->insert(
            array(
                'id' => 7,
                'type' => "Neutral filter"
            )
        );

        DB::table('filter_types')->insert(
            array(
                'id' => 8,
                'type' => "Corrective filter"
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
        Schema::dropIfExists('filter_types');
    }
}
