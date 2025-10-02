<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLegacyObjectnamesAndPartof extends Migration
{
    public function up()
    {
        Schema::create('objectnames', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('objectname', 128);
            $table->string('catalog', 128);
            $table->string('catindex', 128);
            $table->string('altname', 128);
            $table->dateTime('timestamp');

            $table->index('objectname', 'Index_objectname');
            $table->index(['catalog', 'catindex'], 'Index_catalog');
            $table->index('altname', 'Index_altname');
        });

        Schema::create('objectpartof', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('objectname', 255);
            $table->string('partofname', 255);
            $table->dateTime('timestamp');

            $table->index('objectname', 'Index_object');
            $table->index('partofname', 'Index_partof');
        });
    }

    public function down()
    {
        Schema::dropIfExists('objectpartof');
        Schema::dropIfExists('objectnames');
    }
}
