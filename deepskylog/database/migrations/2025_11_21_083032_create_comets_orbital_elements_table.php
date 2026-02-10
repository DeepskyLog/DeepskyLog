<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCometsOrbitalElementsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('comets_orbital_elements')) {
            Schema::create(
                'comets_orbital_elements',
                function (Blueprint $table) {
                    $table->string('name')->primary();
                    $table->integer('epoch')->signed();
                    $table->float('q', 12, 8);
                    $table->float('e', 10, 5);
                    $table->float('i', 10, 5);
                    $table->float('w', 10, 5);
                    $table->float('node', 10, 5);
                    $table->float('Tp', 15, 5);
                    // Photometry fields (optional)
                    $table->float('H', 6, 3)->nullable();
                    $table->float('n', 6, 3)->nullable();
                    $table->float('phase_coeff', 6, 4)->nullable();
                    $table->float('n_pre', 6, 3)->nullable();
                    $table->float('n_post', 6, 3)->nullable();
                    $table->string('ref');
                }
            );
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('comets_orbital_elements');
    }
}
