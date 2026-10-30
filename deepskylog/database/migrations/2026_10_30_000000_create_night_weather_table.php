<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The weather of the past (App\Services\Weather\NightWeather): the
     * hourly values of Open-Meteo's historical archive for one UTC day of a
     * cell of 0.25° (cell_lat, cell_lon: latitude and longitude / 0.25,
     * rounded), null when the archive has none for that day.
     */
    public function up(): void
    {
        Schema::create('night_weather', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('cell_lat');
            $table->smallInteger('cell_lon');
            $table->date('date');
            $table->json('hourly')->nullable();
            $table->timestamp('fetched_at')->nullable();

            $table->unique(['cell_lat', 'cell_lon', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('night_weather');
    }
};
