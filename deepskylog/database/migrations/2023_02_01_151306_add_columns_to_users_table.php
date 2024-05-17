<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->after('id')->default('');
            $table->string('country')->default('');
            $table->unsignedinteger('stdlocation')->nullable();
            $table->unsignedinteger('stdtelescope')->nullable();
            $table->string('language')->default('en_US');
            $table->string('icqname')->nullable();
            $table->string('observationlanguage')->default('en');
            $table->string('standardAtlasCode', 191)->default('urano');
            $table->float('fstOffset')->default(0.0);
            $table->string('copyright')->default('');
            $table->string('copyrightSelection')->default('No license (Not recommended)');
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
            $table->string('version')->default('2023.2');
            $table->boolean('showInches')->default(false);

            // $table->foreign('stdlocation')->nullable()->references('id')
            // ->on('deepskylog.locations');
            // $table->foreign('stdtelescope')->nullable()->references('id')
            // ->on('deepskylog.instruments');
            // $table->foreign('standardAtlasCode')->references('code')->on('deepskylog.atlases');
            $table->string('about', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country', 'stdlocation', 'stdtelescope', 'language', 'icqname', 'observationlanguage', 'standardAtlasCode', 'fstOffset', 'copyright', 'overviewdsos', 'lookupdsos', 'detaildsos', 'overviewstars', 'lookupstars', 'detailstars', 'atlaspagefont', 'photosize1', 'overviewFoV', 'photosize2', 'lookupFoV', 'detailFoV', 'sendMail', 'version', 'showInches', 'about');
        });
    }
};
