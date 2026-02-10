<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanetTranslationsSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        // Canonical names (English) used in the database
        $items = [
            // Planets + Sun + Moon
            ['canonical' => 'Sun', 'de' => 'Sonne', 'es' => 'Sol', 'fr' => 'Soleil', 'nl' => 'Zon', 'sv' => 'Solen'],
            ['canonical' => 'Mercury', 'de' => 'Merkur', 'es' => 'Mercurio', 'fr' => 'Mercure', 'nl' => 'Mercurius', 'sv' => 'Merkurius'],
            ['canonical' => 'Venus', 'de' => 'Venus', 'es' => 'Venus', 'fr' => 'Vénus', 'nl' => 'Venus', 'sv' => 'Venus'],
            ['canonical' => 'Earth', 'de' => 'Erde', 'es' => 'Tierra', 'fr' => 'Terre', 'nl' => 'Aarde', 'sv' => 'Jorden'],
            ['canonical' => 'Moon', 'de' => 'Mond', 'es' => 'Luna', 'fr' => 'Lune', 'nl' => 'Maan', 'sv' => 'Månen'],
            ['canonical' => 'Mars', 'de' => 'Mars', 'es' => 'Marte', 'fr' => 'Mars', 'nl' => 'Mars', 'sv' => 'Mars'],
            ['canonical' => 'Jupiter', 'de' => 'Jupiter', 'es' => 'Júpiter', 'fr' => 'Jupiter', 'nl' => 'Jupiter', 'sv' => 'Jupiter'],
            ['canonical' => 'Saturn', 'de' => 'Saturn', 'es' => 'Saturno', 'fr' => 'Saturne', 'nl' => 'Saturnus', 'sv' => 'Saturnus'],
            ['canonical' => 'Uranus', 'de' => 'Uranus', 'es' => 'Urano', 'fr' => 'Uranus', 'nl' => 'Uranus', 'sv' => 'Uranus'],
            ['canonical' => 'Neptune', 'de' => 'Neptun', 'es' => 'Neptuno', 'fr' => 'Neptune', 'nl' => 'Neptunus', 'sv' => 'Neptunus'],
            ['canonical' => 'Pluto', 'de' => 'Pluto', 'es' => 'Plutón', 'fr' => 'Pluton', 'nl' => 'Pluto', 'sv' => 'Pluto'],

            // Major moons (use canonical names - typically unchanged across locales)
            ['canonical' => 'Phobos', 'de' => 'Phobos', 'es' => 'Fobos', 'fr' => 'Phobos', 'nl' => 'Phobos', 'sv' => 'Phobos'],
            ['canonical' => 'Deimos', 'de' => 'Deimos', 'es' => 'Deimos', 'fr' => 'Deimos', 'nl' => 'Deimos', 'sv' => 'Deimos'],
            ['canonical' => 'Io', 'de' => 'Io', 'es' => 'Io', 'fr' => 'Io', 'nl' => 'Io', 'sv' => 'Io'],
            ['canonical' => 'Europa', 'de' => 'Europa', 'es' => 'Europa', 'fr' => 'Europe', 'nl' => 'Europa', 'sv' => 'Europa'],
            ['canonical' => 'Ganymede', 'de' => 'Ganymed', 'es' => 'Ganímedes', 'fr' => 'Ganymède', 'nl' => 'Ganymedes', 'sv' => 'Ganymedes'],
            ['canonical' => 'Callisto', 'de' => 'Kallisto', 'es' => 'Calisto', 'fr' => 'Callisto', 'nl' => 'Callisto', 'sv' => 'Kallisto'],
            ['canonical' => 'Titan', 'de' => 'Titan', 'es' => 'Titán', 'fr' => 'Titan', 'nl' => 'Titan', 'sv' => 'Titan'],
            ['canonical' => 'Rhea', 'de' => 'Rhea', 'es' => 'Rea', 'fr' => 'Rhéa', 'nl' => 'Rhea', 'sv' => 'Rhea'],
            ['canonical' => 'Iapetus', 'de' => 'Iapetus', 'es' => 'Japeto', 'fr' => 'Japet', 'nl' => 'Iapetus', 'sv' => 'Iapetus'],
            ['canonical' => 'Dione', 'de' => 'Dione', 'es' => 'Dione', 'fr' => 'Dione', 'nl' => 'Dione', 'sv' => 'Dione'],
            ['canonical' => 'Tethys', 'de' => 'Thetis', 'es' => 'Tetis', 'fr' => 'Thétis', 'nl' => 'Tethys', 'sv' => 'Tethys'],
            ['canonical' => 'Enceladus', 'de' => 'Enceladus', 'es' => 'Encélado', 'fr' => 'Encelade', 'nl' => 'Enceladus', 'sv' => 'Enceladus'],
            ['canonical' => 'Mimas', 'de' => 'Mimas', 'es' => 'Mimas', 'fr' => 'Mimas', 'nl' => 'Mimas', 'sv' => 'Mimas'],
            ['canonical' => 'Titania', 'de' => 'Titania', 'es' => 'Titania', 'fr' => 'Titania', 'nl' => 'Titania', 'sv' => 'Titania'],
            ['canonical' => 'Oberon', 'de' => 'Oberon', 'es' => 'Oberón', 'fr' => 'Obéron', 'nl' => 'Oberon', 'sv' => 'Oberon'],
            ['canonical' => 'Umbriel', 'de' => 'Umbriel', 'es' => 'Umbriel', 'fr' => 'Umbriel', 'nl' => 'Umbriel', 'sv' => 'Umbriel'],
            ['canonical' => 'Ariel', 'de' => 'Ariel', 'es' => 'Ariel', 'fr' => 'Ariel', 'nl' => 'Ariel', 'sv' => 'Ariel'],
            ['canonical' => 'Miranda', 'de' => 'Miranda', 'es' => 'Miranda', 'fr' => 'Miranda', 'nl' => 'Miranda', 'sv' => 'Miranda'],
            ['canonical' => 'Triton', 'de' => 'Triton', 'es' => 'Tritón', 'fr' => 'Triton', 'nl' => 'Triton', 'sv' => 'Triton'],
            ['canonical' => 'Charon', 'de' => 'Charon', 'es' => 'Caronte', 'fr' => 'Charon', 'nl' => 'Charon', 'sv' => 'Charon'],
        ];

        foreach ($items as $it) {
            $canonical = $it['canonical'];

            foreach (['de','es','fr','nl','sv'] as $locale) {
                if (! isset($it[$locale]) || $it[$locale] === '') {
                    continue;
                }

                DB::table('object_name_translations')->updateOrInsert([
                    'objectname' => $canonical,
                    'locale' => $locale,
                    'name' => $it[$locale],
                ], [
                    'updated_at' => $now,
                    'created_at' => $now,
                ]);
            }
        }
    }
}
