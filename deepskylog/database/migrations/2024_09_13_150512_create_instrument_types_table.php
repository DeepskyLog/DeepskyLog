<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('instrument_types', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('type');
        });

        // Insert the instrument types
        DB::table('instrument_types')->insert(
            [
                'type' => 'Naked Eye',
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'type' => 'Binoculars',
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'type' => 'Refractor',
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'type' => 'Reflector',
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'type' => 'Finderscope',
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'type' => 'Other',
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'type' => 'Cassegrain',
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'type' => 'Kutter',
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'type' => 'Maksutov',
            ]
        );

        DB::table('instrument_types')->insert(
            [
                'type' => 'Schmidt Cassegrain',
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_types');
    }
};
