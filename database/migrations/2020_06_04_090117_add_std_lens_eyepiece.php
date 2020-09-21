<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStdLensEyepiece extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedinteger('stdeyepiece')->nullable();
            $table->unsignedinteger('stdlens')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
