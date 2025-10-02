<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsteroidsTable extends Migration
{
    public function up()
    {
        Schema::create('asteroids', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->unique();
            $table->string('designation', 128)->nullable();
            $table->string('body_type', 32)->nullable();
            // RA/DEC for ephemeris reference (nullable as not all sources provide it)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asteroids');
    }
}
