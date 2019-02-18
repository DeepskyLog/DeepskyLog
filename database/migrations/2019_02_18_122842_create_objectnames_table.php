<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObjectnamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('objectnames', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->string('objectname', 128);
            $table->string('catalog', 128);
            $table->string('catindex', 128);
            $table->string('altname', 128);
            $table->index('objectname', 'Index_objectname');
            $table->index(['catalog', 'catindex'], 'Index_catalog');
            $table->index('altname', 'Index_altname');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('objectnames');
    }
}
