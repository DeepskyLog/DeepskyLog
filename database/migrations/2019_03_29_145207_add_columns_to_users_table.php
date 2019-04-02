<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country');
            $table->integer('stdlocation')->default(0);
            $table->integer('stdtelescope')->default(0);
            $table->string('language')->default('en_US');
            $table->integer('stdatlas')->default(0);
            $table->string('icqname')->nullable();
            $table->string('observationlanguage')->default('en');
            $table->string('standardAtlasCode')->default('');
            $table->float('fstOffset')->default(0.0);
            $table->string('copyright');
            $table->string('overviewdsos')->default('');
            $table->string('lookupdsos')->default('');
            $table->string('detaildsos')->default('');
            $table->string('overviewstars')->default('');
            $table->string('lookupstars')->default('');
            $table->string('detailstars')->default('');
            $table->integer('atlaspagefont')->default(6);
            $table->integer('photosize1')->default(60);
            $table->integer('overviewFoV')->default(120);
            $table->integer('photosize2')->default(25);
            $table->integer('lookupFoV')->default(60);
            $table->integer('detailFoV')->default(15);
            $table->boolean('sendMail')->default(false);
            $table->string('version')->default('2019.12');
            $table->boolean('showInches')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country');
            $table->dropColumn('stdlocation');
            $table->dropColumn('stdtelescope');
            $table->dropColumn('language');
            $table->dropColumn('stdatlas');
            $table->dropColumn('icqname');
            $table->dropColumn('observationlanguage');
            $table->dropColumn('standardAtlasCode');
            $table->dropColumn('fstOffset');
            $table->dropColumn('copyright');
            $table->dropColumn('overviewdsos');
            $table->dropColumn('lookupdsos');
            $table->dropColumn('detaildsos');
            $table->dropColumn('overviewstars');
            $table->dropColumn('lookupstars');
            $table->dropColumn('detailstars');
            $table->dropColumn('atlaspagefont');
            $table->dropColumn('photosize1');
            $table->dropColumn('overviewFoV');
            $table->dropColumn('photosize2');
            $table->dropColumn('lookupFoV');
            $table->dropColumn('detailFoV');
            $table->dropColumn('sendMail');
            $table->dropColumn('version');
            $table->dropColumn('showInches');
        });
    }
}
