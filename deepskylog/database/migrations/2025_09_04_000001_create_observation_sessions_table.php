<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('observation_sessions', function (Blueprint $table) {
            // Use an auto-incrementing primary key, but legacy IDs can still be
            // inserted explicitly when needed (see migration added to alter
            // existing tables). `increments` creates an unsigned INTEGER
            // AUTO_INCREMENT primary key.
            $table->increments('id');
            $table->string('name', 200)->default('');
            $table->string('observerid', 200)->default('');
            $table->dateTime('begindate');
            $table->dateTime('enddate');
            $table->unsignedInteger('locationid');
            $table->string('weather', 500)->default('');
            $table->string('equipment', 500)->default('');
            $table->string('comments', 5000)->nullable();
            $table->string('language', 255)->default('');
            $table->unsignedInteger('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('observation_sessions');
    }
};
