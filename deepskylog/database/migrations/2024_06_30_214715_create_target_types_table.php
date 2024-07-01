<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetTypesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('target_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('type');
            $table->string('observation_type');

            //            $table->foreign('observation_type')
            //                ->references('type')->on('observation_types');
        });

        // Insert the target types
        DB::table('target_types')->insert(
            [
                'id' => 'ASTER',
                'type' => 'Asterism',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'BRTNB',
                'type' => 'Bright nebula',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'CLANB',
                'type' => 'Cluster with nebulosity',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'DRKNB',
                'type' => 'Dark nebula',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'EMINB',
                'type' => 'Emission nebula',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'ENRNN',
                'type' => 'Emission and Reflection nebula',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'ENSTR',
                'type' => 'Emission nebula around a star',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'GACAN',
                'type' => 'Cluster with nebulosity in galaxy',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'GXAGC',
                'type' => 'Globular cluster in galaxy',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'GALCL',
                'type' => 'Galaxy cluster',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'GALXY',
                'type' => 'Galaxy',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'GLOCL',
                'type' => 'Globular cluster',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'GXADN',
                'type' => 'Diffuse nebula in galaxy',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'LMCCN',
                'type' => 'Cluster with nebulosity in LMC',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'LMCDN',
                'type' => 'Diffuse nebula in LMC',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'LMCGC',
                'type' => 'Globular cluster in LMC',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'LMCOC',
                'type' => 'Open cluster in LMC',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'SMCCN',
                'type' => 'Cluster with nebulosity in SMC',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'SMCDN',
                'type' => 'Diffuse nebula in SMC',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'SMCGC',
                'type' => 'Globular cluster in SMC',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'SMCOC',
                'type' => 'Open cluster in SMC',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'HII',
                'type' => 'H-II',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'NONEX',
                'type' => 'Nonexistent',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'OPNCL',
                'type' => 'Open cluster',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'PLNNB',
                'type' => 'Planetary nebula',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'REFNB',
                'type' => 'Reflection nebula',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'RNHII',
                'type' => 'Reflection nebula and H-II',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'SNOVA',
                'type' => 'Supernova',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'SNREM',
                'type' => 'Supernova remnant',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'STNEB',
                'type' => 'Nebula around star',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'QUASR',
                'type' => 'Quasar',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'WRNEB',
                'type' => 'Wolf Rayet nebula',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'REST',
                'type' => 'Rest',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'DS',
                'type' => 'Double Star',
                'observation_type' => 'double',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'COMET',
                'type' => 'Comet',
                'observation_type' => 'comets',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'PLANET',
                'type' => 'Planet',
                'observation_type' => 'planets',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'SUN',
                'type' => 'Sun',
                'observation_type' => 'sun',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'CRATER',
                'type' => 'Crater',
                'observation_type' => 'moon',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'SEA',
                'type' => 'Sea',
                'observation_type' => 'moon',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'MOUNTAIN',
                'type' => 'Mountain',
                'observation_type' => 'moon',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'VALLEY',
                'type' => 'Valley',
                'observation_type' => 'moon',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'OTHER',
                'type' => 'Other feature',
                'observation_type' => 'moon',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'AA1STAR',
                'type' => 'Star',
                'observation_type' => 'ds',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'AA3STAR',
                'type' => '3 Stars',
                'observation_type' => 'double',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'AA4STAR',
                'type' => '4 Stars',
                'observation_type' => 'double',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'AA8STAR',
                'type' => '8 Stars',
                'observation_type' => 'double',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('target_types');
    }
}
