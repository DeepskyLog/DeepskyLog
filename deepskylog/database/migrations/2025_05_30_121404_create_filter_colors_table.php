<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Insert default colors
        DB::table('filter_colors')->insert(
            [
                'name' => 'No color',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Light Red',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Red',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Deep Red',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Orange',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Light Yellow',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Deep Yellow',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Yellow',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Yellow Green',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Light Green',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Green',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Medium Blue',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Pale Blue',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Blue',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Deep Blue',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('filter_colors')->insert(
            [
                'name' => 'Deep Violet',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_colors');
    }
};
