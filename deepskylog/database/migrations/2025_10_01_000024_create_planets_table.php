<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanetsTable extends Migration
{
    public function up()
    {
        Schema::create('planets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->unique();
            $table->string('designation', 128)->nullable();
            $table->string('body_type', 32)->nullable();
            $table->decimal('ra', 10, 6)->nullable();
            $table->decimal('decl', 10, 6)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('planets');
    }
}
