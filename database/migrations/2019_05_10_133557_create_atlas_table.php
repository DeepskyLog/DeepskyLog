<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtlasTable extends Migration
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
                $table->string('code')->primary();
                $table->string('name');
            }
        );

        // Insert the atlases
        DB::table('atlases')->insert(
            [
                'code' => 'urano',
                'name' => 'Uranometria',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'urano_new',
                'name' => 'Uranometria (2nd edition)',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'sky',
                'name' => 'Sky Atlas',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'milleniumbase',
                'name' => 'Millenium Star Atlas',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'taki',
                'name' => 'Taki Atlas',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'psa',
                'name' => 'Pocket Sky Atlas',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'torresB',
                'name' => 'Triatlas B (Torres)',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'torresBC',
                'name' => 'Triatlas BC (Torres)',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'torresC',
                'name' => 'Triatlas C (Torres)',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'DSLDL',
                'name' => 'Deepskylog Detail Landscape',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'DSLDP',
                'name' => 'Deepskylog Detail Portrait',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'DSLLL',
                'name' => 'Deepskylog Lookup Landscape',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'DSLLP',
                'name' => 'Deepskylog Lookup Portrait',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'DSLOL',
                'name' => 'Deepskylog Overview Landscape',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'DSLOP',
                'name' => 'Deepskylog Overview Portrait',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'DeepskyHunter',
                'name' => 'Deep Sky Hunter',
            ]
        );

        DB::table('atlases')->insert(
            [
                'code' => 'Interstellarum',
                'name' => 'Interstellarum Deep Sky Atlas',
            ]
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
