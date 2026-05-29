<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('stars')) {
            return;
        }

        Schema::create('stars', function (Blueprint $table) {
            $table->id();
            $table->char('source_key', 40)->unique();
            $table->string('source_table', 16)->index();
            $table->string('type', 7)->nullable();
            $table->string('constellation_code', 3)->nullable()->index();
            $table->string('bayer', 3)->nullable();
            $table->string('bayer_suffix', 2)->nullable();
            $table->string('flamsteed', 3)->nullable();
            $table->string('name', 25)->nullable()->index();
            $table->integer('ra_mas')->nullable();
            $table->integer('dec_mas')->nullable();
            $table->double('ra_hours')->nullable();
            $table->double('ra_degrees')->nullable();
            $table->double('decl_degrees')->nullable();
            $table->integer('vmag_int')->nullable()->index();
            $table->decimal('vmag', 5, 2)->nullable()->index();
            $table->string('spectral_type', 25)->nullable();

            $table->index(['decl_degrees', 'ra_degrees']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stars');
    }
};