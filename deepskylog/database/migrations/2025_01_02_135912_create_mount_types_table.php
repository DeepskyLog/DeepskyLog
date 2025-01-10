<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mount_types', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('name')->unique();
        });

        // Insert the instrument types
        DB::table('mount_types')->insert(
            [
                'name' => 'Alt/Azimuth',
            ]
        );

        DB::table('mount_types')->insert(
            [
                'name' => 'Equatorial',
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_types');
    }
};
