<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrument_makes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
        });

        // Insert the instrument makes
        DB::table('instrument_makes')->insert(
            [
                'name' => '',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Obsession',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'SkyWatcher',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Celestron',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Analog Sky',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Meade',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Orion',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Explore Scientific',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Vixen',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Takahashi',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Tele Vue',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'William Optics',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Borg',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Stellarvue',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Astro-Tech',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'TEC',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Astro-Physics',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'PlaneWave',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Hofheim Instruments',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Coronado',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Omegon',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'APM',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Bresser',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'GSO',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Canon',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Sumerian',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Zeiss',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Swarovski',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'ZWO',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Nikon',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'TMB',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Fujinon',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Pentax',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'SVBony',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Polarex',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Lichtenknecker',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Leica',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Intes',
            ]
        );

        DB::table('instrument_makes')->insert(
            [
                'name' => 'Dark Star',
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_makes');
    }
};
