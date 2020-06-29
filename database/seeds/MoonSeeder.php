<?php

use App\Target;
use Carbon\Carbon;
use App\TargetName;
use App\TargetPartOf;
use Illuminate\Database\Seeder;

class MoonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     */
    public function run()
    {
        // Add target type Moon for moons of planets
        DB::table('target_types')->insert(
            [
                'id' => 'MOON',
                'type' => 'Moon',
                'observation_type' => 'planets',
            ]
        );

        DB::table('observation_types')->insert(
            [
                'type' => 'asteroids',
                'Name' => 'Asteroids',
            ]
        );

        DB::table('target_types')->insert(
            [
                'id' => 'ASTEROID',
                'type' => 'Asteroid',
                'observation_type' => 'asteroids',
            ]
        );

        // Phobos and Deimos
        $this->createMoon('Phobos', 'Mars');
        $this->createMoon('Deimos', 'Mars');

        // Moons of Jupiter: Io, Europa, Ganymede, Callisto
        $this->createMoon('Io', 'Jupiter');
        $this->createMoon('Europa', 'Jupiter');
        $this->createMoon('Ganymede', 'Jupiter');
        $this->createMoon('Callisto', 'Jupiter');

        // Moons of Saturn: Titan, Rhea, Dione, Tethys, Enceladus, and Mimas
        $this->createMoon('Titan', 'Saturn');
        $this->createMoon('Rhea', 'Saturn');
        $this->createMoon('Dione', 'Saturn');
        $this->createMoon('Tethys', 'Saturn');
        $this->createMoon('Enceladus', 'Saturn');
        $this->createMoon('Mimas', 'Saturn');

        // Moons of Uranus: Titania and Oberon, Umbriel and Ariel
        $this->createMoon('Titania', 'Uranus');
        $this->createMoon('Oberon', 'Uranus');

        // Moons of Neptune: Triton
        $this->createMoon('Triton', 'Neptune');
    }

    /**
     * Creates a new moon for a given planet.
     *
     * @parameter string $moon   The name of the moon
     * @parameter string $planet The name of the planet
     *
     * @return None
     */
    public function createMoon(string $moon, string $planet)
    {
        $newmoon = Target::create(['target_name' => $moon, 'target_type' => 'MOON']);

        if ($moon == 'Ganymede') {
            $newmoon->setTranslation('target_name', 'nl', 'Ganymedes');
            $newmoon->setTranslation('target_name', 'es', 'Ganímedes');
            $newmoon->setTranslation('target_name', 'fr', 'Ganymède');
            $newmoon->setTranslation('target_name', 'de', 'Ganymed');

            $newmoon->save();
        }

        TargetName::create(
            [
                'target_id' => $newmoon->id,
                'catalog' => '',
                'catindex' => $newmoon->target_name,
                'altname' => $newmoon->target_name,
            ]
        );

        $planetclass = TargetName::where('altname', $planet)->first();

        TargetPartOf::firstOrCreate(
            [
                'target_id' => $newmoon->id,
                'partof_id' => $planetclass->target_id,
                'created_at' => Carbon::now(),
            ]
        );
    }
}
