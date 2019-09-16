<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEyepiecesTable extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        Schema::create(
            'eyepieces',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 255);
                $table->float('focalLength');
                $table->unsignedInteger('apparentFOV');
                $table->float('maxFocalLength')->nullable();
                $table->unsignedInteger('user_id');
                $table->boolean('active')->default(true);
                $table->unsignedInteger('observations')->default(0);

                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')
                    ->onDelete('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down()
    {
        Schema::dropIfExists('eyepieces');
    }
}
