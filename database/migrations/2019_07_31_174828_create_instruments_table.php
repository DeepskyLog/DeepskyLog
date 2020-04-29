<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstrumentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'instruments',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->float('diameter');
                $table->unsignedInteger('type');
                $table->float('fd')->nullable();
                $table->unsignedInteger('fixedMagnification')->nullable();
                $table->unsignedInteger('user_id');
                $table->boolean('active')->default(true);

                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')
                    ->onDelete('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('instruments');
    }
}
