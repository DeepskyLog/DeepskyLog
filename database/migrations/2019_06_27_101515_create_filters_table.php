<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'filters', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 255);
                $table->unsignedInteger('type');
                $table->unsignedInteger('color')->nullable();
                $table->unsignedInteger('wratten')->nullable();
                $table->unsignedInteger('schott')->nullable();
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
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('filters');
    }
}

