<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_makes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Insert the filter makes
        DB::table('filter_makes')->insert(
            [
                'name' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'ASH',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Astronomik',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Astroprofessional',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Atik',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Baader Planetarium',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Celestron',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'CrystalVue',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'DMG',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Explore Scientific',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'GSO',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Lumicon',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Meade',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Optolong',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Omega Optical',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Omegon',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Orion',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Ostara',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'SkyWatcher',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Svbony',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Tele Vue',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'Thousand Oaks',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'TS-Optics',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_makes')->insert(
            [
                'name' => 'ZWO',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_makes');
    }
};
