<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhotometryToCometsOrbitalElementsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('comets_orbital_elements', function (Blueprint $table) {
            if (! Schema::hasColumn('comets_orbital_elements', 'H')) {
                $table->float('H')->nullable()->after('Tp');
            }
            if (! Schema::hasColumn('comets_orbital_elements', 'n')) {
                $table->float('n')->nullable()->after('H');
            }
            if (! Schema::hasColumn('comets_orbital_elements', 'phase_coeff')) {
                $table->float('phase_coeff')->nullable()->after('n');
            }
            if (! Schema::hasColumn('comets_orbital_elements', 'n_pre')) {
                $table->float('n_pre')->nullable()->after('phase_coeff');
            }
            if (! Schema::hasColumn('comets_orbital_elements', 'n_post')) {
                $table->float('n_post')->nullable()->after('n_pre');
            }
            if (! Schema::hasColumn('comets_orbital_elements', 'ref')) {
                $table->string('ref')->nullable()->after('n_post');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('comets_orbital_elements', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('comets_orbital_elements', 'H')) {
                $drop[] = 'H';
            }
            if (Schema::hasColumn('comets_orbital_elements', 'n')) {
                $drop[] = 'n';
            }
            if (Schema::hasColumn('comets_orbital_elements', 'phase_coeff')) {
                $drop[] = 'phase_coeff';
            }
            if (Schema::hasColumn('comets_orbital_elements', 'n_pre')) {
                $drop[] = 'n_pre';
            }
            if (Schema::hasColumn('comets_orbital_elements', 'n_post')) {
                $drop[] = 'n_post';
            }
            if (Schema::hasColumn('comets_orbital_elements', 'ref')) {
                $drop[] = 'ref';
            }

            if (! empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
}
