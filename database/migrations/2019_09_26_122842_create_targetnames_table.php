<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetNamesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('target_names', function (Blueprint $table) {
            $table->string('objectname', 128);
            $table->string('catalog', 128)->nullable();
            $table->string('catindex', 128)->nullable();
            $table->string('altname', 128);
            $table->index('objectname', 'Index_objectname');
            $table->index(['catalog', 'catindex'], 'Index_catalog');
            $table->index('altname', 'Index_altname');
            $table->timestamps();

            $table->foreign('objectname')
                ->references('name')->on('targets');
        });

        // Add the translations for the sun, moon and planets
        \App\TargetName::create(
            [
                'objectname' => 'Sun',
                'catalog' => '',
                'catindex' => 'Sol',
                'altname' => 'Sol',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Moon',
                'catalog' => '',
                'catindex' => 'Månen',
                'altname' => 'Månen',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Sun',
                'catalog' => '',
                'catindex' => 'Zon',
                'altname' => 'Zon',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Moon',
                'catalog' => '',
                'catindex' => 'Maan',
                'altname' => 'Maan',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Mercury',
                'catalog' => '',
                'catindex' => 'Mercurius',
                'altname' => 'Mercurius',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Saturn',
                'catalog' => '',
                'catindex' => 'Saturnus',
                'altname' => 'Saturnus',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Neptune',
                'catalog' => '',
                'catindex' => 'Neptunus',
                'altname' => 'Neptunus',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Sun',
                'catalog' => '',
                'catindex' => 'Sonne',
                'altname' => 'Sonne',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Moon',
                'catalog' => '',
                'catindex' => 'Mond',
                'altname' => 'Mond',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Mercury',
                'catalog' => '',
                'catindex' => 'Merkur',
                'altname' => 'Merkur',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Neptune',
                'catalog' => '',
                'catindex' => 'Neptun',
                'altname' => 'Neptun',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Sun',
                'catalog' => '',
                'catindex' => 'Soleil',
                'altname' => 'Soleil',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Moon',
                'catalog' => '',
                'catindex' => 'Lune',
                'altname' => 'Lune',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Mercury',
                'catalog' => '',
                'catindex' => 'Mercure',
                'altname' => 'Mercure',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Saturn',
                'catalog' => '',
                'catindex' => 'Saturne',
                'altname' => 'Saturne',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Moon',
                'catalog' => '',
                'catindex' => 'Luna',
                'altname' => 'Luna',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Mercury',
                'catalog' => '',
                'catindex' => 'Mercurio',
                'altname' => 'Mercurio',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Mars',
                'catalog' => '',
                'catindex' => 'Marte',
                'altname' => 'Marte',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Saturn',
                'catalog' => '',
                'catindex' => 'Saturno',
                'altname' => 'Saturno',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Uranus',
                'catalog' => '',
                'catindex' => 'Urano',
                'altname' => 'Urano',
            ]
        );

        \App\TargetName::create(
            [
                'objectname' => 'Neptune',
                'catalog' => '',
                'catindex' => 'Neptuno',
                'altname' => 'Neptuno',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('target_names');
    }
}
