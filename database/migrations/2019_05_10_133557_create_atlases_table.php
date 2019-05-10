<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtlasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'atlases', function (Blueprint $table) {
                $table->string('code');
                $table->string('name');
            }
        );

        // Insert the atlases
        DB::table('atlases')->insert(
            array(
                'code' => 'urano',
                'name' => "Uranometria"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'urano_new',
                'name' => "Uranometria (2nd edition)"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'sky',
                'name' => "Sky Atlas"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'milleniumbase',
                'name' => "Millenium Star Atlas"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'taki',
                'name' => "Taki Atlas"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'psa',
                'name' => "Pocket Sky Atlas"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'torresB',
                'name' => "Triatlas B (Torres)"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'torresBC',
                'name' => "Triatlas BC (Torres)"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'torresC',
                'name' => "Triatlas C (Torres)"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'DSLDL',
                'name' => "Deepskylog Detail Landscape"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'DSLDP',
                'name' => "Deepskylog Detail Portrait"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'DSLLL',
                'name' => "Deepskylog Lookup Landscape"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'DSLLP',
                'name' => "Deepskylog Lookup Portrait"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'DSLOL',
                'name' => "Deepskylog Overview Landscape"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'DSLOP',
                'name' => "Deepskylog Overview Portrait"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'DeepskyHunter',
                'name' => "Deep Sky Hunter"
            )
        );

        DB::table('atlases')->insert(
            array(
                'code' => 'Interstellarum',
                'name' => "Interstellarum Deep Sky Atlas"
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
        Schema::dropIfExists('atlases');
    }
}
