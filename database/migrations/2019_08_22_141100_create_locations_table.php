<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'locations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->float('longitude', 8, 5);
                $table->float('latitude', 8, 5);
                $table->integer('elevation');
                $table->string('country');
                $table->string('timezone');
                $table->float('limitingMagnitude')->nullable();
                $table->float('skyBackground')->nullable();
                $table->unsignedSmallInteger('bortle')->nullable();
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
        Schema::dropIfExists('locations');
    }
}
