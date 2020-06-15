<?php

/**
 * Seeder for the target table of the database.
 * Fills the database with the deepsky objects and comets from the old database.
 *
 * PHP Version 7
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use App\CometObjectOld;
use App\ObjectOld;
use App\Target;
use Illuminate\Database\Seeder;

/**
 * Seeder for the target table of the database.
 * Fills the database with the deepsky objects and comets from the old database.
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class TargetTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Import the object data
        $objectData = ObjectOld::all();

        // Sun
        $target_data = [
            'en' => [
                'target_name' => 'Sun',
            ],
            'fr' => [
                'target_name' => 'Soleil',
            ],
            'es' => [
                'target_name' => 'Sol',
            ],
            'nl' => [
                'target_name' => 'Zon',
            ],
            'de' => [
                'target_name' => 'Sonne',
            ],
            'sv' => [
                'target_name' => 'Sol',
            ],
            'target_type' => 'SUN'
        ];

        Target::create($target_data);

        // Moon
        $target_data = [
            'en' => [
                'target_name' => 'Moon',
            ],
            'fr' => [
                'target_name' => 'Lune',
            ],
            'es' => [
                'target_name' => 'Luna',
            ],
            'nl' => [
                'target_name' => 'Maan',
            ],
            'de' => [
                'target_name' => 'Mond',
            ],
            'sv' => [
                'target_name' => 'Måne',
            ],
            'target_type' => 'OTHER'
        ];

        Target::create($target_data);

        // Mercury
        $target_data = [
            'en' => [
                'target_name' => 'Mercury',
            ],
            'fr' => [
                'target_name' => 'Mercure',
            ],
            'es' => [
                'target_name' => 'Mercurio',
            ],
            'nl' => [
                'target_name' => 'Mercurius',
            ],
            'de' => [
                'target_name' => 'Merkur',
            ],
            'sv' => [
                'target_name' => 'Mercury',
            ],
            'target_type' => 'PLANET'
        ];

        Target::create($target_data);

        // Venus
        $target_data = [
            'en' => [
                'target_name' => 'Venus',
            ],
            'fr' => [
                'target_name' => 'Vénus',
            ],
            'es' => [
                'target_name' => 'Venus',
            ],
            'nl' => [
                'target_name' => 'Venus',
            ],
            'de' => [
                'target_name' => 'Venus',
            ],
            'sv' => [
                'target_name' => 'Venus',
            ],
            'target_type' => 'PLANET'
        ];

        Target::create($target_data);

        // Mars
        $target_data = [
            'en' => [
                'target_name' => 'Mars',
            ],
            'fr' => [
                'target_name' => 'Mars',
            ],
            'es' => [
                'target_name' => 'Marte',
            ],
            'nl' => [
                'target_name' => 'Mars',
            ],
            'de' => [
                'target_name' => 'Mars',
            ],
            'sv' => [
                'target_name' => 'Mars',
            ],
            'target_type' => 'PLANET'
        ];

        Target::create($target_data);

        // Jupiter
        $target_data = [
            'en' => [
                'target_name' => 'Jupiter',
            ],
            'fr' => [
                'target_name' => 'Jupiter',
            ],
            'es' => [
                'target_name' => 'Júpiter',
            ],
            'nl' => [
                'target_name' => 'Jupiter',
            ],
            'de' => [
                'target_name' => 'Jupiter',
            ],
            'sv' => [
                'target_name' => 'Jupiter',
            ],
            'target_type' => 'PLANET'
        ];

        Target::create($target_data);

        // Saturn
        $target_data = [
            'en' => [
                'target_name' => 'Saturn',
            ],
            'fr' => [
                'target_name' => 'Saturne',
            ],
            'es' => [
                'target_name' => 'Saturno',
            ],
            'nl' => [
                'target_name' => 'Saturnus',
            ],
            'de' => [
                'target_name' => 'Saturn',
            ],
            'sv' => [
                'target_name' => 'Saturn',
            ],
            'target_type' => 'PLANET'
        ];

        Target::create($target_data);

        // Uranus
        $target_data = [
            'en' => [
                'target_name' => 'Uranus',
            ],
            'fr' => [
                'target_name' => 'Uranus',
            ],
            'es' => [
                'target_name' => 'Urano',
            ],
            'nl' => [
                'target_name' => 'Uranus',
            ],
            'de' => [
                'target_name' => 'Uranus',
            ],
            'sv' => [
                'target_name' => 'Uranus',
            ],
            'target_type' => 'PLANET'
        ];

        Target::create($target_data);

        // Neptune
        $target_data = [
            'en' => [
                'target_name' => 'Neptune',
            ],
            'fr' => [
                'target_name' => 'Neptune',
            ],
            'es' => [
                'target_name' => 'Neptuno',
            ],
            'nl' => [
                'target_name' => 'Neptunus',
            ],
            'de' => [
                'target_name' => 'Neptun',
            ],
            'sv' => [
                'target_name' => 'Neptune',
            ],
            'target_type' => 'PLANET'
        ];

        Target::create($target_data);

        // Insert the craters
        \App\Target::create(
            [
                'target_name' => 'Abbot',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Abel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Abenezra',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Abetti',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Abulfeda',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Acosta',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Adams',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Agatharchides',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Agrippa',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Airy',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Akis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Al-Bakri',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Al-Biruni',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Al-Marrakushi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Alan',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Albategnius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Aldrin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Alexander',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Alfraganus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Alhazen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Aliacensis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Almanon',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Aloha',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Alpetragius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Alphonsus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ameghino',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ammonius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Amontons',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Amundsen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Anaxagoras',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Anaximander',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Anaximenes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Anděl',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Andersson',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ango',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Angström',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ann',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Annegrit',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ansgarius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Anville',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Apianus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Apollonius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Arago',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Aratus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Archimedes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Archytas',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Argelander',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ariadaeus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Aristarchus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Aristillus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Aristoteles',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Armstrong',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Arnold',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Arrhenius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Artemis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Artsimovich',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Aryabhata',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Arzachel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Asada',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Asclepi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Aston',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Atlas',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Atwood',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Autolycus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Auwers',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Auzout',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Avery',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Azophi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Baade',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Babbage',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Back',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Baco',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Baillaud',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bailly',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Baily',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Balboa',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ball',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Balmer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Banachiewicz',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bancroft',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Banting',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Barkla',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Barnard',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Barocius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Barrow',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bartels',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bartels A',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bayer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Beals',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Beaumont',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Beer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Behaim',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Beketov',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Béla',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => "Bel'kovich",
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bellot',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bernoulli',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Berosus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Berzelius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bessarion',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bessel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bettinus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bianchini',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Biela',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bilharz',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Billy',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Biot',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Birmingham',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Birt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Black',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Blagg',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Blancanus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Blanchinus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bliss',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bobillier',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bode',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Boethius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Boguslawsky',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bohnenberger',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bohr',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Boltzmann',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bombelli',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bonpland',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Boole',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Borda',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Borel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Boris',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Born',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Boscovich',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Boss',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bouguer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Boussingault',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bowen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Brackett',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Brayley',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Breislak',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Brenner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Brewster',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Brianchon',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Briggs',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Brisbane',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Brown',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bruce',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Brunner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Buch',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bullialdus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bunsen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Burckhardt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Bürg',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Burnham',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Büsching',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Byrd',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Byrgius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'C. Herschel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'C. Mayer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cabeus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cajal',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Calippus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cameron',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Campanus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cannon',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Capella',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Capuanus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cardanus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Carlini',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Carlos',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Carmichael',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Carpenter',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Carrel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Carrillo',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Carrington',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cartan',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Casatus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cassini',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catalán',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catharina',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cauchy',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cavalerius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cavendish',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Caventou',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cayley',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Celsius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Censorinus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cepheus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Chacornac',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Challis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Chang-Ngo',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Chevallier',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ching-Te',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Chladni',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cichus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Clairaut',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Clausius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Clavius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cleomedes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cleostratus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Clerke',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Collins',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Colombo',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Condon',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Condorcet',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Conon',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cook',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Copernicus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Couder',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Courtney',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cremona',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Crile',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Crozier',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Crüger',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ctesibius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Curie',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Curtis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Curtius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cusanus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cuvier',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cyrillus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Cysatus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => "d'Arrest",
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'da Vinci',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dag',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Daguerre',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dale',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dalton',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Daly',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Damoiseau',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Daniell',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Darney',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Darwin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Daubrée',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Davy',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dawes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'de Gasparis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'de Gerlache',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'de la Rue',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'de Moraes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'de Morgan',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'de Sitter',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'de Vico',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Debes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dechen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Delambre',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Delaunay',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Delia',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Delisle',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Delmotte',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Deluc',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dembowski',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Democritus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Demonax',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Desargues',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Descartes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Deseilligny',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Deslandres',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Diana',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dionysius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Diophantus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dollond',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Donati',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Donna',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Donner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Doppelmayer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dove',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Draper',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Drebbel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Drude',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dubyago',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dunthorne',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Eckert',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Eddington',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Edison',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Edith',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Egede',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Eichstadt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Eimmart',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Einstein',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Elger',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Elmer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Encke',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Endymion',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Epigenes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Epimenides',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Eratosthenes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Erlanger',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Esclangon',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Euclides',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Euctemon',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Eudoxus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Euler',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fabbroni',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fabricius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fabry',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fahrenheit',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Faraday',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Faustini',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fauth',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Faye',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fedorov',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Felix',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fermat',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fernelius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Feuillée',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Finsch',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Firmicus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Flammarion',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Flamsteed',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Florensky',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Florey',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Focas',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fontana',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fontenelle',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Foucault',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fourier',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fra Mauro',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fracastorius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Franck',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Franklin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Franz',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fraunhofer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Fredholm',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Freud',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Furnerius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'G. Bond',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Galen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Galilaei',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Galle',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Galvani',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gambart',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gardner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gärtner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gassendi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gaston',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gaudibert',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gauricus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gauss',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gay-Lussac',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Geber',
                'target_type' => 'CRATER',
            ]
        );

        \App\Target::create(
            [
                'target_name' => 'Geissler',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Geminus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gemma Frisius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gerard',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gernsback',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gibbs',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gilbert',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gill',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ginzel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gioja',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Giordano Bruno',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Glaisher',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Glushko',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Goclenius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Goddard',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Godin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Goldschmidt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Golgi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Goodacre',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gore',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gould',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Grace',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Graff',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Greaves',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Grimaldi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Grignard',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Grove',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gruemberger',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gruithuisen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Guericke',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gum',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gutenberg',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Guthnick',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Gyldén',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hagecius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hahn',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Haidinger',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hainzel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Haldane',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hale',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hall',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Halley',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hamilton',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hanno',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hansen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hansteen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Harding',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hargreaves',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Harlan',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Harold',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Harpalus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hartwig',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hase',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hausen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Haworth',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hayn',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hecataeus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hédervári',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hedin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Heinrich',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Heinsius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Heis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Helicon',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hell',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Helmert',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Helmholtz',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Henry',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Henry Frères',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Henyey',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Heraclitus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hercules',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Herigonius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hermann',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hermite',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Herodotus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Herschel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hertz',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hesiodus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hevelius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hill',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hind',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hippalus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hinshelwood',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hipparchus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hirayama',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hohmann',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Holden',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hommel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hooke',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hornsby',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Horrebow',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Horrocks',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hortensius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Houtermans',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hubble',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Huggins',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Humason',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Humboldt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hume',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Huxley',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hyginus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Hypatia',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ian',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ibn Bajja',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ibn Battuta',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ibn Yunus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ibn-Rushd',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => "Idel'son",
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ideler',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ina',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Inghirami',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Isabel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Isidorus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Isis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ivan',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'J. Herschel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Jacobi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Jansen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Jansky',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Janssen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Jeans',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Jehan',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Jenkins',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Jenner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Jerik',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Joliot',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Jomo',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'José',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Joy',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Julienne',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Julius Caesar',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kaiser',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kane',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kant',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kao',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kapteyn',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Karima',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kästner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kathleen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Keldysh',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kepler',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kies',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kiess',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kinau',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kirch',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kircher',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kirchhoff',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Klaproth',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Klein',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Knox-Shaw',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'König',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kopff',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kozyrev',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Krafft',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kramarov',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Krasnov',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kreiken',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Krieger',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Krogh',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Krusenstern',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kugler',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kuiper',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kundt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Kunowsky',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'la Caille',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'la Condamine',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'la Pérouse',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacchini',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacroix',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lade',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lagalla',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lagrange',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lalande',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lallemand',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lamarck',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lambert',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lamé',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lamèch',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lamont',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Landsteiner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Langley',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Langrenus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lansberg',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lassell',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Laue',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lauritsen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lavoisier',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lawrence',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'le Gentil',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'le Monnier',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'le Verrier',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Leakey',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lebesgue',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lee',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Legendre',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lehmann',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lepaute',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Letronne',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lexell',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Licetus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lichtenberg',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lick',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Liebig',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lilius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Linda',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lindbergh',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lindenau',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lindsay',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Linné',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Liouville',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lippershey',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Littrow',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lockyer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Loewy',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lohrmann',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lohse',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Longomontanus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lorentz',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Louise',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Louville',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lubbock',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lubiniezky',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lucian',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Luther',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lyapunov',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lyell',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lyot',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Maclaurin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Maclear',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'MacMillan',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Macrobius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mädler',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Maestlin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Magelhaens',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Magelhaens A',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Maginus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Main',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mairan',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Malapert',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Malinkin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mallet',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Manilius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Manners',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Manuel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Manzinus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Maraldi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Marco Polo',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Marinus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Marius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Markov',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Marth',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mary',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Maskelyne',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mason',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Maunder',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Maupertuis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Maurolycus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Maury',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mavis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'McAdie',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'McClure',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'McDonald',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'McLaughlin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mee',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mees',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Menelaus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Menzel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mercator',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mersenius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Messala',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Messier',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Metius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Meton',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Michael',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Milichius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Miller',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mitchell',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Moigno',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Moltke',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Monge',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Monira',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montanari',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Moretus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Morley',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Moseley',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mösting',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mouchez',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Müller',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Murchison',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mutus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nansen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Naonobu',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nasireddin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nasmyth',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Natasha',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Naumann',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Neander',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nearch',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Neison',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Neper',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Neumayer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Newcomb',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Newton',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nicholson',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nicolai',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nicollet',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nielsen',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nobile',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nobili',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nöggerath',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nonius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Norman',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Nunn',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Oenopides',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Oersted',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Oken',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Olbers',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Opelt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Oppolzer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Orontius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Osama',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Osiris',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Osman',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Palisa',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Palitzsch',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pallas',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Palmieri',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Parrot',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Parry',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pascal',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Patricia',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Peary',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Peek',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Peirce',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Peirescius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pentland',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Petavius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Petermann',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Peters',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Petit',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Petrov',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pettit',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Phillips',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Philolaus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Phocylides',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Piazzi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Piazzi Smyth',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Picard',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Piccolomini',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pickering',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pictet',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pierazzo',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pilâtre',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pingré',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pitatus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pitiscus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Plana',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Plato',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Playfair',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Plinius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Plutarch',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Poczobutt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Poisson',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Polybius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pomortsev',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Poncelet',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pons',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pontanus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pontécoulant',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Popov',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Porter',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Posidonius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Powell',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Prinz',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Priscilla',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Proclus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Proctor',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Protagoras',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ptolemaeus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Puiseux',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pupin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Purbach',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Purkyně',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pythagoras',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Pytheas',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rabbi Levi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Raman',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ramsden',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rankine',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ravi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rayleigh',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Réaumur',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Regiomontanus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Regnault',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Reichenbach',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Reimarus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Reiner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Reinhold',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Repsold',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Respighi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rhaeticus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rheita',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rhysling',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Riccioli',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Riccius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Riemann',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ritchey',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ritter',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ritz',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Robert',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Robinson',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rocca',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rocco',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Römer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rosa',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rosenberger',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ross',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rosse',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rost',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rothmann',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Runge',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Russell',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ruth',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rutherfurd',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sabatier',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sabine',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sacrobosco',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Samir',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sampson',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Santbech',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Santos-Dumont',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sarabhai',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sasserides',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Saunder',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Saussure',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Scheele',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Scheiner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schiaparelli',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schickard',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schiller',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schlüter',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schmidt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schomberger',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schönfeld',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schorr',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schröter',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schubert',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schumacher',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Schwabe',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Scoresby',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Scott',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Secchi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Seeliger',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Segner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Seleucus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Seneca',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Shaler',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Shapley',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sharp',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sheepshanks',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Shoemaker',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Short',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Shuckburgh',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Shuleykin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Silberschlag',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Simpelius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinas',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sirsalis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sklodowska',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Slocum',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Smithson',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Smoluchowski',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Snellius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Somerville',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sömmering',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Soraya',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sosigenes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'South',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Spallanzani',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Spörer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Spurr',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Stadius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Stadius A',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Steinheil',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Stella',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Stevinus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Stewart',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Stiborius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Stöfler',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Stokes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Strabo',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Street',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Struve',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Suess',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sulpicius Gallus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sundman',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Susan',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Swasey',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Swift',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sylvester',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'T. Mayer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Tacchini',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Tacitus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Tacquet',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Taizo',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Talbot',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Tannerus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Taruntius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Taylor',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Tebbutt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Tempel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Thales',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Theaetetus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Thebit',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Theiler',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Theon Junior',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Theon Senior',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Theophilus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Theophrastus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Timaeus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Timocharis',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Tisserand',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Tolansky',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Torricelli',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Toscanelli',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Townley',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Tralles',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Triesnecker',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Trouvelot',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Tucker',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Turner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Tycho',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ukert',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Ulugh Beigh',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Urey',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Väisälä',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'van Albada',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Van Biesbroeck',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Van Vleck',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vasco da Gama',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vashakidze',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vega',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vendelinus',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vera',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Verne',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Very',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vieta',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Virchow',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vitello',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vitruvius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vlacq',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vogel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Volta',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'von Behring',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'von Braun',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Voskresenskiy',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'W. Bond',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wallace',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wallach',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Walter',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Walther',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wargentin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Warner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Watt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Watts',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Webb',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Weierstrass',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Weigel',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Weinek',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Weiss',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Werner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wexler',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Whewell',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wichmann',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Widmannstätten',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wildt',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wilhelm',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wilkins',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wilson',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Winthrop',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wöhler',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wolf',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wollaston',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wright',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wrottesley',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wurzelbauer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Wyld',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Xenophanes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Yakovkin',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => "Yangel'",
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Yerkes',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Yoshi',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Young',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Zach',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Zagut',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Zähringer',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Zasyadko',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Zeno',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Zinner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Zöllner',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Zucchius',
                'target_type' => 'CRATER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Zupus',
                'target_type' => 'CRATER',
            ]
        );

        // Add mountains on the moon
        \App\Target::create(
            [
                'target_name' => 'Mons Agnes',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Ampère',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons André',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Ardeshir',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Argaeus',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Blanc',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Bradley',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Delisle',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Dieter',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Dilip',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Esam',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Ganau',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Gruithuisen Delta',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Gruithuisen Gamma',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Hadley',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Hadley Delta',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Hansteen',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Herodotus',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Huygens',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons La Hire',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Maraldi',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Moro',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Penck',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Pico',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Piton',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Rümker',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Usov',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Vinogradov',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Vitruvius',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mons Wolff',
                'target_type' => 'MOUNTAIN',
            ]
        );

        \App\Target::create(
            [
                'target_name' => 'Montes Agricola',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Alpes',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Apenninus',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Archimedes',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Carpatus',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Caucasus',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Cordillera',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Haemus',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Harbinger',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Jura',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Pyrenaeus',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Recti',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Riphaeus',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Rook',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Secchi',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Spitzbergen',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Taurus',
                'target_type' => 'MOUNTAIN',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Montes Teneriffe',
                'target_type' => 'MOUNTAIN',
            ]
        );

        // Add the seas
        \App\Target::create(
            [
                'target_name' => 'Mare Anguis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Australe',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Cognitum',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Crisium',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Fecunditatis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Frigoris',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Humboldtianum',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Humorum',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Imbrium',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Ingenii',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Insularum',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Marginis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Moscoviense',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Nectaris',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Nubium',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Orientale',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Serenitatis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Smythii',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Spumans',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Tranquillitatis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Undarum',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Mare Vaporum',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Oceanus Procellarum',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Aestatis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Autumni',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Bonitatis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Doloris',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Excellentiae',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Felicitatis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Gaudii',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Hiemalis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Lenitatis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Luxuriae',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Mortis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Oblivionis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Odii',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Perseverantiae',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Solitudinis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Somniorum',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Spei',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Temporis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Timoris',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Lacus Veris',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Palus Epidemiarum',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Palus Putredinis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Palus Somni',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinus Aestuum',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinus Amoris',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinus Asperitatis',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinus Concordiae',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinus Fidei',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinus Honoris',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinus Iridum',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinus Lunicus',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinus Medii',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinus Roris',
                'target_type' => 'SEA',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Sinus Successus',
                'target_type' => 'SEA',
            ]
        );

        // Add the valleys of the moon
        \App\Target::create(
            [
                'target_name' => 'Vallis Alpes',
                'target_type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vallis Baade',
                'target_type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vallis Bohr',
                'target_type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vallis Bouvard',
                'target_type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vallis Capella',
                'target_type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vallis Inghirami',
                'target_type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vallis Palitzsch',
                'target_type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vallis Planck',
                'target_type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vallis Rheita',
                'target_type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vallis Schrödinger',
                'target_type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vallis Schröteri',
                'target_type' => 'VALLEY',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Vallis Snellius',
                'target_type' => 'VALLEY',
            ]
        );

        // Add the other features on the moon
        \App\Target::create(
            [
                'target_name' => 'Reiner Gamma',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catena Abulfeda',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catena Brigitte',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catena Davy',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catena Humboldt',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catena Krafft',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catena Littrow',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catena Pierre',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catena Sylvester',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catena Taruntius',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catena Timocharis',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Catena Yuri',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Aldrovandi',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Andrusov',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Argand',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Barlow',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Burnet',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Cato',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Dana',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Ewing',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Geikie',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Harker',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Lister',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Mawson',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Rubey',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Smirnov',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Sorby',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Stille',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Tetyaev',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsa Whiston',
                'target_type' => 'OTHER',
            ]
        );

        \App\Target::create(
            [
                'target_name' => 'Dorsum Arduino',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Azara',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Bucher',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Buckland',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Cayeux',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Cloos',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Cushman',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Gast',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Grabau',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Guettard',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Heim',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Higazy',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Nicol',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Niggli',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Oppel',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Owen',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Scilla',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Termier',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Thera',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Von Cotta',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Dorsum Zirkel',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Promontorium Agarum',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Promontorium Agassiz',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Promontorium Archerusia',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Promontorium Deville',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Promontorium Fresnel',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Promontorium Heraclides',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Promontorium Kelvin',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Promontorium Laplace',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Promontorium Taenarium',
                'target_type' => 'OTHER',
            ]
        );

        \App\Target::create(
            [
                'target_name' => 'Rima Agatharchides',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Agricola',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Archytas',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Ariadaeus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Billy',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Birt',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Bradley',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Brayley',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Calippus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Cardanus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Carmen',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Cauchy',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Cleomedes',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Cleopatra',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Conon',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Dawes',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Delisle',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Diophantus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Draper',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Euler',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Flammarion',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Furnerius',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima G. Bond',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Galilaei',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Gärtner',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Gay-Lussac',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Hadley',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Hansteen',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Hesiodus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Hyginus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Jansen',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Krieger',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Mairan',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Marcello',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Marius',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Messier',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Milichius',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Oppolzer',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Réaumur',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Reiko',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Rudolf',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Schröter',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Sharp',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Sheepshanks',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Siegfried',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Suess',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Sung-Mei',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima T. Mayer',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Vladimir',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Wan-Yu',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => "Rima Yangel'",
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rima Zahia',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Alphonsus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Apollonius',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Archimedes',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Aristarchus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Arzachel',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Atlas',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Bode',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Boscovich',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Chacornac',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Daniell',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Darwin',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Doppelmayer',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Focas',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Fresnel',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae de Gasparis',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Gassendi',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Gerard',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Goclenius',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Grimaldi',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Hypatia',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Janssen',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Kopff',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Liebig',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Littrow',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Maclear',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Maestlin',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Maupertuis',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Menelaus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Mersenius',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Opelt',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Palmieri',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Parry',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Petavius',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Pettit',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Pitatus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Plato',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Plinius',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Posidonius',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Prinz',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Ramsden',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Repsold',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Riccioli',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Ritter',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Römer',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Secchi',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Sirsalis',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Sosigenes',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Sulpicius Gallus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Taruntius',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Theaetetus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Triesnecker',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Vasco da Gama',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rimae Zupus',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rupes Altai',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rupes Boris',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rupes Cauchy',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rupes Kelvin',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rupes Liebig',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rupes Mercator',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rupes Recta',
                'target_type' => 'OTHER',
            ]
        );
        \App\Target::create(
            [
                'target_name' => 'Rupes Toscanelli',
                'target_type' => 'OTHER',
            ]
        );

        foreach ($objectData as $oldObject) {
            if ($oldObject->timestamp == '') {
                $date = date('Y-m-d H:i:s');
            } else {
                [$year, $month, $day, $hour, $minute, $second]
                    = sscanf($oldObject->timestamp, '%4d%2d%2d%2d%2d%d');
                $date = date(
                    'Y-m-d H:i:s',
                    mktime($hour, $minute, $second, $month, $day, $year)
                );
            }

            $type = $oldObject->type;

            if ($type == 'LMCDN' || $type == 'SMCDN') {
                $type = 'GXADN';
            }

            if ($type == 'GXAGC' || $type == 'LMCGC' || $type == 'SMCGC') {
                $type = 'GLOCL';
            }

            if ($type == 'GACAN' || $type == 'LMCCN' || $type == 'SMCCN') {
                $type = 'CLANB';
            }

            if ($type == 'LMCOC' || $type == 'SMCOC' || $type == 'AA8STAR') {
                $type = 'OPNCL';
            }

            if ($type == 'AA1STAR') {
                $type = 'DS';
            }

            $mag = $oldObject->mag;
            if ($mag > 80) {
                $mag = null;
            }
            $subr = $oldObject->subr;
            if ($subr > 80) {
                $subr = null;
            }

            Target::create(
                [
                    'target_name' => $oldObject->name,
                    'target_type' => $type,
                    'constellation' => $oldObject->con,
                    'ra' => $oldObject->ra,
                    'decl' => $oldObject->decl,
                    'mag' => $mag,
                    'subr' => $subr,
                    'diam1' => $oldObject->diam1,
                    'diam2' => $oldObject->diam2,
                    'pa' => $oldObject->pa,
                    'SBObj' => $oldObject->SBObj,
                    'datasource' => $oldObject->datasource,
                    'description' => $oldObject->description,
                    'urano' => $oldObject->urano,
                    'urano_new' => $oldObject->urano_new,
                    'sky' => $oldObject->sky,
                    'millenium' => $oldObject->millenium,
                    'taki' => $oldObject->taki,
                    'psa' => $oldObject->psa,
                    'torresB' => $oldObject->torresB,
                    'torresBC' => $oldObject->torresBC,
                    'torresC' => $oldObject->torresC,
                    'milleniumbase' => $oldObject->milleniumbase,
                    'DSLDL' => $oldObject->DSLDL,
                    'DSLDP' => $oldObject->DSLDP,
                    'DSLLL' => $oldObject->DSLLL,
                    'DSLLP' => $oldObject->DSLLP,
                    'DSLOL' => $oldObject->DSLOL,
                    'DSLOP' => $oldObject->DSLOP,
                    'DeepskyHunter' => $oldObject->DeepskyHunter,
                    'Interstellarum' => $oldObject->Interstellarum,
                    'created_at' => $date,
                ]
            );
        }

        // Import the cometobject data
        $cometData = CometObjectOld::all();

        foreach ($cometData as $comet) {
            if ($comet->timestamp == '') {
                $date = date('Y-m-d H:i:s');
            } else {
                [$year, $month, $day, $hour, $minute, $second]
                               = sscanf($comet->timestamp, '%4d%2d%2d%2d%2d%d');
                $date = date(
                    'Y-m-d H:i:s',
                    mktime($hour, $minute, $second, $month, $day, $year)
                );
            }

            Target::create(
                [
                    'target_name' => html_entity_decode($comet->name),
                    'target_type' => 'COMET',
                    'created_at' => $date,
                ]
            );
        }
    }
}
