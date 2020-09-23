<?php

use App\Models\Target;
use Carbon\Carbon;
use App\Models\TargetName;
use App\Models\TargetPartOf;
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

        DB::table('target_types')->insert(
            [
                'id' => 'DWARF',
                'type' => 'Dwarf Planets',
                'observation_type' => 'asteroids',
            ]
        );

        $pluto = Target::create(
            ['target_name' => 'Pluto', 'target_type' => 'DWARF']
        );
        $pluto->setTranslation('target_name', 'es', 'Plutón');
        $pluto->setTranslation('target_name', 'fr', 'Pluton');

        $pluto->save();

        TargetName::create(
            [
                'target_id' => $pluto->id,
                'catalog' => '',
                'catindex' => 'Pluto',
                'altname' => 'Pluto',
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
