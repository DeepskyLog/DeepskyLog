<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The System II longitude of Jupiter's Great Red Spot as administrators
     * and database experts enter it on /admin/great-red-spot (from JUPOS or
     * Sky & Telescope), with its drift: the newest measurement gives the
     * transits of App\Services\Planets\PlanetMoonEvents. The first row is the
     * value the code started with.
     */
    public function up(): void
    {
        Schema::create('great_red_spot_positions', function (Blueprint $table) {
            $table->id();
            $table->decimal('longitude', 6, 2);
            $table->date('measured_on');
            $table->decimal('drift', 5, 2);
            $table->string('source')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index('measured_on');
        });

        DB::table('great_red_spot_positions')->insert([
            'longitude'   => 91.0,
            'measured_on' => '2026-06-01',
            'drift'       => 21.0,
            'source'      => 'Sky & Telescope, from the JUPOS trend',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('great_red_spot_positions');
    }
};
