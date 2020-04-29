<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->string('type', 8);
            $table->string('con', 5)->nullable();
            $table->float('ra', 10, 5)->unsigned()->nullable();
            $table->float('decl', 10, 5)->nullable();
            $table->float('mag')->nullable();
            $table->float('subr')->nullable();
            $table->float('diam1')->unsigned()->nullable();
            $table->float('diam2')->unsigned()->nullable();
            $table->smallInteger('pa')->unsigned()->nullable();
            $table->float('SBObj', 10, 5)->nullable();
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
        \App\Target::create(
            [
                'name' => 'Sun',
                'type' => 'SUN',
            ]
        );

        // Insert the planets
        \App\Target::create(
            [
                'name' => 'Mercury',
                'type' => 'PLANET',
            ]
        );

        \App\Target::create(
            [
                'name' => 'Venus',
                'type' => 'PLANET',
            ]
        );

        \App\Target::create(
            [
                'name' => 'Mars',
                'type' => 'PLANET',
            ]
        );

        \App\Target::create(
            [
                'name' => 'Jupiter',
                'type' => 'PLANET',
            ]
        );

        \App\Target::create(
            [
                'name' => 'Saturn',
                'type' => 'PLANET',
            ]
        );

        \App\Target::create(
            [
                'name' => 'Uranus',
                'type' => 'PLANET',
            ]
        );

        \App\Target::create(
            [
                'name' => 'Neptune',
                'type' => 'PLANET',
            ]
        );

        // Insert the moon
        \App\Target::create(
            [
                'name' => 'Moon',
                'type' => 'OTHER',
            ]
        );

        // Insert the craters
        \App\Target::create(
            [
                'name' => 'Abbot',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Abel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Abenezra',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Abetti',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Abulfeda',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Acosta',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Adams',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Agatharchides',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Agrippa',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Airy',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Akis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Al-Bakri',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Al-Biruni',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Al-Marrakushi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Alan',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Albategnius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Aldrin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Alexander',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Alfraganus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Alhazen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Aliacensis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Almanon',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Aloha',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Alpetragius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Alphonsus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ameghino',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ammonius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Amontons',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Amundsen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Anaxagoras',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Anaximander',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Anaximenes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Anděl',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Andersson',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ango',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Angström',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ann',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Annegrit',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ansgarius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Anville',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Apianus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Apollonius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Arago',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Aratus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Archimedes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Archytas',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Argelander',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ariadaeus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Aristarchus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Aristillus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Aristoteles',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Armstrong',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Arnold',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Arrhenius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Artemis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Artsimovich',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Aryabhata',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Arzachel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Asada',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Asclepi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Aston',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Atlas',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Atwood',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Autolycus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Auwers',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Auzout',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Avery',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Azophi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Baade',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Babbage',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Back',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Baco',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Baillaud',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bailly',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Baily',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Balboa',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ball',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Balmer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Banachiewicz',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bancroft',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Banting',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Barkla',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Barnard',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Barocius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Barrow',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bartels',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bartels A',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bayer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Beals',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Beaumont',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Beer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Behaim',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Beketov',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Béla',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => "Bel'kovich",
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bellot',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bernoulli',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Berosus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Berzelius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bessarion',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bessel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bettinus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bianchini',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Biela',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bilharz',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Billy',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Biot',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Birmingham',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Birt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Black',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Blagg',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Blancanus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Blanchinus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bliss',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bobillier',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bode',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Boethius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Boguslawsky',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bohnenberger',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bohr',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Boltzmann',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bombelli',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bonpland',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Boole',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Borda',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Borel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Boris',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Born',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Boscovich',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Boss',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bouguer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Boussingault',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bowen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Brackett',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Brayley',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Breislak',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Brenner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Brewster',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Brianchon',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Briggs',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Brisbane',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Brown',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bruce',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Brunner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Buch',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bullialdus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bunsen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Burckhardt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Bürg',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Burnham',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Büsching',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Byrd',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Byrgius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'C. Herschel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'C. Mayer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cabeus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cajal',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Calippus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cameron',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Campanus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cannon',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Capella',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Capuanus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cardanus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Carlini',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Carlos',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Carmichael',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Carpenter',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Carrel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Carrillo',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Carrington',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cartan',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Casatus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cassini',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catalán',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catharina',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cauchy',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cavalerius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cavendish',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Caventou',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cayley',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Celsius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Censorinus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cepheus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Chacornac',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Challis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Chang-Ngo',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Chevallier',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ching-Te',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Chladni',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cichus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Clairaut',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Clausius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Clavius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cleomedes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cleostratus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Clerke',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Collins',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Colombo',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Condon',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Condorcet',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Conon',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cook',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Copernicus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Couder',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Courtney',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cremona',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Crile',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Crozier',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Crüger',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ctesibius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Curie',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Curtis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Curtius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cusanus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cuvier',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cyrillus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Cysatus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => "d'Arrest",
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'da Vinci',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dag',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Daguerre',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dale',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dalton',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Daly',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Damoiseau',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Daniell',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Darney',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Darwin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Daubrée',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Davy',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dawes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'de Gasparis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'de Gerlache',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'de la Rue',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'de Moraes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'de Morgan',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'de Sitter',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'de Vico',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Debes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dechen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Delambre',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Delaunay',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Delia',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Delisle',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Delmotte',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Deluc',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dembowski',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Democritus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Demonax',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Desargues',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Descartes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Deseilligny',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Deslandres',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Diana',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dionysius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Diophantus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dollond',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Donati',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Donna',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Donner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Doppelmayer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dove',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Draper',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Drebbel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Drude',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dubyago',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dunthorne',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Eckert',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Eddington',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Edison',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Edith',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Egede',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Eichstadt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Eimmart',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Einstein',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Elger',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Elmer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Encke',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Endymion',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Epigenes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Epimenides',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Eratosthenes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Erlanger',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Esclangon',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Euclides',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Euctemon',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Eudoxus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Euler',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fabbroni',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fabricius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fabry',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fahrenheit',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Faraday',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Faustini',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fauth',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Faye',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fedorov',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Felix',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fermat',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fernelius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Feuillée',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Finsch',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Firmicus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Flammarion',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Flamsteed',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Florensky',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Florey',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Focas',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fontana',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fontenelle',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Foucault',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fourier',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fra Mauro',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fracastorius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Franck',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Franklin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Franz',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fraunhofer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Fredholm',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Freud',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Furnerius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'G. Bond',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Galen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Galilaei',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Galle',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Galvani',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gambart',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gardner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gärtner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gassendi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gaston',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gaudibert',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gauricus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gauss',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gay-Lussac',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Geber',
                'type' => 'CRATER',
            ]
        );

        \App\Target::create(
            [
                'name' => 'Geissler',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Geminus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gemma Frisius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gerard',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gernsback',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gibbs',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gilbert',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gill',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ginzel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gioja',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Giordano Bruno',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Glaisher',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Glushko',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Goclenius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Goddard',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Godin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Goldschmidt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Golgi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Goodacre',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gore',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gould',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Grace',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Graff',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Greaves',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Grimaldi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Grignard',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Grove',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gruemberger',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gruithuisen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Guericke',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gum',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gutenberg',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Guthnick',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Gyldén',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hagecius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hahn',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Haidinger',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hainzel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Haldane',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hale',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hall',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Halley',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hamilton',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hanno',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hansen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hansteen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Harding',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hargreaves',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Harlan',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Harold',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Harpalus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hartwig',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hase',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hausen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Haworth',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hayn',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hecataeus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hédervári',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hedin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Heinrich',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Heinsius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Heis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Helicon',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hell',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Helmert',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Helmholtz',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Henry',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Henry Frères',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Henyey',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Heraclitus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hercules',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Herigonius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hermann',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hermite',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Herodotus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Herschel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hertz',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hesiodus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hevelius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hill',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hind',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hippalus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hinshelwood',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hipparchus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hirayama',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hohmann',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Holden',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hommel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hooke',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hornsby',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Horrebow',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Horrocks',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hortensius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Houtermans',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hubble',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Huggins',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Humason',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Humboldt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hume',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Huxley',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hyginus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Hypatia',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ian',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ibn Bajja',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ibn Battuta',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ibn Yunus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ibn-Rushd',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => "Idel'son",
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ideler',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ina',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Inghirami',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Isabel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Isidorus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Isis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ivan',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'J. Herschel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Jacobi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Jansen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Jansky',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Janssen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Jeans',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Jehan',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Jenkins',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Jenner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Jerik',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Joliot',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Jomo',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'José',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Joy',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Julienne',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Julius Caesar',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kaiser',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kane',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kant',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kao',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kapteyn',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Karima',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kästner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kathleen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Keldysh',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kepler',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kies',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kiess',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kinau',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kirch',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kircher',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kirchhoff',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Klaproth',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Klein',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Knox-Shaw',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'König',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kopff',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kozyrev',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Krafft',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kramarov',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Krasnov',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kreiken',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Krieger',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Krogh',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Krusenstern',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kugler',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kuiper',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kundt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Kunowsky',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'la Caille',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'la Condamine',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'la Pérouse',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacchini',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacroix',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lade',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lagalla',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lagrange',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lalande',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lallemand',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lamarck',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lambert',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lamé',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lamèch',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lamont',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Landsteiner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Langley',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Langrenus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lansberg',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lassell',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Laue',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lauritsen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lavoisier',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lawrence',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'le Gentil',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'le Monnier',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'le Verrier',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Leakey',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lebesgue',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lee',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Legendre',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lehmann',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lepaute',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Letronne',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lexell',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Licetus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lichtenberg',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lick',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Liebig',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lilius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Linda',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lindbergh',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lindenau',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lindsay',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Linné',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Liouville',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lippershey',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Littrow',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lockyer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Loewy',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lohrmann',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lohse',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Longomontanus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lorentz',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Louise',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Louville',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lubbock',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lubiniezky',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lucian',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Luther',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lyapunov',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lyell',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lyot',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Maclaurin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Maclear',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'MacMillan',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Macrobius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mädler',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Maestlin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Magelhaens',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Magelhaens A',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Maginus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Main',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mairan',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Malapert',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Malinkin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mallet',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Manilius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Manners',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Manuel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Manzinus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Maraldi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Marco Polo',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Marinus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Marius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Markov',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Marth',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mary',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Maskelyne',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mason',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Maunder',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Maupertuis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Maurolycus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Maury',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mavis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'McAdie',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'McClure',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'McDonald',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'McLaughlin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mee',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mees',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Menelaus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Menzel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mercator',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mersenius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Messala',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Messier',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Metius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Meton',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Michael',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Milichius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Miller',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mitchell',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Moigno',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Moltke',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Monge',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Monira',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montanari',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Moretus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Morley',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Moseley',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mösting',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mouchez',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Müller',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Murchison',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mutus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nansen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Naonobu',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nasireddin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nasmyth',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Natasha',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Naumann',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Neander',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nearch',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Neison',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Neper',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Neumayer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Newcomb',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Newton',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nicholson',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nicolai',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nicollet',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nielsen',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nobile',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nobili',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nöggerath',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nonius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Norman',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Nunn',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Oenopides',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Oersted',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Oken',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Olbers',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Opelt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Oppolzer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Orontius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Osama',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Osiris',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Osman',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Palisa',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Palitzsch',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pallas',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Palmieri',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Parrot',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Parry',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pascal',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Patricia',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Peary',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Peek',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Peirce',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Peirescius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pentland',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Petavius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Petermann',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Peters',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Petit',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Petrov',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pettit',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Phillips',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Philolaus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Phocylides',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Piazzi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Piazzi Smyth',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Picard',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Piccolomini',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pickering',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pictet',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pierazzo',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pilâtre',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pingré',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pitatus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pitiscus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Plana',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Plato',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Playfair',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Plinius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Plutarch',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Poczobutt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Poisson',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Polybius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pomortsev',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Poncelet',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pons',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pontanus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pontécoulant',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Popov',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Porter',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Posidonius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Powell',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Prinz',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Priscilla',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Proclus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Proctor',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Protagoras',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ptolemaeus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Puiseux',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pupin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Purbach',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Purkyně',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pythagoras',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Pytheas',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rabbi Levi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Raman',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ramsden',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rankine',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ravi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rayleigh',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Réaumur',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Regiomontanus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Regnault',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Reichenbach',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Reimarus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Reiner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Reinhold',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Repsold',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Respighi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rhaeticus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rheita',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rhysling',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Riccioli',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Riccius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Riemann',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ritchey',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ritter',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ritz',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Robert',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Robinson',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rocca',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rocco',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Römer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rosa',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rosenberger',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ross',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rosse',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rost',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rothmann',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Runge',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Russell',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ruth',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rutherfurd',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sabatier',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sabine',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sacrobosco',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Samir',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sampson',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Santbech',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Santos-Dumont',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sarabhai',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sasserides',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Saunder',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Saussure',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Scheele',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Scheiner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schiaparelli',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schickard',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schiller',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schlüter',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schmidt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schomberger',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schönfeld',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schorr',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schröter',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schubert',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schumacher',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Schwabe',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Scoresby',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Scott',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Secchi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Seeliger',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Segner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Seleucus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Seneca',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Shaler',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Shapley',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sharp',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sheepshanks',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Shoemaker',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Short',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Shuckburgh',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Shuleykin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Silberschlag',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Simpelius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinas',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sirsalis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sklodowska',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Slocum',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Smithson',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Smoluchowski',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Snellius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Somerville',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sömmering',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Soraya',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sosigenes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'South',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Spallanzani',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Spörer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Spurr',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Stadius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Stadius A',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Steinheil',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Stella',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Stevinus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Stewart',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Stiborius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Stöfler',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Stokes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Strabo',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Street',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Struve',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Suess',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sulpicius Gallus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sundman',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Susan',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Swasey',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Swift',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sylvester',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'T. Mayer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Tacchini',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Tacitus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Tacquet',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Taizo',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Talbot',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Tannerus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Taruntius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Taylor',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Tebbutt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Tempel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Thales',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Theaetetus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Thebit',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Theiler',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Theon Junior',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Theon Senior',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Theophilus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Theophrastus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Timaeus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Timocharis',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Tisserand',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Tolansky',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Torricelli',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Toscanelli',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Townley',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Tralles',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Triesnecker',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Trouvelot',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Tucker',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Turner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Tycho',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ukert',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Ulugh Beigh',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Urey',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Väisälä',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'van Albada',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Van Biesbroeck',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Van Vleck',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vasco da Gama',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vashakidze',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vega',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vendelinus',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vera',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Verne',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Very',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vieta',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Virchow',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vitello',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vitruvius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vlacq',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vogel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Volta',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'von Behring',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'von Braun',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Voskresenskiy',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'W. Bond',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wallace',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wallach',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Walter',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Walther',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wargentin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Warner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Watt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Watts',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Webb',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Weierstrass',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Weigel',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Weinek',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Weiss',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Werner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wexler',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Whewell',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wichmann',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Widmannstätten',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wildt',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wilhelm',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wilkins',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wilson',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Winthrop',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wöhler',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wolf',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wollaston',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wright',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wrottesley',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wurzelbauer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Wyld',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Xenophanes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Yakovkin',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => "Yangel'",
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Yerkes',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Yoshi',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Young',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Zach',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Zagut',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Zähringer',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Zasyadko',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Zeno',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Zinner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Zöllner',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Zucchius',
                'type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Zupus',
                'type' => 'CRATER',
            ]
        );

        // Add mountains on the moon
        \App\Target::create(
            [
                'name' => 'Mons Agnes',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Ampère',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons André',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Ardeshir',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Argaeus',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Blanc',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Bradley',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Delisle',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Dieter',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Dilip',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Esam',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Ganau',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Gruithuisen Delta',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Gruithuisen Gamma',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Hadley',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Hadley Delta',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Hansteen',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Herodotus',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Huygens',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons La Hire',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Maraldi',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Moro',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Penck',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Pico',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Piton',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Rümker',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Usov',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Vinogradov',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Vitruvius',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mons Wolff',
                'type' => 'MOUNTAIN',
            ]
        );

        \App\Target::create(
            [
                'name' => 'Montes Agricola',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Alpes',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Apenninus',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Archimedes',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Carpatus',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Caucasus',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Cordillera',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Haemus',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Harbinger',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Jura',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Pyrenaeus',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Recti',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Riphaeus',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Rook',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Secchi',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Spitzbergen',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Taurus',
                'type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Montes Teneriffe',
                'type' => 'MOUNTAIN',
            ]
        );

        // Add the seas
        \App\Target::create(
            [
                'name' => 'Mare Anguis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Australe',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Cognitum',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Crisium',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Fecunditatis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Frigoris',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Humboldtianum',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Humorum',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Imbrium',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Ingenii',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Insularum',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Marginis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Moscoviense',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Nectaris',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Nubium',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Orientale',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Serenitatis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Smythii',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Spumans',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Tranquillitatis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Undarum',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Mare Vaporum',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Oceanus Procellarum',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Aestatis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Autumni',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Bonitatis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Doloris',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Excellentiae',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Felicitatis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Gaudii',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Hiemalis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Lenitatis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Luxuriae',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Mortis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Oblivionis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Odii',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Perseverantiae',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Solitudinis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Somniorum',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Spei',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Temporis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Timoris',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Lacus Veris',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Palus Epidemiarum',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Palus Putredinis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Palus Somni',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinus Aestuum',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinus Amoris',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinus Asperitatis',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinus Concordiae',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinus Fidei',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinus Honoris',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinus Iridum',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinus Lunicus',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinus Medii',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinus Roris',
                'type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Sinus Successus',
                'type' => 'SEA',
            ]
        );

        // Add the valleys of the moon
        \App\Target::create(
            [
                'name' => 'Vallis Alpes',
                'type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vallis Baade',
                'type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vallis Bohr',
                'type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vallis Bouvard',
                'type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vallis Capella',
                'type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vallis Inghirami',
                'type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vallis Palitzsch',
                'type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vallis Planck',
                'type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vallis Rheita',
                'type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vallis Schrödinger',
                'type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vallis Schröteri',
                'type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Vallis Snellius',
                'type' => 'VALLEY',
            ]
        );

        // Add the other features on the moon
        \App\Target::create(
            [
                'name' => 'Reiner Gamma',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catena Abulfeda',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catena Brigitte',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catena Davy',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catena Humboldt',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catena Krafft',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catena Littrow',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catena Pierre',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catena Sylvester',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catena Taruntius',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catena Timocharis',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Catena Yuri',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Aldrovandi',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Andrusov',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Argand',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Barlow',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Burnet',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Cato',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Dana',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Ewing',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Geikie',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Harker',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Lister',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Mawson',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Rubey',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Smirnov',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Sorby',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Stille',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Tetyaev',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsa Whiston',
                'type' => 'OTHER',
            ]
        );

        \App\Target::create(
            [
                'name' => 'Dorsum Arduino',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Azara',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Bucher',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Buckland',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Cayeux',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Cloos',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Cushman',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Gast',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Grabau',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Guettard',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Heim',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Higazy',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Nicol',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Niggli',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Oppel',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Owen',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Scilla',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Termier',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Thera',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Von Cotta',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Dorsum Zirkel',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Promontorium Agarum',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Promontorium Agassiz',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Promontorium Archerusia',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Promontorium Deville',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Promontorium Fresnel',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Promontorium Heraclides',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Promontorium Kelvin',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Promontorium Laplace',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Promontorium Taenarium',
                'type' => 'OTHER',
            ]
        );

        \App\Target::create(
            [
                'name' => 'Rima Agatharchides',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Agricola',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Archytas',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Ariadaeus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Billy',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Birt',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Bradley',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Brayley',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Calippus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Cardanus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Carmen',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Cauchy',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Cleomedes',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Cleopatra',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Conon',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Dawes',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Delisle',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Diophantus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Draper',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Euler',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Flammarion',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Furnerius',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima G. Bond',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Galilaei',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Gärtner',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Gay-Lussac',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Hadley',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Hansteen',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Hesiodus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Hyginus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Jansen',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Krieger',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Mairan',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Marcello',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Marius',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Messier',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Milichius',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Oppolzer',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Réaumur',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Reiko',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Rudolf',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Schröter',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Sharp',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Sheepshanks',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Siegfried',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Suess',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Sung-Mei',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima T. Mayer',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Vladimir',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Wan-Yu',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => "Rima Yangel'",
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rima Zahia',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Alphonsus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Apollonius',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Archimedes',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Aristarchus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Arzachel',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Atlas',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Bode',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Boscovich',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Chacornac',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Daniell',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Darwin',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Doppelmayer',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Focas',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Fresnel',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae de Gasparis',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Gassendi',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Gerard',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Goclenius',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Grimaldi',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Hypatia',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Janssen',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Kopff',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Liebig',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Littrow',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Maclear',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Maestlin',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Maupertuis',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Menelaus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Mersenius',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Opelt',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Palmieri',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Parry',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Petavius',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Pettit',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Pitatus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Plato',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Plinius',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Posidonius',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Prinz',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Ramsden',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Repsold',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Riccioli',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Ritter',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Römer',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Secchi',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Sirsalis',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Sosigenes',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Sulpicius Gallus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Taruntius',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Theaetetus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Triesnecker',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Vasco da Gama',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rimae Zupus',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rupes Altai',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rupes Boris',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rupes Cauchy',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rupes Kelvin',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rupes Liebig',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rupes Mercator',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rupes Recta',
                'type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'name' => 'Rupes Toscanelli',
                'type' => 'OTHER',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('targets');
    }
}
