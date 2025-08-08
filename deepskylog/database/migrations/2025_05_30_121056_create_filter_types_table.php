<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Insert the filter makes
        DB::table('filter_types')->insert(
            [
                'name' => 'Other',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_types')->insert(
            [
                'name' => 'Broadband',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_types')->insert(
            [
                'name' => 'Narrowband',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_types')->insert(
            [
                'name' => 'O-III',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_types')->insert(
            [
                'name' => 'H-Beta',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_types')->insert(
            [
                'name' => 'H-Alpha',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_types')->insert(
            [
                'name' => 'Color',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_types')->insert(
            [
                'name' => 'Neutral Density',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_types')->insert(
            [
                'name' => 'Corrective',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_types');
    }
};
