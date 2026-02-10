<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoonsTable extends Migration
{
    public function up()
    {
        Schema::create('moons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->index();
            $table->unsignedBigInteger('planet_id')->nullable()->index();
            $table->string('designation', 128)->nullable();
            $table->string('body_type', 64)->default('moon');
            $table->text('notes')->nullable();
            $table->timestamps();

            // foreign key is optional; keep simple for compatibility
        });
    }

    public function down()
    {
        Schema::dropIfExists('moons');
    }
}
