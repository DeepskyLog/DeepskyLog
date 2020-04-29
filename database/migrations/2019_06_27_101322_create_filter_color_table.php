<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilterColorTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'filter_colors',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('color');
            }
        );

        // Insert the observation types
        DB::table('filter_colors')->insert(
            [
                'id' => 1,
                'color' => 'Light red',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 2,
                'color' => 'Red',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 3,
                'color' => 'Deep red',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 4,
                'color' => 'Orange',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 5,
                'color' => 'Light yellow',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 6,
                'color' => 'Deep yellow',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 7,
                'color' => 'Yellow',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 8,
                'color' => 'Yellow-Green',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 9,
                'color' => 'Light green',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 10,
                'color' => 'Green',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 11,
                'color' => 'Medium blue',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 12,
                'color' => 'Pale blue',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 13,
                'color' => 'Blue',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 14,
                'color' => 'Deep blue',
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'id' => 15,
                'color' => 'Deep Violet',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('filter_colors');
    }
}
