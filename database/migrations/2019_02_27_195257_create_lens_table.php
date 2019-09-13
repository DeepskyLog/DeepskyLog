<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->float('factor', 11);
            $table->unsignedInteger('user_id');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('observations')->default(0);

            $table->timestamps();

            $table->foreign('user_id')->references('id')
                ->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lens');
    }
}
