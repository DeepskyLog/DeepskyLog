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
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_makes');
    }
};
