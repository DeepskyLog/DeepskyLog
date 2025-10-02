<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLunarFeaturesTable extends Migration
{
    public function up()
    {
        Schema::create('lunar_features', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->index();
            $table->string('feature_type', 64)->index(); // crater, mare, rille, etc.
            $table->unsignedBigInteger('moon_id')->nullable()->index();
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lon', 9, 6)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lunar_features');
    }
}
