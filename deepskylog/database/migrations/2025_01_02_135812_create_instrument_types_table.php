<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrument_types', function (Blueprint $table) {
            $table->id()->primary()->startingValue(0);
            $table->string('name')->unique();
            $table->boolean('flip_image')->default(false);
            $table->boolean('flop_image')->default(false);
        });

        // Insert the instrument types
        DB::table('instrument_types')->insert(
            [
                'name' => 'Naked Eye',
                'flip_image' => false,
                'flop_image' => false,
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'name' => 'Binoculars',
                'flip_image' => false,
                'flop_image' => false,
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'name' => 'Refractor',
                'flip_image' => true,
                'flop_image' => false,
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'name' => 'Reflector',
                'flip_image' => true,
                'flop_image' => true,
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'name' => 'Finderscope',
                'flip_image' => true,
                'flop_image' => false,
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'name' => 'Other',
                'flip_image' => true,
                'flop_image' => false,
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'name' => 'Cassegrain',
                'flip_image' => true,
                'flop_image' => false,
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'name' => 'Kutter',
                'flip_image' => true,
                'flop_image' => true,
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'name' => 'Maksutov',
                'flip_image' => true,
                'flop_image' => false,
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'name' => 'Schmidt Cassegrain',
                'flip_image' => true,
                'flop_image' => false,
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_types');
    }
};
