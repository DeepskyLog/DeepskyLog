<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->after('id')->default('');
            $table->string('type')->default('default');
            $table->string('country')->default('');
            $table->unsignedinteger('stdlocation')->default(0);
            $table->unsignedinteger('stdtelescope')->default(0);
            $table->string('language')->default('en_US');
            $table->string('icqname')->nullable();
            $table->string('observationlanguage')->default('en');
            $table->string('standardAtlasCode')->default('urano');
            $table->float('fstOffset')->default(0.0);
            $table->string('copyright')->default('');
            $table->string('overviewdsos')->default(10);
            $table->string('lookupdsos')->default(12);
            $table->string('detaildsos')->default(15);
            $table->string('overviewstars')->default(10);
            $table->string('lookupstars')->default(12);
            $table->string('detailstars')->default(15);
            $table->integer('atlaspagefont')->default(6);
            $table->integer('photosize1')->default(60);
            $table->integer('overviewFoV')->default(120);
            $table->integer('photosize2')->default(25);
            $table->integer('lookupFoV')->default(60);
            $table->integer('detailFoV')->default(15);
            $table->boolean('sendMail')->default(false);
            $table->string('version')->default('2019.12');
            $table->boolean('showInches')->default(false);

            $table->foreign('stdlocation')->references('id')->on('locations');
            $table->foreign('stdtelescope')->references('id')->on('instruments');
            $table->foreign('standardAtlasCode')->references('code')->on('atlases');
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
