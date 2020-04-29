<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEyepieceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'eyepiece_types', function (Blueprint $table) {
                $table->string('brand', 128);
                $table->string('type', 128);
                $table->timestamps();
                $table->unique(['brand', 'type']);
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eyepiece_types');
    }
}
