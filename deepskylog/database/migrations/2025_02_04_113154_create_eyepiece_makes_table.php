<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eyepiece_makes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();

            $table->timestamps();
        });

        // Insert the eyepiece makes
        DB::table('eyepiece_makes')->insert(
            [
                'name' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => '365 Astronomy',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Agena',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Altair Astro',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Andrews',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Angeleyes',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Antares',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Apertura',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'APM',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Aquila',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Arcturus',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Artesky',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Astro Essentials',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Astro Hutech',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Astro Professional',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Astro Tech',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Astromania',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'ATC',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Auriga',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Baader Planetarium',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Bintel',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Brandon',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Bresser',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'BST',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Celestron',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Coronado',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Datyson',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Denkmeier Optical',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Edmund Optics',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Explore Scientific',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Founder Optics',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Fujiyama',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Gosky',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'GSO',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Harry Siebert Optics',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Hercules',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Kitakaru',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'KSON',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Lacerta',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Long Perng',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Lunt',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Masuyama',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'MaxVision',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Meade',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Meoptex',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Neewer',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Nikon',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Docter',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Oberwerk',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Omegon',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'OpticStar',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Orbinar',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Orion',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Ostara UK',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'OVL (First Light Optics)',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Pentax',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Saxon Australia',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Sky Optic',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Sky Rover',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => "Sky's the Limit",
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Skywatcher',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Solomark',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Stella Lyra',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Stellarvue',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Surplus Shed',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Svbony',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Takahashi',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Tecnosky',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Tele Vue',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Telescope Service',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Ursa Major',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Vixen',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'William Optics',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Wollensak',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Celtic Bird',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Deep Sky',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Leica',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Lomo',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Seben',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_makes')->insert(
            [
                'name' => 'Vision King',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('eyepiece_makes');
    }
};
