<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Orbital elements of comets at the epochs of their observations (JPL
 * Horizons), next to the current elements of `comets_orbital_elements`: those
 * describe the orbit of today, which can lie far from the orbit of an
 * apparition decades ago (App\Services\CometCoordinates::orbitalElements()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comet_epoch_elements', function (Blueprint $table) {
            $table->id();
            // The designation of App\Services\CometCoordinates::splitName(): C/2006 VZ13, 2P, 73P-B
            $table->string('designation', 32);
            // Julian day (TDB) of the osculating elements
            $table->double('epoch');
            $table->double('q');
            $table->double('e');
            $table->double('i');
            $table->double('w');
            $table->double('node');
            // Time of perihelion as YYYYMMDD.dddd (TDB), as in comets_orbital_elements
            $table->double('Tp');
            $table->string('source', 64);
            $table->timestamps();

            $table->unique(['designation', 'epoch']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comet_epoch_elements');
    }
};
