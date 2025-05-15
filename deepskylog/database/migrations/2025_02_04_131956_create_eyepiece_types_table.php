<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eyepiece_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Add a foreign key pointing to the id column of the eyepiece_makes table
            $table->unsignedBigInteger('eyepiece_makes_id');
            $table->foreign('eyepiece_makes_id')->references('id')->on('eyepiece_makes');

            $table->timestamps();
        });

        // Insert the eyepiece types
        DB::table('eyepiece_types')->insert(
            [
                'name' => '',
                'eyepiece_makes_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Andromeda Extra Flat',
                'eyepiece_makes_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Columbus Ultra Wide Angle',
                'eyepiece_makes_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Magellan Wide Angle',
                'eyepiece_makes_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Orthoscopic',
                'eyepiece_makes_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Aspheric Zoom',
                'eyepiece_makes_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Enhanced Wide Angle',
                'eyepiece_makes_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Starguider Dual ED',
                'eyepiece_makes_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Wide Angle SWA',
                'eyepiece_makes_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle',
                'eyepiece_makes_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Lightwave Hyperwide Premium',
                'eyepiece_makes_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Lightwave LER Planetary',
                'eyepiece_makes_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Lightwave LER Premium',
                'eyepiece_makes_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UltraFlat',
                'eyepiece_makes_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'ED',
                'eyepiece_makes_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Series 500',
                'eyepiece_makes_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '70° Series Flatfield',
                'eyepiece_makes_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'LER 2"',
                'eyepiece_makes_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle',
                'eyepiece_makes_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle Red or Gold Ring',
                'eyepiece_makes_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWA',
                'eyepiece_makes_id' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'W70',
                'eyepiece_makes_id' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'XWA HDC',
                'eyepiece_makes_id' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Wide Angle',
                'eyepiece_makes_id' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Hi-FW',
                'eyepiece_makes_id' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ultra Flat Field',
                'eyepiece_makes_id' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UW',
                'eyepiece_makes_id' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'XWA HDC',
                'eyepiece_makes_id' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Zoom',
                'eyepiece_makes_id' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SW',
                'eyepiece_makes_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ebony',
                'eyepiece_makes_id' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'EWA',
                'eyepiece_makes_id' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Planetary SW',
                'eyepiece_makes_id' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium Flat Field',
                'eyepiece_makes_id' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super ED',
                'eyepiece_makes_id' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ultra Flat',
                'eyepiece_makes_id' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Aspherical Zoom',
                'eyepiece_makes_id' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Orthoscopic',
                'eyepiece_makes_id' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Scopetech orthoscopic',
                'eyepiece_makes_id' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Long Eye Relief',
                'eyepiece_makes_id' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Paradigm Dual ED',
                'eyepiece_makes_id' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium Flat Field',
                'eyepiece_makes_id' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'XWA HDC',
                'eyepiece_makes_id' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Kellner',
                'eyepiece_makes_id' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Planetary',
                'eyepiece_makes_id' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium Flatfield',
                'eyepiece_makes_id' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Plossl',
                'eyepiece_makes_id' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWA',
                'eyepiece_makes_id' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UltraWide 80',
                'eyepiece_makes_id' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA-NEW',
                'eyepiece_makes_id' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Kellner',
                'eyepiece_makes_id' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'N',
                'eyepiece_makes_id' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl (EX)',
                'eyepiece_makes_id' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium Flat Field',
                'eyepiece_makes_id' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWA',
                'eyepiece_makes_id' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'WA',
                'eyepiece_makes_id' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Classic Ortho',
                'eyepiece_makes_id' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Hyperion',
                'eyepiece_makes_id' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Hyperion Aspheric',
                'eyepiece_makes_id' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Morpheus',
                'eyepiece_makes_id' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Mark IV Zoom w/ click-stops',
                'eyepiece_makes_id' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plössl',
                'eyepiece_makes_id' => 21,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium LER',
                'eyepiece_makes_id' => 21,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SuperView',
                'eyepiece_makes_id' => 21,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Brandon Orthoscopic',
                'eyepiece_makes_id' => 22,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 23,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'LER Zoom',
                'eyepiece_makes_id' => 23,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'DLX LER Zoom',
                'eyepiece_makes_id' => 23,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '58 degree Series',
                'eyepiece_makes_id' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Flat Field',
                'eyepiece_makes_id' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'E-Lux Kellner',
                'eyepiece_makes_id' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Luminos',
                'eyepiece_makes_id' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Omni',
                'eyepiece_makes_id' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ultima',
                'eyepiece_makes_id' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ultima Edge',
                'eyepiece_makes_id' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'X-Cel LX',
                'eyepiece_makes_id' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 75,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'CeMax',
                'eyepiece_makes_id' => 26,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 27,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl w/ Gold ring',
                'eyepiece_makes_id' => 27,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle Long Eye Relief',
                'eyepiece_makes_id' => 27,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Gold Band Zoom',
                'eyepiece_makes_id' => 27,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Silver Band Zoom',
                'eyepiece_makes_id' => 27,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 76,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'D14-pr',
                'eyepiece_makes_id' => 28,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'D21-pr',
                'eyepiece_makes_id' => 28,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'LOA 3D-Pair of 1 3D and 1 Neutral',
                'eyepiece_makes_id' => 28,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'LOA Neutral-ea',
                'eyepiece_makes_id' => 28,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl-ea',
                'eyepiece_makes_id' => 28,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl-pr',
                'eyepiece_makes_id' => 28,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'RKE',
                'eyepiece_makes_id' => 29,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '100 Series',
                'eyepiece_makes_id' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '3" Series',
                'eyepiece_makes_id' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '52 Series',
                'eyepiece_makes_id' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '62 Series',
                'eyepiece_makes_id' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '68 Series',
                'eyepiece_makes_id' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '82 LER Series',
                'eyepiece_makes_id' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '82 Series',
                'eyepiece_makes_id' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Alien',
                'eyepiece_makes_id' => 31,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Marvel',
                'eyepiece_makes_id' => 31,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'HD Orthoscopic',
                'eyepiece_makes_id' => 32,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 33,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWA',
                'eyepiece_makes_id' => 33,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 34,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Superview',
                'eyepiece_makes_id' => 34,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Monocentric',
                'eyepiece_makes_id' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Observatory Elite Series',
                'eyepiece_makes_id' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Observatory Series',
                'eyepiece_makes_id' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Performance +',
                'eyepiece_makes_id' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Planesphere BK7',
                'eyepiece_makes_id' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Planesphere Fused Silica',
                'eyepiece_makes_id' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Starsplitter SS3 Series',
                'eyepiece_makes_id' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Starsplitter SS4 Series',
                'eyepiece_makes_id' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ultra',
                'eyepiece_makes_id' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plano',
                'eyepiece_makes_id' => 36,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plano Zoom',
                'eyepiece_makes_id' => 36,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Aspherical Zoom',
                'eyepiece_makes_id' => 77,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 78,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'RPL',
                'eyepiece_makes_id' => 37,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '5-element eyepiece',
                'eyepiece_makes_id' => 38,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Abbe Orthoscopic',
                'eyepiece_makes_id' => 38,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Kellner',
                'eyepiece_makes_id' => 38,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'LE Abbe Orthoscopic',
                'eyepiece_makes_id' => 38,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Plössl',
                'eyepiece_makes_id' => 38,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ultra Flat Field',
                'eyepiece_makes_id' => 38,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium Flat Field',
                'eyepiece_makes_id' => 39,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Planetary',
                'eyepiece_makes_id' => 39,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWA70',
                'eyepiece_makes_id' => 39,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 39,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'WA',
                'eyepiece_makes_id' => 39,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '80° Series LER',
                'eyepiece_makes_id' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'LER',
                'eyepiece_makes_id' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plössl',
                'eyepiece_makes_id' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle 68°',
                'eyepiece_makes_id' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Field',
                'eyepiece_makes_id' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Lanthanum Orthoscopic',
                'eyepiece_makes_id' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Solar Eyepieces',
                'eyepiece_makes_id' => 41,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Solar Eyepieces Zoom',
                'eyepiece_makes_id' => 41,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Masuyama',
                'eyepiece_makes_id' => 42,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'MOP',
                'eyepiece_makes_id' => 42,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Orthoscopic',
                'eyepiece_makes_id' => 42,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 43,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWA',
                'eyepiece_makes_id' => 43,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 43,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle',
                'eyepiece_makes_id' => 43,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Series 5000 PWA',
                'eyepiece_makes_id' => 44,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Series 5000 UHD',
                'eyepiece_makes_id' => 44,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Series 4000 Super Plossl',
                'eyepiece_makes_id' => 44,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Series 4000 Zoom',
                'eyepiece_makes_id' => 44,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 44,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Acouto Zoom',
                'eyepiece_makes_id' => 44,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Kellner',
                'eyepiece_makes_id' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Plössl',
                'eyepiece_makes_id' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 46,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle',
                'eyepiece_makes_id' => 46,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 46,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'NAV-HW',
                'eyepiece_makes_id' => 47,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'NAV-SW',
                'eyepiece_makes_id' => 47,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 48,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Binocular Eyepieces XL',
                'eyepiece_makes_id' => 49,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Cronus',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Flatfield',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Flatfield ED',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'LE Planetary',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Oberon',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ortho',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Panorama II',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium Flatfield',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Redline',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super LE',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Plossl',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWA',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Plossl Zoom',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Magnum Zoom',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium Zoom',
                'eyepiece_makes_id' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Long Eye Relief Wide Angle',
                'eyepiece_makes_id' => 51,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'XL Ultra Wide Angle',
                'eyepiece_makes_id' => 51,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'XS Super Wide Angle',
                'eyepiece_makes_id' => 51,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'ED',
                'eyepiece_makes_id' => 51,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Plossl',
                'eyepiece_makes_id' => 51,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 51,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 52,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Edge-On Planetary',
                'eyepiece_makes_id' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'EF',
                'eyepiece_makes_id' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'E-Series',
                'eyepiece_makes_id' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Expanse',
                'eyepiece_makes_id' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'LHD',
                'eyepiece_makes_id' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Q70',
                'eyepiece_makes_id' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Sirius',
                'eyepiece_makes_id' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ultra Flat Field',
                'eyepiece_makes_id' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'E-Series Zoom',
                'eyepiece_makes_id' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Lanthanum Zoom',
                'eyepiece_makes_id' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 53,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Flat Field',
                'eyepiece_makes_id' => 54,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'HR Plossl',
                'eyepiece_makes_id' => 54,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'S-HR Plossl',
                'eyepiece_makes_id' => 54,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWA',
                'eyepiece_makes_id' => 54,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Extra Flat',
                'eyepiece_makes_id' => 55,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Nirvana 82°',
                'eyepiece_makes_id' => 55,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Panaview',
                'eyepiece_makes_id' => 55,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Hyperflex Zoom',
                'eyepiece_makes_id' => 55,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SMC-XW',
                'eyepiece_makes_id' => 56,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SMC-XW85',
                'eyepiece_makes_id' => 56,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SMC-XW-R',
                'eyepiece_makes_id' => 56,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'XF ED',
                'eyepiece_makes_id' => 56,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'XF Zoom',
                'eyepiece_makes_id' => 56,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'XL Zoom',
                'eyepiece_makes_id' => 56,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Cielo HD',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'ED1',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'ED2',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'ED3',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Kellner',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Silver Plossl',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Plossl',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super SR',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Wide Angle',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWA',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWA 82',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWE 100',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'WA',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Zoom',
                'eyepiece_makes_id' => 79,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 79,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '2" LER',
                'eyepiece_makes_id' => 58,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Elyth',
                'eyepiece_makes_id' => 58,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Extra Flat',
                'eyepiece_makes_id' => 58,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Panaview',
                'eyepiece_makes_id' => 58,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 58,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 58,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Hyperflex Zoom',
                'eyepiece_makes_id' => 58,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Hi-FW',
                'eyepiece_makes_id' => 59,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium Flat Field',
                'eyepiece_makes_id' => 59,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ultra Flat Field',
                'eyepiece_makes_id' => 59,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 59,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'WA',
                'eyepiece_makes_id' => 59,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'XWA',
                'eyepiece_makes_id' => 59,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Zoom',
                'eyepiece_makes_id' => 59,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plössl (Silver)',
                'eyepiece_makes_id' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'ExtraFlat Wide Angle',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Kellner',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'LET',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Long Eye Relief',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Nirvana 82',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'PanaView',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Planetary',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Sky Panorama',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Lens',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Plossl',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Wide Angle',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Hyperflex Zoom',
                'eyepiece_makes_id' => 61,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Kellner',
                'eyepiece_makes_id' => 62,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plössl',
                'eyepiece_makes_id' => 62,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 62,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '68° LER WA',
                'eyepiece_makes_id' => 63,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => '80° LER UWA',
                'eyepiece_makes_id' => 63,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Long Eye Relief',
                'eyepiece_makes_id' => 63,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'RPL',
                'eyepiece_makes_id' => 63,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Superview',
                'eyepiece_makes_id' => 63,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ultra Flat',
                'eyepiece_makes_id' => 63,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 63,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Optimus',
                'eyepiece_makes_id' => 64,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Planetary',
                'eyepiece_makes_id' => 64,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 64,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Huygens',
                'eyepiece_makes_id' => 65,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Kellner',
                'eyepiece_makes_id' => 65,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 65,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Erfle',
                'eyepiece_makes_id' => 65,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Aspheric Wide Angle',
                'eyepiece_makes_id' => 66,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Long Eye Relief Wide Angle Gold Ring',
                'eyepiece_makes_id' => 66,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Long Eye Relief Wide Angle Red Ring',
                'eyepiece_makes_id' => 66,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Planetary',
                'eyepiece_makes_id' => 66,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 66,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Wide Angle',
                'eyepiece_makes_id' => 66,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ultra Flat Field',
                'eyepiece_makes_id' => 66,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle',
                'eyepiece_makes_id' => 66,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 66,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Abbe Orthoscopic',
                'eyepiece_makes_id' => 67,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'LE',
                'eyepiece_makes_id' => 67,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Starbase Kellner',
                'eyepiece_makes_id' => 67,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Starbase Orthoscopic',
                'eyepiece_makes_id' => 67,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'TOE',
                'eyepiece_makes_id' => 67,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'TPL',
                'eyepiece_makes_id' => 67,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Flat Field',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ortho Wide Field',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Planetary ED',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Planetary HR',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium Flat Field',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Plossl',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Wide Angle',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Superwide HD',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UltraFlatField',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'XWA',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Aspheric Zoom',
                'eyepiece_makes_id' => 68,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Apollo',
                'eyepiece_makes_id' => 69,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'DeLite',
                'eyepiece_makes_id' => 69,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Delos',
                'eyepiece_makes_id' => 69,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ethos',
                'eyepiece_makes_id' => 69,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Ethos SX',
                'eyepiece_makes_id' => 69,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Nagler',
                'eyepiece_makes_id' => 69,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Panoptic',
                'eyepiece_makes_id' => 69,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 69,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Radian',
                'eyepiece_makes_id' => 69,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Nagler Zoom',
                'eyepiece_makes_id' => 69,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'ED',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Expanse ED',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Flat Field',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Planetary HR',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium Flat Field',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'RK',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Standard Plossl',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Super Plossl',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Superview for Camera',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SWA',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UFL',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWA AF82',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'UWAN 82',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Wide Angle for Camera',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'XWA',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Deluxe Zoom',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Premium Zoom',
                'eyepiece_makes_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'FMC Flatfield',
                'eyepiece_makes_id' => 71,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'NPL',
                'eyepiece_makes_id' => 72,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'SLV',
                'eyepiece_makes_id' => 72,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Swan',
                'eyepiece_makes_id' => 73,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Widefield',
                'eyepiece_makes_id' => 73,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Huygens',
                'eyepiece_makes_id' => 74,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Kellner',
                'eyepiece_makes_id' => 74,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Plossl',
                'eyepiece_makes_id' => 74,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Erfle',
                'eyepiece_makes_id' => 74,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('eyepiece_types')->insert(
            [
                'name' => 'Zoom',
                'eyepiece_makes_id' => 80,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('eyepiece_types');
    }
};
