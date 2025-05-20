<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lens_makes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Insert the lens makes
        DB::table('lens_makes')->insert(
            [
                'name' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Angle Eyes',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Antares',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'APM',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Artesky',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'ASToptics',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Baader',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Bresser',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Celestron',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Explore Scientific',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'GSO',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Helios',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Leica',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Levenhuk',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Meade',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Omegon',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Orion',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Seben',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'SkyWatcher',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Takahashi',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Tal',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Tecnosky',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Tele Vue',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'TS-Optics',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Vixen',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'William Optics',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('lens_makes')->insert(
            [
                'name' => 'Zeiss',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('lens_makes');
    }
};
