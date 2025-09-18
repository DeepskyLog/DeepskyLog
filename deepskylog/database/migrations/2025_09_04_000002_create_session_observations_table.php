<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sessionObservations', function (Blueprint $table) {
            $table->unsignedInteger('sessionid');
            $table->string('observationid', 55)->default('');
            $table->primary(['sessionid', 'observationid']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessionObservations');
    }
};
