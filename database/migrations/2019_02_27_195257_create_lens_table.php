<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->float('factor', 11);
            $table->unsignedInteger('user_id');
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->foreign('user_id')->references('id')
                ->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('lens');
    }
}
