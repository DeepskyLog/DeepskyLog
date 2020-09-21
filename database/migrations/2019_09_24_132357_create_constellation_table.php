<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConstellationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'constellations', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
            }
        );

        // Insert the constellations
        DB::table('constellations')->insert(
            [
                'id' => 'AND',
                'name' => 'Andromeda',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'ANT',
                'name' => 'Antlia',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'APS',
                'name' => 'Apus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'AQR',
                'name' => 'Aquarius',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'AQL',
                'name' => 'Aquila',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'ARA',
                'name' => 'Ara',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'ARI',
                'name' => 'Aries',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'AUR',
                'name' => 'Auriga',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'BOO',
                'name' => 'Bootes',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CAE',
                'name' => 'Caelum',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CAM',
                'name' => 'Camelopardalis',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CNC',
                'name' => 'Cancer',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CVN',
                'name' => 'Canes Venatici',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CMA',
                'name' => 'Canis Major',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CMI',
                'name' => 'Canis Minor',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CAP',
                'name' => 'Capricornus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CAR',
                'name' => 'Carina',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CAS',
                'name' => 'Cassiopeia',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CEN',
                'name' => 'Centaurus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CEP',
                'name' => 'Cepheus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CET',
                'name' => 'Cetus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CHA',
                'name' => 'Chamaeleon',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CIR',
                'name' => 'Circinus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'COL',
                'name' => 'Columba',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'COM',
                'name' => 'Coma Berenices',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CRA',
                'name' => 'Corona Australis',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CRB',
                'name' => 'Corona Borealis',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CRV',
                'name' => 'Corvus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CRT',
                'name' => 'Crater',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CRU',
                'name' => 'Crux',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'CYG',
                'name' => 'Cygnus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'DEL',
                'name' => 'Delphinus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'DOR',
                'name' => 'Dorado',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'DRA',
                'name' => 'Draco',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'EQU',
                'name' => 'Equuleus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'ERI',
                'name' => 'Eridanus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'FOR',
                'name' => 'Fornax',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'GEM',
                'name' => 'Gemini',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'GRU',
                'name' => 'Grus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'HER',
                'name' => 'Hercules',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'HOR',
                'name' => 'Horologium',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'HYA',
                'name' => 'Hydra',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'HYI',
                'name' => 'Hydrus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'IND',
                'name' => 'Indus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'LAC',
                'name' => 'Lacerta',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'LEO',
                'name' => 'Leo',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'LMI',
                'name' => 'Leo Minor',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'LEP',
                'name' => 'Lepus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'LIB',
                'name' => 'Libra',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'LUP',
                'name' => 'Lupus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'LYN',
                'name' => 'Lynx',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'LYR',
                'name' => 'Lyra',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'MEN',
                'name' => 'Mensa',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'MIC',
                'name' => 'Microscopium',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'MON',
                'name' => 'Monoceros',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'MUS',
                'name' => 'Musca',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'NOR',
                'name' => 'Norma',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'OCT',
                'name' => 'Octans',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'OPH',
                'name' => 'Ophiuchus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'ORI',
                'name' => 'Orion',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'PAV',
                'name' => 'Pavo',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'PEG',
                'name' => 'Pegasus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'PER',
                'name' => 'Perseus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'PHE',
                'name' => 'Phoenix',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'PIC',
                'name' => 'Pictor',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'PSC',
                'name' => 'Pisces',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'PSA',
                'name' => 'Pisces Austrinus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'PUP',
                'name' => 'Puppis',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'PYX',
                'name' => 'Pyxis',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'RET',
                'name' => 'Reticulum',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'SGE',
                'name' => 'Sagitta',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'SGR',
                'name' => 'Sagittarius',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'SCO',
                'name' => 'Scorpius',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'SCL',
                'name' => 'Sculptor',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'SCT',
                'name' => 'Scutum',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'SER',
                'name' => 'Serpens',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'SEX',
                'name' => 'Sextans',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'TAU',
                'name' => 'Taurus',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'TEL',
                'name' => 'Telescopium',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'TRA',
                'name' => 'Triangulum Australe',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'TRI',
                'name' => 'Triangulum',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'TUC',
                'name' => 'Tucana',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'UMA',
                'name' => 'Ursa Major',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'UMI',
                'name' => 'Ursa Minor',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'VEL',
                'name' => 'Vela',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'VIR',
                'name' => 'Virgo',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'VOL',
                'name' => 'Volans',
            ]
        );

        DB::table('constellations')->insert(
            [
                'id' => 'VUL',
                'name' => 'Vulpecula',
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
        Schema::dropIfExists('constellations');
    }
}
