<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->string('icqname', 11)->nullable();
            $table->string('type', 8);
            $table->string('con', 5)->nullable();
            $table->float('ra')->unsigned()->nullable();
            $table->float('decl')->nullable();
            $table->float('mag')->nullable();
            $table->float('subr')->nullable();
            $table->float('diam1')->unsigned()->nullable();
            $table->float('diam2')->unsigned()->nullable();
            $table->smallInteger('pa')->unsigned()->nullable();
            $table->float('SBObj')->nullable();
            $table->string('datasource', 50)->nullable();
            $table->string('description', 1024)->nullable();

            $table->smallInteger('urano')->unsigned()->nullable();
            $table->smallInteger('urano_new')->unsigned()->nullable();
            $table->smallInteger('sky')->unsigned()->nullable();
            $table->smallInteger('millenium')->unsigned()->nullable();
            $table->smallInteger('taki')->unsigned()->nullable();
            $table->smallInteger('psa')->unsigned()->nullable();
            $table->smallInteger('torresB')->unsigned()->nullable();
            $table->smallInteger('torresBC')->unsigned()->nullable();
            $table->smallInteger('torresC')->unsigned()->nullable();
            $table->smallInteger('milleniumbase')->unsigned()->nullable();
            $table->smallInteger('DSLDL')->unsigned()->nullable();
            $table->smallInteger('DSLDP')->unsigned()->nullable();
            $table->smallInteger('DSLLL')->unsigned()->nullable();
            $table->smallInteger('DSLLP')->unsigned()->nullable();
            $table->smallInteger('DSLOL')->unsigned()->nullable();
            $table->smallInteger('DSLOP')->unsigned()->nullable();
            $table->smallInteger('DeepskyHunter')->unsigned()->nullable();
            $table->smallInteger('Interstellarum')->unsigned()->nullable();

            $table->timestamps();

            $table->foreign('type')
                ->references('id')->on('target_types');

            $table->foreign('con')
                ->references('id')->on('constellations');
        });

            // Insert the Sun
            DB::table('targets')->insert(
                [
                    'name' => "Sun",
                    'type' => 'SUN'
                ]
            );

            // Insert the planets
            DB::table('targets')->insert(
                [
                    'name' => "Mercury",
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => "Venus",
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => "Mars",
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => "Jupiter",
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => "Saturn",
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => "Uranus",
                    'type' => 'PLANET'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => "Neptune",
                    'type' => 'PLANET'
                ]
            );

            // Insert the craters
            DB::table('targets')->insert(
                [
                    'name' => "Abbot",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Abel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Abenezra",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Abetti",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Abulfeda",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Acosta",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Adams",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Agatharchides",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Agrippa",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Airy",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Akis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Al-Bakri",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Al-Biruni",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Al-Marrakushi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Alan",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Albategnius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Aldrin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Alexander",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Alfraganus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Alhazen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Aliacensis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Almanon",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Aloha",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Alpetragius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Alphonsus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ameghino",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ammonius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Amontons",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Amundsen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Anaxagoras",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Anaximander",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Anaximenes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Anděl",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Andersson",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ango",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Angström",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ann",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Annegrit",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ansgarius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Anville",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Apianus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Apollonius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Arago",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Aratus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Archimedes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Archytas",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Argelander",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ariadaeus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Aristarchus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Aristillus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Aristoteles",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Armstrong",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Arnold",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Arrhenius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Artemis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Artsimovich",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Aryabhata",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Arzachel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Asada",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Asclepi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Aston",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Atlas",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Atwood",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Autolycus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Auwers",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Auzout",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Avery",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Azophi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Baade",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Babbage",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Back",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Baco",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Baillaud",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bailly",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Baily",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Balboa",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ball",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Balmer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Banachiewicz",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bancroft",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Banting",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Barkla",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Barnard",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Barocius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Barrow",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bartels",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bartels A",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bayer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Beals",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Beaumont",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Beer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Behaim",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Beketov",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Béla",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bel'kovich",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bellot",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bernoulli",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Berosus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Berzelius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bessarion",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bessel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bettinus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bianchini",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Biela",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bilharz",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Billy",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Biot",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Birmingham",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Birt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Black",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Blagg",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Blancanus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Blanchinus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bliss",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bobillier",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bode",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Boethius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Boguslawsky",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bohnenberger",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bohr",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Boltzmann",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bombelli",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bonpland",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Boole",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Borda",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Borel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Boris",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Born",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Boscovich",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Boss",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bouguer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Boussingault",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bowen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Brackett",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Brayley",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Breislak",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Brenner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Brewster",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Brianchon",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Briggs",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Brisbane",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Brown",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bruce",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Brunner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Buch",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bullialdus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bunsen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Burckhardt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Bürg",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Burnham",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Büsching",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Byrd",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Byrgius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "C. Herschel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "C. Mayer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cabeus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cajal",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Calippus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cameron",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Campanus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cannon",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Capella",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Capuanus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cardanus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Carlini",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Carlos",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Carmichael",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Carpenter",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Carrel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Carrillo",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Carrington",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cartan",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Casatus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cassini",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Catalán",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Catharina",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cauchy",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cavalerius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cavendish",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Caventou",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cayley",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Celsius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Censorinus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cepheus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Chacornac",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Challis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Chang-Ngo",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Chevallier",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ching-Te",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Chladni",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cichus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Clairaut",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Clausius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Clavius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cleomedes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cleostratus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Clerke",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Collins",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Colombo",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Condon",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Condorcet",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Conon",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cook",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Copernicus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Couder",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Courtney",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cremona",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Crile",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Crozier",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Crüger",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ctesibius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Curie",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Curtis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Curtius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cusanus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cuvier",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cyrillus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Cysatus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "d'Arrest",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "da Vinci",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Dag",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Daguerre",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Dale",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Dalton",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Daly",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Damoiseau",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Daniell",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Darney",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Darwin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Daubrée",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Davy",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Dawes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "de Gasparis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "de Gerlache",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "de la Rue",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "de Moraes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "de Morgan",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "de Sitter",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "de Vico",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Debes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Dechen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Delambre",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Delaunay",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Delia",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Delisle",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Delmotte",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Deluc",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Dembowski",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Democritus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Demonax",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Desargues",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Descartes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Deseilligny",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Deslandres",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Diana",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Dionysius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Diophantus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Dollond",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Donati",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Donna",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Donner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Doppelmayer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Dove",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Draper",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Drebbel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Drude",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Dubyago",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Dunthorne",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Eckert",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Eddington",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Edison",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Edith",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Egede",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Eichstadt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Eimmart",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Einstein",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Elger",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Elmer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Encke",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Endymion",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Epigenes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Epimenides",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Eratosthenes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Erlanger",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Esclangon",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Euclides",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Euctemon",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Eudoxus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Euler",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fabbroni",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fabricius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fabry",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fahrenheit",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Faraday",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Faustini",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fauth",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Faye",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fedorov",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Felix",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fermat",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fernelius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Feuillée",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Finsch",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Firmicus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Flammarion",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Flamsteed",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Florensky",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Florey",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Focas",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fontana",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fontenelle",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Foucault",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fourier",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fra Mauro",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fracastorius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Franck",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Franklin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Franz",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fraunhofer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Fredholm",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Freud",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Furnerius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "G. Bond",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Galen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Galilaei",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Galle",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Galvani",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gambart",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gardner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gärtner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gassendi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gaston",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gaudibert",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gauricus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gauss",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gay-Lussac",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Geber",
                    'type' => 'CRATER'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => "Geissler",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Geminus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gemma Frisius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gerard",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gernsback",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gibbs",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gilbert",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gill",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ginzel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gioja",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Giordano Bruno",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Glaisher",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Glushko",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Goclenius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Goddard",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Godin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Goldschmidt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Golgi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Goodacre",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gore",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gould",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Grace",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Graff",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Greaves",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Grimaldi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Grignard",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Grove",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gruemberger",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gruithuisen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Guericke",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gum",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gutenberg",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Guthnick",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Gyldén",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hagecius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hahn",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Haidinger",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hainzel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Haldane",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hale",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hall",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Halley",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hamilton",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hanno",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hansen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hansteen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Harding",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hargreaves",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Harlan",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Harold",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Harpalus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hartwig",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hase",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hausen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Haworth",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hayn",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hecataeus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hédervári",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hedin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Heinrich",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Heinsius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Heis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Helicon",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hell",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Helmert",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Helmholtz",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Henry",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Henry Frères",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Henyey",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Heraclitus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hercules",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Herigonius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hermann",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hermite",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Herodotus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Herschel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hertz",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hesiodus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hevelius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hill",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hind",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hippalus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hinshelwood",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hipparchus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hirayama",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hohmann",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Holden",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hommel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hooke",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hornsby",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Horrebow",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Horrocks",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hortensius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Houtermans",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hubble",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Huggins",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Humason",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Humboldt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hume",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Huxley",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hyginus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Hypatia",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ian",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ibn Bajja",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ibn Battuta",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ibn Yunus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ibn-Rushd",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Idel'son",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ideler",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ina",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Inghirami",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Isabel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Isidorus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Isis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ivan",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "J. Herschel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Jacobi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Jansen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Jansky",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Janssen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Jeans",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Jehan",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Jenkins",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Jenner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Jerik",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Joliot",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Jomo",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "José",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Joy",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Julienne",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Julius Caesar",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kaiser",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kane",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kant",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kao",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kapteyn",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Karima",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kästner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kathleen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Keldysh",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kepler",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kies",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kiess",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kinau",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kirch",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kircher",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kirchhoff",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Klaproth",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Klein",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Knox-Shaw",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "König",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kopff",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kozyrev",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Krafft",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kramarov",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Krasnov",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kreiken",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Krieger",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Krogh",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Krusenstern",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kugler",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kuiper",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kundt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Kunowsky",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "la Caille",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "la Condamine",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "la Pérouse",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lacchini",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lacroix",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lade",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lagalla",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lagrange",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lalande",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lallemand",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lamarck",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lambert",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lamé",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lamèch",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lamont",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Landsteiner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Langley",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Langrenus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lansberg",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lassell",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Laue",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lauritsen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lavoisier",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lawrence",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "le Gentil",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "le Monnier",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "le Verrier",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Leakey",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lebesgue",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lee",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Legendre",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lehmann",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lepaute",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Letronne",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lexell",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Licetus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lichtenberg",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lick",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Liebig",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lilius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Linda",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lindbergh",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lindenau",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lindsay",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Linné",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Liouville",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lippershey",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Littrow",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lockyer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Loewy",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lohrmann",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lohse",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Longomontanus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lorentz",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Louise",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Louville",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lubbock",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lubiniezky",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lucian",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Luther",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lyapunov",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lyell",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Lyot",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Maclaurin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Maclear",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "MacMillan",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Macrobius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mädler",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Maestlin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Magelhaens",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Magelhaens A",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Maginus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Main",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mairan",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Malapert",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Malinkin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mallet",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Manilius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Manners",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Manuel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Manzinus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Maraldi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Marco Polo",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Marinus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Marius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Markov",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Marth",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mary",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Maskelyne",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mason",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Maunder",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Maupertuis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Maurolycus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Maury",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mavis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "McAdie",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "McClure",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "McDonald",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "McLaughlin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mee",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mees",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Menelaus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Menzel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mercator",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mercurius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mersenius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Messala",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Messier",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Metius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Meton",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Michael",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Milichius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Miller",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mitchell",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Moigno",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Moltke",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Monge",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Monira",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montanari",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Moretus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Morley",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Moseley",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mösting",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mouchez",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Müller",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Murchison",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mutus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nansen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Naonobu",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nasireddin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nasmyth",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Natasha",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Naumann",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Neander",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nearch",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Neison",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Neper",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Neumayer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Newcomb",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Newton",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nicholson",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nicolai",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nicollet",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nielsen",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nobile",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nobili",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nöggerath",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nonius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Norman",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Nunn",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Oenopides",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Oersted",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Oken",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Olbers",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Opelt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Oppolzer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Orontius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Osama",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Osiris",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Osman",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Palisa",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Palitzsch",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pallas",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Palmieri",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Parrot",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Parry",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pascal",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Patricia",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Peary",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Peek",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Peirce",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Peirescius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pentland",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Petavius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Petermann",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Peters",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Petit",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Petrov",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pettit",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Phillips",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Philolaus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Phocylides",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Piazzi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Piazzi Smyth",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Picard",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Piccolomini",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pickering",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pictet",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pierazzo",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pilâtre",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pingré",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pitatus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pitiscus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Plana",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Plato",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Playfair",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Plinius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Plutarch",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Poczobutt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Poisson",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Polybius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pomortsev",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Poncelet",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pons",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pontanus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pontécoulant",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Popov",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Porter",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Posidonius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Powell",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Prinz",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Priscilla",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Proclus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Proctor",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Protagoras",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ptolemaeus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Puiseux",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pupin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Purbach",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Purkyně",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pythagoras",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Pytheas",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rabbi Levi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Raman",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ramsden",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rankine",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ravi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rayleigh",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Réaumur",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Regiomontanus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Regnault",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Reichenbach",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Reimarus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Reiner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Reinhold",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Repsold",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Respighi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rhaeticus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rheita",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rhysling",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Riccioli",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Riccius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Riemann",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ritchey",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ritter",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ritz",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Robert",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Robinson",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rocca",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rocco",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Römer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rosa",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rosenberger",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ross",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rosse",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rost",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rothmann",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Runge",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Russell",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ruth",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Rutherfurd",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sabatier",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sabine",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sacrobosco",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Samir",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sampson",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Santbech",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Santos-Dumont",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sarabhai",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sasserides",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Saunder",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Saussure",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Scheele",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Scheiner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schiaparelli",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schickard",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schiller",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schlüter",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schmidt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schomberger",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schönfeld",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schorr",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schröter",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schubert",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schumacher",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Schwabe",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Scoresby",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Scott",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Secchi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Seeliger",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Segner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Seleucus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Seneca",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Shaler",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Shapley",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sharp",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sheepshanks",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Shoemaker",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Short",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Shuckburgh",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Shuleykin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Silberschlag",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Simpelius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sinas",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sirsalis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sklodowska",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Slocum",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Smithson",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Smoluchowski",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Snellius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Somerville",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sömmering",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Soraya",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sosigenes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "South",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Spallanzani",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Spörer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Spurr",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Stadius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Stadius A",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Steinheil",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Stella",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Stevinus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Stewart",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Stiborius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Stöfler",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Stokes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Strabo",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Street",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Struve",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Suess",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sulpicius Gallus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sundman",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Susan",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Swasey",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Swift",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Sylvester",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "T. Mayer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Tacchini",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Tacitus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Tacquet",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Taizo",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Talbot",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Tannerus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Taruntius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Taylor",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Tebbutt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Tempel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Thales",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Theaetetus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Thebit",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Theiler",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Theon Junior",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Theon Senior",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Theophilus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Theophrastus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Timaeus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Timocharis",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Tisserand",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Tolansky",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Torricelli",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Toscanelli",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Townley",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Tralles",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Triesnecker",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Trouvelot",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Tucker",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Turner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Tycho",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ukert",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Ulugh Beigh",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Urey",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Väisälä",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "van Albada",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Van Biesbroeck",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Van Vleck",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Vasco da Gama",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Vashakidze",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Vega",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Vendelinus",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Vera",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Verne",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Very",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Vieta",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Virchow",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Vitello",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Vitruvius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Vlacq",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Vogel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Volta",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "von Behring",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "von Braun",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Voskresenskiy",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "W. Bond",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wallace",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wallach",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Walter",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Walther",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wargentin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Warner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Watt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Watts",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Webb",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Weierstrass",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Weigel",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Weinek",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Weiss",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Werner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wexler",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Whewell",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wichmann",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Widmannstätten",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wildt",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wilhelm",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wilkins",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wilson",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Winthrop",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wöhler",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wolf",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wollaston",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wright",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wrottesley",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wurzelbauer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Wyld",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Xenophanes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Yakovkin",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Yangel'",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Yerkes",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Yoshi",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Young",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Zach",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Zagut",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Zähringer",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Zasyadko",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Zeno",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Zinner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Zöllner",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Zucchius",
                    'type' => 'CRATER'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Zupus",
                    'type' => 'CRATER'
                ]
            );

            // Add mountains on the moon
            DB::table('targets')->insert(
                [
                    'name' => "Mons Agnes",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Ampère",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons André",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Ardeshir",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Argaeus",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Blanc",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Bradley",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Delisle",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Dieter",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Dilip",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Esam",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Ganau",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Gruithuisen Delta",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Gruithuisen Gamma",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Hadley",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Hadley Delta",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Hansteen",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Herodotus",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Huygens",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons La Hire",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Maraldi",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Moro",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Penck",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Pico",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Piton",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Rümker",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Usov",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Vinogradov",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Vitruvius",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Mons Wolff",
                    'type' => 'MOUNTAIN'
                ]
            );

            DB::table('targets')->insert(
                [
                    'name' => "Montes Agricola",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Alpes",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Apenninus",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Archimedes",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Carpatus",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Caucasus",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Cordillera",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Haemus",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Harbinger",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Jura",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Pyrenaeus",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Recti",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Riphaeus",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Rook",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Secchi",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Spitzbergen",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Taurus",
                    'type' => 'MOUNTAIN'
                ]
            );
            DB::table('targets')->insert(
                [
                    'name' => "Montes Teneriffe",
                    'type' => 'MOUNTAIN'
                ]
            );


    }

    /**
     * Reverse the migrations.
     *
     */
    public function down()
    {
        Schema::dropIfExists('targets');
    }
}
