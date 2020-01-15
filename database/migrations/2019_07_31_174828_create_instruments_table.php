<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstrumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        Schema::create(
            'instruments',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 255);
                $table->float('diameter');
                $table->unsignedInteger('type');
                $table->float('fd')->nullable();
                $table->unsignedInteger('fixedMagnification')->nullable();
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
        Schema::dropIfExists('instruments');
    }
}
