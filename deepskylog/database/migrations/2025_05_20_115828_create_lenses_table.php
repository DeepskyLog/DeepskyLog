<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('make_id')->constrained('lens_makes', 'id');
            $table->string('name', 255);
            $table->float('factor');
            $table->boolean('active')->default(true);
            $table->boolean('lensactive')->virtualAs('active');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->string('observer');
            $table->integer('observations', unsigned: true)->default(0);
            $table->string('picture')->nullable();
            $table->string('slug');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lenses');
    }
};
