<?php

namespace App\Console\Commands;

use App\Models\CometObservationsOld;
use App\Models\ObservationsOld;
use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use LevelUp\Experience\Models\Achievement;
use LevelUp\Experience\Models\Pivots\AchievementUser;

class updateAchievementsCommand extends Command
{
    protected $signature = 'update:achievements';

    protected $description = 'Updates the achievements';

    public function handle(): void
    {
        $this->info('Updating achievements');

        // Get all DeepskyLog sketches of the week
        $sketches = SketchOfTheWeek::all();

        // Loop over all sketches
        foreach ($sketches as $sketch) {
            try {
                $user = $this->getSketchData($sketch);

                // Get the SketchOfTheWeek achievement
                $achievement = Achievement::where('name', 'DeepskyLog sketch of the week')->get()[0];

                // Add achievement to user
                $this->addAchievementToUser($user, $achievement);
            } catch (Exception) {
                // dump('Unable to add achievement: '.$e->getMessage());
            }
        }

        // Get all DeepskyLog sketches of the week
        $sketches = SketchOfTheMonth::all();

        // Loop over all sketches
        foreach ($sketches as $sketch) {
            try {
                $user = $this->getSketchData($sketch);

                // Get the SketchOfTheWeek achievement
                $achievement = Achievement::where('name', 'DeepskyLog sketch of the month')->get()[0];

                // Add achievement to user
                $this->addAchievementToUser($user, $achievement);
            } catch (Exception $e) {
                dump('Unable to add achievement: '.$e->getMessage());
            }
        }

        // Get all users
        $users = User::all();

        // Loop over all users
        foreach ($users as $user) {
            // Get the Accomplishments for the selected user
            $achievement = Achievement::where('name', 'Top ten observer')->get()[0];
            try {
                //                dump($user->);
                if ($user->isInTopTenOfObservers()) {
                    // Add the top ten observer achievement
                    $this->addAchievementToUser($user, $achievement);
                } else {
                    $this->removeAchievementFromUser($user, $achievement);
                }

                // Calculate number of Caldwell objects seen
                $total = $user->getObservedCountFromCatalog('M');

                // Remove the messier achievements
                $messierGold = Achievement::where('name', 'Messier Gold')->get()[0];
                $messierSilver = Achievement::where('name', 'Messier Silver')->get()[0];
                $messierBronze = Achievement::where('name', 'Messier Bronze')->get()[0];

                $this->setAchievement3($user, $messierGold, $messierSilver, $messierBronze, $total, 110);

                // Calculate number of Messier objects drawn
                $total = $user->getDrawingCountFromCatalog('M');

                // Remove the messier achievements
                $messierGoldDrawing = Achievement::where('name', 'Messier Drawing Gold')->get()[0];
                $messierSilverDrawing = Achievement::where('name', 'Messier Drawing Silver')->get()[0];
                $messierBronzeDrawing = Achievement::where('name', 'Messier Drawing Bronze')->get()[0];

                $this->setAchievement3(
                    $user,
                    $messierGoldDrawing,
                    $messierSilverDrawing,
                    $messierBronzeDrawing,
                    $total,
                    110
                );

                // Calculate number of Caldwell objects seen
                $total = $user->getObservedCountFromCatalog('Caldwell');

                // Remove the Caldwell achievements
                $caldwellGold = Achievement::where('name', 'Caldwell Gold')->get()[0];
                $caldwellSilver = Achievement::where('name', 'Caldwell Silver')->get()[0];
                $caldwellBronze = Achievement::where('name', 'Caldwell Bronze')->get()[0];

                $this->setAchievement3(
                    $user,
                    $caldwellGold,
                    $caldwellSilver,
                    $caldwellBronze,
                    $total,
                    109
                );

                // Calculate number of Caldwell objects drawn
                $total = $user->getDrawingCountFromCatalog('Caldwell');

                // Remove the Caldwell achievements
                $caldwellGoldDrawing = Achievement::where('name', 'Caldwell Drawing Gold')->get()[0];
                $caldwellSilverDrawing = Achievement::where('name', 'Caldwell Drawing Silver')->get()[0];
                $caldwellBronzeDrawing = Achievement::where('name', 'Caldwell Drawing Bronze')->get()[0];

                $this->setAchievement3(
                    $user,
                    $caldwellGoldDrawing,
                    $caldwellSilverDrawing,
                    $caldwellBronzeDrawing,
                    $total,
                    109
                );

                // Calculate number of Herschel 400 objects seen
                $total = $user->getObservedCountFromCatalog('H400');

                $h400Platinum = Achievement::where('name', 'Herschel 400 Platinum')->get()[0];
                $h400Diamond = Achievement::where('name', 'Herschel 400 Diamond')->get()[0];
                $h400Gold = Achievement::where('name', 'Herschel 400 Gold')->get()[0];
                $h400Silver = Achievement::where('name', 'Herschel 400 Silver')->get()[0];
                $h400Bronze = Achievement::where('name', 'Herschel 400 Bronze')->get()[0];

                $this->setAchievement5(
                    $user,
                    $h400Platinum,
                    $h400Diamond,
                    $h400Gold,
                    $h400Silver,
                    $h400Bronze,
                    $total,
                    400
                );

                // Calculate number of Herschel 400 objects drawn
                $total = $user->getDrawingCountFromCatalog('H400');

                $h400PlatinumDrawing = Achievement::where('name', 'Herschel 400 Drawing Platinum')->get()[0];
                $h400DiamondDrawing = Achievement::where('name', 'Herschel 400 Drawing Diamond')->get()[0];
                $h400GoldDrawing = Achievement::where('name', 'Herschel 400 Drawing Gold')->get()[0];
                $h400SilverDrawing = Achievement::where('name', 'Herschel 400 Drawing Silver')->get()[0];
                $h400BronzeDrawing = Achievement::where('name', 'Herschel 400 Drawing Bronze')->get()[0];

                $this->setAchievement5(
                    $user,
                    $h400PlatinumDrawing,
                    $h400DiamondDrawing,
                    $h400GoldDrawing,
                    $h400SilverDrawing,
                    $h400BronzeDrawing,
                    $total,
                    400
                );

                // Calculate number of Herschel II objects seen
                $total = $user->getObservedCountFromCatalog('H400-II');

                $hIIPlatinum = Achievement::where('name', 'Herschel II Platinum')->get()[0];
                $hIIDiamond = Achievement::where('name', 'Herschel II Diamond')->get()[0];
                $hIIGold = Achievement::where('name', 'Herschel II Gold')->get()[0];
                $hIISilver = Achievement::where('name', 'Herschel II Silver')->get()[0];
                $hIIBronze = Achievement::where('name', 'Herschel II Bronze')->get()[0];

                $this->setAchievement5(
                    $user,
                    $hIIPlatinum,
                    $hIIDiamond,
                    $hIIGold,
                    $hIISilver,
                    $hIIBronze,
                    $total,
                    400
                );

                // Calculate number of Herschel II objects drawn
                $total = $user->getDrawingCountFromCatalog('H400-II');

                $hIIPlatinumDrawing = Achievement::where('name', 'Herschel II Drawing Platinum')->get()[0];
                $hIIDiamondDrawing = Achievement::where('name', 'Herschel II Drawing Diamond')->get()[0];
                $hIIGoldDrawing = Achievement::where('name', 'Herschel II Drawing Gold')->get()[0];
                $hIISilverDrawing = Achievement::where('name', 'Herschel II Drawing Silver')->get()[0];
                $hIIBronzeDrawing = Achievement::where('name', 'Herschel II Drawing Bronze')->get()[0];

                $this->setAchievement5(
                    $user,
                    $hIIPlatinumDrawing,
                    $hIIDiamondDrawing,
                    $hIIGoldDrawing,
                    $hIISilverDrawing,
                    $hIIBronzeDrawing,
                    $total,
                    400
                );

                // Calculate total number of drawings
                $total = $user->getTotalNumberOfDrawings();

                $achievement10 = Achievement::where('name', 'Drawing 1')->get()[0];
                $achievement9 = Achievement::where('name', 'Drawings 10')->get()[0];
                $achievement8 = Achievement::where('name', 'Drawings 25')->get()[0];
                $achievement7 = Achievement::where('name', 'Drawings 50')->get()[0];
                $achievement6 = Achievement::where('name', 'Drawings 100')->get()[0];
                $achievement5 = Achievement::where('name', 'Drawings 250')->get()[0];
                $achievement4 = Achievement::where('name', 'Drawings 500')->get()[0];
                $achievement3 = Achievement::where('name', 'Drawings 1000')->get()[0];
                $achievement2 = Achievement::where('name', 'Drawings 2500')->get()[0];
                $achievement1 = Achievement::where('name', 'Drawings 5000')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    5000,
                    2500,
                    1000,
                    500,
                    250,
                    100,
                    50,
                    25,
                    10,
                    1
                );

                // Calculate number of open clusters seen
                $total = $user->getOpenClusterObservations();

                $achievement10 = Achievement::where('name', 'Open Cluster 1')->get()[0];
                $achievement9 = Achievement::where('name', 'Open Clusters 5')->get()[0];
                $achievement8 = Achievement::where('name', 'Open Clusters 10')->get()[0];
                $achievement7 = Achievement::where('name', 'Open Clusters 25')->get()[0];
                $achievement6 = Achievement::where('name', 'Open Clusters 50')->get()[0];
                $achievement5 = Achievement::where('name', 'Open Clusters 100')->get()[0];
                $achievement4 = Achievement::where('name', 'Open Clusters 200')->get()[0];
                $achievement3 = Achievement::where('name', 'Open Clusters 500')->get()[0];
                $achievement2 = Achievement::where('name', 'Open Clusters 1000')->get()[0];
                $achievement1 = Achievement::where('name', 'Open Clusters 2500')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    2500,
                    1000,
                    500,
                    200,
                    100,
                    50,
                    25,
                    10,
                    5,
                    1
                );

                // Calculate number of open clusters drawn
                $total = $user->getOpenClusterDrawings();

                $achievement10 = Achievement::where('name', 'Open Cluster 1 Drawing')->get()[0];
                $achievement9 = Achievement::where('name', 'Open Clusters 5 Drawings')->get()[0];
                $achievement8 = Achievement::where('name', 'Open Clusters 10 Drawings')->get()[0];
                $achievement7 = Achievement::where('name', 'Open Clusters 25 Drawings')->get()[0];
                $achievement6 = Achievement::where('name', 'Open Clusters 50 Drawings')->get()[0];
                $achievement5 = Achievement::where('name', 'Open Clusters 100 Drawings')->get()[0];
                $achievement4 = Achievement::where('name', 'Open Clusters 200 Drawings')->get()[0];
                $achievement3 = Achievement::where('name', 'Open Clusters 500 Drawings')->get()[0];
                $achievement2 = Achievement::where('name', 'Open Clusters 1000 Drawings')->get()[0];
                $achievement1 = Achievement::where('name', 'Open Clusters 2500 Drawings')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    2500,
                    1000,
                    500,
                    200,
                    100,
                    50,
                    25,
                    10,
                    5,
                    1
                );

                // Calculate number of globular clusters seen
                $total = $user->getGlobularClusterObservations();

                $achievement10 = Achievement::where('name', 'Globular Cluster 1')->get()[0];
                $achievement9 = Achievement::where('name', 'Globular Clusters 3')->get()[0];
                $achievement8 = Achievement::where('name', 'Globular Clusters 5')->get()[0];
                $achievement7 = Achievement::where('name', 'Globular Clusters 10')->get()[0];
                $achievement6 = Achievement::where('name', 'Globular Clusters 15')->get()[0];
                $achievement5 = Achievement::where('name', 'Globular Clusters 25')->get()[0];
                $achievement4 = Achievement::where('name', 'Globular Clusters 50')->get()[0];
                $achievement3 = Achievement::where('name', 'Globular Clusters 75')->get()[0];
                $achievement2 = Achievement::where('name', 'Globular Clusters 100')->get()[0];
                $achievement1 = Achievement::where('name', 'Globular Clusters 150')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    150,
                    100,
                    75,
                    50,
                    25,
                    15,
                    10,
                    5,
                    3,
                    1
                );

                // Calculate number of globular clusters drawn
                $total = $user->getGlobularClusterDrawings();

                $achievement10 = Achievement::where('name', 'Globular Cluster 1 Drawing')->get()[0];
                $achievement9 = Achievement::where('name', 'Globular Clusters 3 Drawings')->get()[0];
                $achievement8 = Achievement::where('name', 'Globular Clusters 5 Drawings')->get()[0];
                $achievement7 = Achievement::where('name', 'Globular Clusters 10 Drawings')->get()[0];
                $achievement6 = Achievement::where('name', 'Globular Clusters 15 Drawings')->get()[0];
                $achievement5 = Achievement::where('name', 'Globular Clusters 25 Drawings')->get()[0];
                $achievement4 = Achievement::where('name', 'Globular Clusters 50 Drawings')->get()[0];
                $achievement3 = Achievement::where('name', 'Globular Clusters 75 Drawings')->get()[0];
                $achievement2 = Achievement::where('name', 'Globular Clusters 100 Drawings')->get()[0];
                $achievement1 = Achievement::where('name', 'Globular Clusters 150 Drawings')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    150,
                    100,
                    75,
                    50,
                    25,
                    15,
                    10,
                    5,
                    3,
                    1
                );

                // Calculate number of planetary nebulae seen
                $total = $user->getPlanetaryNebulaObservations();

                $achievement10 = Achievement::where('name', 'Planetary Nebula 1')->get()[0];
                $achievement9 = Achievement::where('name', 'Planetary Nebula 3')->get()[0];
                $achievement8 = Achievement::where('name', 'Planetary Nebula 5')->get()[0];
                $achievement7 = Achievement::where('name', 'Planetary Nebula 10')->get()[0];
                $achievement6 = Achievement::where('name', 'Planetary Nebula 25')->get()[0];
                $achievement5 = Achievement::where('name', 'Planetary Nebula 50')->get()[0];
                $achievement4 = Achievement::where('name', 'Planetary Nebula 100')->get()[0];
                $achievement3 = Achievement::where('name', 'Planetary Nebula 250')->get()[0];
                $achievement2 = Achievement::where('name', 'Planetary Nebula 500')->get()[0];
                $achievement1 = Achievement::where('name', 'Planetary Nebula 1000')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    1000,
                    500,
                    250,
                    100,
                    50,
                    25,
                    10,
                    5,
                    3,
                    1
                );

                // Calculate number of planetary nebulae drawn
                $total = $user->getPlanetaryNebulaDrawings();

                $achievement10 = Achievement::where('name', 'Planetary Nebula 1 Drawings')->get()[0];
                $achievement9 = Achievement::where('name', 'Planetary Nebula 3 Drawings')->get()[0];
                $achievement8 = Achievement::where('name', 'Planetary Nebula 5 Drawings')->get()[0];
                $achievement7 = Achievement::where('name', 'Planetary Nebula 10 Drawings')->get()[0];
                $achievement6 = Achievement::where('name', 'Planetary Nebula 25 Drawings')->get()[0];
                $achievement5 = Achievement::where('name', 'Planetary Nebula 50 Drawings')->get()[0];
                $achievement4 = Achievement::where('name', 'Planetary Nebula 100 Drawings')->get()[0];
                $achievement3 = Achievement::where('name', 'Planetary Nebula 250 Drawings')->get()[0];
                $achievement2 = Achievement::where('name', 'Planetary Nebula 500 Drawings')->get()[0];
                $achievement1 = Achievement::where('name', 'Planetary Nebula 1000 Drawings')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    1000,
                    500,
                    250,
                    100,
                    50,
                    25,
                    10,
                    5,
                    3,
                    1
                );

                // Calculate number of galaxies seen
                $total = $user->getGalaxyObservations();

                $achievement10 = Achievement::where('name', 'Galaxy 1')->get()[0];
                $achievement9 = Achievement::where('name', 'Galaxies 10')->get()[0];
                $achievement8 = Achievement::where('name', 'Galaxies 25')->get()[0];
                $achievement7 = Achievement::where('name', 'Galaxies 50')->get()[0];
                $achievement6 = Achievement::where('name', 'Galaxies 100')->get()[0];
                $achievement5 = Achievement::where('name', 'Galaxies 250')->get()[0];
                $achievement4 = Achievement::where('name', 'Galaxies 500')->get()[0];
                $achievement3 = Achievement::where('name', 'Galaxies 1000')->get()[0];
                $achievement2 = Achievement::where('name', 'Galaxies 2500')->get()[0];
                $achievement1 = Achievement::where('name', 'Galaxies 5000')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    5000,
                    2500,
                    1000,
                    500,
                    250,
                    100,
                    50,
                    25,
                    10,
                    1
                );

                // Calculate number of galaxies drawn
                $total = $user->getGalaxyDrawings();

                $achievement10 = Achievement::where('name', 'Galaxy 1 Drawing')->get()[0];
                $achievement9 = Achievement::where('name', 'Galaxies 10 Drawings')->get()[0];
                $achievement8 = Achievement::where('name', 'Galaxies 25 Drawings')->get()[0];
                $achievement7 = Achievement::where('name', 'Galaxies 50 Drawings')->get()[0];
                $achievement6 = Achievement::where('name', 'Galaxies 100 Drawings')->get()[0];
                $achievement5 = Achievement::where('name', 'Galaxies 250 Drawings')->get()[0];
                $achievement4 = Achievement::where('name', 'Galaxies 500 Drawings')->get()[0];
                $achievement3 = Achievement::where('name', 'Galaxies 1000 Drawings')->get()[0];
                $achievement2 = Achievement::where('name', 'Galaxies 2500 Drawings')->get()[0];
                $achievement1 = Achievement::where('name', 'Galaxies 5000 Drawings')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    5000,
                    2500,
                    1000,
                    500,
                    250,
                    100,
                    50,
                    25,
                    10,
                    1
                );

                // Calculate number of nebulae seen
                $total = $user->getNebulaObservations();

                $achievement10 = Achievement::where('name', 'Nebula 1')->get()[0];
                $achievement9 = Achievement::where('name', 'Nebulae 5')->get()[0];
                $achievement8 = Achievement::where('name', 'Nebulae 10')->get()[0];
                $achievement7 = Achievement::where('name', 'Nebulae 25')->get()[0];
                $achievement6 = Achievement::where('name', 'Nebulae 50')->get()[0];
                $achievement5 = Achievement::where('name', 'Nebulae 75')->get()[0];
                $achievement4 = Achievement::where('name', 'Nebulae 100')->get()[0];
                $achievement3 = Achievement::where('name', 'Nebulae 150')->get()[0];
                $achievement2 = Achievement::where('name', 'Nebulae 200')->get()[0];
                $achievement1 = Achievement::where('name', 'Nebulae 300')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    300,
                    200,
                    150,
                    100,
                    75,
                    50,
                    25,
                    10,
                    5,
                    1
                );

                // Calculate number of nebulae drawn
                $total = $user->getNebulaDrawings();

                $achievement10 = Achievement::where('name', 'Nebula 1 Drawing')->get()[0];
                $achievement9 = Achievement::where('name', 'Nebulae 5 Drawings')->get()[0];
                $achievement8 = Achievement::where('name', 'Nebulae 10 Drawings')->get()[0];
                $achievement7 = Achievement::where('name', 'Nebulae 25 Drawings')->get()[0];
                $achievement6 = Achievement::where('name', 'Nebulae 50 Drawings')->get()[0];
                $achievement5 = Achievement::where('name', 'Nebulae 75 Drawings')->get()[0];
                $achievement4 = Achievement::where('name', 'Nebulae 100 Drawings')->get()[0];
                $achievement3 = Achievement::where('name', 'Nebulae 150 Drawings')->get()[0];
                $achievement2 = Achievement::where('name', 'Nebulae 200 Drawings')->get()[0];
                $achievement1 = Achievement::where('name', 'Nebulae 300 Drawings')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    300,
                    200,
                    150,
                    100,
                    75,
                    50,
                    25,
                    10,
                    5,
                    1
                );

                // Calculate number of unique objects seen
                $total = $user->getUniqueObjectsObservations();

                $achievement10 = Achievement::where('name', 'Object 1')->get()[0];
                $achievement9 = Achievement::where('name', 'Objects 10')->get()[0];
                $achievement8 = Achievement::where('name', 'Objects 25')->get()[0];
                $achievement7 = Achievement::where('name', 'Objects 50')->get()[0];
                $achievement6 = Achievement::where('name', 'Objects 100')->get()[0];
                $achievement5 = Achievement::where('name', 'Objects 250')->get()[0];
                $achievement4 = Achievement::where('name', 'Objects 500')->get()[0];
                $achievement3 = Achievement::where('name', 'Objects 1000')->get()[0];
                $achievement2 = Achievement::where('name', 'Objects 2500')->get()[0];
                $achievement1 = Achievement::where('name', 'Objects 5000')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    5000,
                    2500,
                    1000,
                    500,
                    250,
                    100,
                    50,
                    25,
                    10,
                    1
                );

                // Calculate number of unique objects drawn
                $total = $user->getUniqueObjectsDrawings();

                $achievement10 = Achievement::where('name', 'Object 1 Drawing')->get()[0];
                $achievement9 = Achievement::where('name', 'Objects 10 Drawings')->get()[0];
                $achievement8 = Achievement::where('name', 'Objects 25 Drawings')->get()[0];
                $achievement7 = Achievement::where('name', 'Objects 50 Drawings')->get()[0];
                $achievement6 = Achievement::where('name', 'Objects 100 Drawings')->get()[0];
                $achievement5 = Achievement::where('name', 'Objects 250 Drawings')->get()[0];
                $achievement4 = Achievement::where('name', 'Objects 500 Drawings')->get()[0];
                $achievement3 = Achievement::where('name', 'Objects 1000 Drawings')->get()[0];
                $achievement2 = Achievement::where('name', 'Objects 2500 Drawings')->get()[0];
                $achievement1 = Achievement::where('name', 'Objects 5000 Drawings')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    5000,
                    2500,
                    1000,
                    500,
                    250,
                    100,
                    50,
                    25,
                    10,
                    1
                );

                // Calculate number of comets seen
                $total = $user->getCometObservations();

                $achievement10 = Achievement::where('name', 'Comet 1')->get()[0];
                $achievement9 = Achievement::where('name', 'Comets 10')->get()[0];
                $achievement8 = Achievement::where('name', 'Comets 25')->get()[0];
                $achievement7 = Achievement::where('name', 'Comets 50')->get()[0];
                $achievement6 = Achievement::where('name', 'Comets 100')->get()[0];
                $achievement5 = Achievement::where('name', 'Comets 250')->get()[0];
                $achievement4 = Achievement::where('name', 'Comets 500')->get()[0];
                $achievement3 = Achievement::where('name', 'Comets 1000')->get()[0];
                $achievement2 = Achievement::where('name', 'Comets 2500')->get()[0];
                $achievement1 = Achievement::where('name', 'Comets 5000')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    5000,
                    2500,
                    1000,
                    500,
                    250,
                    100,
                    50,
                    25,
                    10,
                    1
                );

                // Calculate number of comets drawn
                $total = $user->getCometDrawings();

                $achievement10 = Achievement::where('name', 'Comet 1 Drawing')->get()[0];
                $achievement9 = Achievement::where('name', 'Comets 10 Drawings')->get()[0];
                $achievement8 = Achievement::where('name', 'Comets 25 Drawings')->get()[0];
                $achievement7 = Achievement::where('name', 'Comets 50 Drawings')->get()[0];
                $achievement6 = Achievement::where('name', 'Comets 100 Drawings')->get()[0];
                $achievement5 = Achievement::where('name', 'Comets 250 Drawings')->get()[0];
                $achievement4 = Achievement::where('name', 'Comets 500 Drawings')->get()[0];
                $achievement3 = Achievement::where('name', 'Comets 1000 Drawings')->get()[0];
                $achievement2 = Achievement::where('name', 'Comets 2500 Drawings')->get()[0];
                $achievement1 = Achievement::where('name', 'Comets 5000 Drawings')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    5000,
                    2500,
                    1000,
                    500,
                    250,
                    100,
                    50,
                    25,
                    10,
                    1
                );

                // Calculate number of unique comets seen
                $total = $user->getUniqueCometObservations();

                $achievement10 = Achievement::where('name', 'Different Comets 1')->get()[0];
                $achievement9 = Achievement::where('name', 'Different Comets 10')->get()[0];
                $achievement8 = Achievement::where('name', 'Different Comets 25')->get()[0];
                $achievement7 = Achievement::where('name', 'Different Comets 50')->get()[0];
                $achievement6 = Achievement::where('name', 'Different Comets 100')->get()[0];
                $achievement5 = Achievement::where('name', 'Different Comets 250')->get()[0];
                $achievement4 = Achievement::where('name', 'Different Comets 500')->get()[0];
                $achievement3 = Achievement::where('name', 'Different Comets 1000')->get()[0];
                $achievement2 = Achievement::where('name', 'Different Comets 2500')->get()[0];
                $achievement1 = Achievement::where('name', 'Different Comets 5000')->get()[0];

                $this->setAchievement10(
                    $user,
                    $achievement1,
                    $achievement2,
                    $achievement3,
                    $achievement4,
                    $achievement5,
                    $achievement6,
                    $achievement7,
                    $achievement8,
                    $achievement9,
                    $achievement10,
                    $total,
                    5000,
                    2500,
                    1000,
                    500,
                    250,
                    100,
                    50,
                    25,
                    10,
                    1
                );
            } catch (Exception) {
                //                dump('Unable to add achievement: '.$e->getMessage());
            }
        }

        $this->info('Achievements updated');
    }

    /**
     * Retrieves the user associated with a given sketch.
     *
     * @param  mixed  $sketch  The sketch object.
     * @return User The user object associated with the sketch.
     *
     * @throws Exception If the sketch observation ID is invalid or if there is an error retrieving the observation or user.
     */
    public function getSketchData(mixed $sketch): User
    {
        if ($sketch->observation_id < 0) {
            $observation = CometObservationsOld::where('id', -$sketch->observation_id)->first();
        } else {
            // Get the observation from the observation_id column
            $observation = ObservationsOld::where('id', $sketch->observation_id)->first();
        }
        // Get observerid from the observation
        $observerid = html_entity_decode($observation->observerid);

        // Get user from the observerid
        return User::where('username', $observerid)->first();
    }

    /**
     * Adds an achievement to a user.
     *
     * @param  mixed  $user  The user to whom the achievement is being added.
     * @param  mixed  $achievement  The achievement to be added.
     *
     * @throws Exception If there is an error adding the achievement.
     */
    public function addAchievementToUser(mixed $user, mixed $achievement): void
    {
        // Add achievement to user
        try {
            $user->grantAchievement($achievement);
        } catch (Exception) {
            // dump('Unable to add achievement: '.$e->getMessage());
        }
    }

    /**
     * Removes an achievement from a user.
     *
     * @param  mixed  $user  The user object.
     * @param  mixed  $achievement  The achievement object.
     */
    private function removeAchievementFromUser(mixed $user, mixed $achievement): void
    {
        // Remove achievement from user
        try {
            AchievementUser::where('user_id', $user->id)->where('achievement_id', $achievement->id)->delete();
        } catch (Exception) {
            // dump('Unable to remove achievement: '.$e->getMessage());
        }
    }

    /**
     * Sets the achievement level for a user based on their totals and amounts (for 5 achievement steps)
     *
     * @param  mixed  $user  The user for whom the achievement level is being set.
     * @param  mixed  $gold  The achievement for the gold level.
     * @param  mixed  $silver  The achievement for the silver level.
     * @param  mixed  $bronze  The achievement for the bronze level.
     * @param  int  $total  The total achievement points of the user.
     * @param  int  $max  The maximum achievement points required to achieve the gold level.
     *
     * @throws Exception
     */
    public function setAchievement3(
        mixed $user,
        mixed $gold,
        mixed $silver,
        mixed $bronze,
        int $total,
        int $max
    ): void {
        // Re-add the achievements
        if ($total == $max) {
            $this->addAchievementToUser($user, $gold);
            $this->removeAchievementFromUser($user, $silver);
            $this->removeAchievementFromUser($user, $bronze);
        } elseif ($total >= 50) {
            $this->addAchievementToUser($user, $silver);
            $this->removeAchievementFromUser($user, $gold);
            $this->removeAchievementFromUser($user, $bronze);
        } elseif ($total >= 25) {
            $this->addAchievementToUser($user, $bronze);
            $this->removeAchievementFromUser($user, $gold);
            $this->removeAchievementFromUser($user, $silver);
        } else {
            $this->removeAchievementFromUser($user, $gold);
            $this->removeAchievementFromUser($user, $silver);
            $this->removeAchievementFromUser($user, $bronze);
        }
    }

    /**
     * Sets the achievement level for a user based on their totals and amounts (for 5 achievement steps)
     *
     * @param  mixed  $user  The user object.
     * @param  mixed  $platinum  The achievement object for platinum level.
     * @param  mixed  $diamond  The achievement object for diamond level.
     * @param  mixed  $gold  The achievement object for gold level.
     * @param  mixed  $silver  The achievement object for silver level.
     * @param  mixed  $bronze  The achievement object for bronze level.
     * @param  int  $total  The total points of the user.
     * @param  int  $max  The maximum points to achieve platinum level.
     *
     * @throws Exception
     */
    public function setAchievement5(
        mixed $user,
        mixed $platinum,
        mixed $diamond,
        mixed $gold,
        mixed $silver,
        mixed $bronze,
        int $total,
        int $max
    ): void {
        // Re-add the achievements
        if ($total == $max) {
            $this->addAchievementToUser($user, $platinum);
            $this->removeAchievementFromUser($user, $diamond);
            $this->removeAchievementFromUser($user, $gold);
            $this->removeAchievementFromUser($user, $silver);
            $this->removeAchievementFromUser($user, $bronze);
        } elseif ($total >= 200) {
            $this->addAchievementToUser($user, $diamond);
            $this->removeAchievementFromUser($user, $platinum);
            $this->removeAchievementFromUser($user, $silver);
            $this->removeAchievementFromUser($user, $gold);
            $this->removeAchievementFromUser($user, $bronze);
        } elseif ($total >= 100) {
            $this->addAchievementToUser($user, $gold);
            $this->removeAchievementFromUser($user, $platinum);
            $this->removeAchievementFromUser($user, $silver);
            $this->removeAchievementFromUser($user, $diamond);
            $this->removeAchievementFromUser($user, $bronze);
        } elseif ($total >= 50) {
            $this->addAchievementToUser($user, $silver);
            $this->removeAchievementFromUser($user, $platinum);
            $this->removeAchievementFromUser($user, $diamond);
            $this->removeAchievementFromUser($user, $gold);
            $this->removeAchievementFromUser($user, $bronze);
        } elseif ($total >= 25) {
            $this->removeAchievementFromUser($user, $platinum);
            $this->removeAchievementFromUser($user, $diamond);
            $this->addAchievementToUser($user, $bronze);
            $this->removeAchievementFromUser($user, $gold);
            $this->removeAchievementFromUser($user, $silver);
        } else {
            $this->removeAchievementFromUser($user, $platinum);
            $this->removeAchievementFromUser($user, $diamond);
            $this->removeAchievementFromUser($user, $gold);
            $this->removeAchievementFromUser($user, $silver);
            $this->removeAchievementFromUser($user, $bronze);
        }
    }

    /**
     * Sets the achievements for the given user based on the total and amounts (for 10 achievement steps).
     *
     * @param  mixed  $user  The user for whom the achievements are set.
     * @param  mixed  $achievement1  The first achievement.
     * @param  mixed  $achievement2  The second achievement.
     * @param  mixed  $achievement3  The third achievement.
     * @param  mixed  $achievement4  The fourth achievement.
     * @param  mixed  $achievement5  The fifth achievement.
     * @param  mixed  $achievement6  The sixth achievement.
     * @param  mixed  $achievement7  The seventh achievement.
     * @param  mixed  $achievement8  The eighth achievement.
     * @param  mixed  $achievement9  The ninth achievement.
     * @param  mixed  $achievement10  The tenth achievement.
     * @param  int  $total  The total amount.
     * @param  int  $amount1  The amount for the first achievement.
     * @param  int  $amount2  The amount for the second achievement.
     * @param  int  $amount3  The amount for the third achievement.
     * @param  int  $amount4  The amount for the fourth achievement.
     * @param  int  $amount5  The amount for the fifth achievement.
     * @param  int  $amount6  The amount for the sixth achievement.
     * @param  int  $amount7  The amount for the seventh achievement.
     * @param  int  $amount8  The amount for the eighth achievement.
     * @param  int  $amount9  The amount for the ninth achievement.
     * @param  int  $amount10  The amount for the tenth achievement.
     *
     * @throws Exception
     */
    public function setAchievement10(
        mixed $user,
        mixed $achievement1,
        mixed $achievement2,
        mixed $achievement3,
        mixed $achievement4,
        mixed $achievement5,
        mixed $achievement6,
        mixed $achievement7,
        mixed $achievement8,
        mixed $achievement9,
        mixed $achievement10,
        int $total,
        int $amount1,
        int $amount2,
        int $amount3,
        int $amount4,
        int $amount5,
        int $amount6,
        int $amount7,
        int $amount8,
        int $amount9,
        int $amount10
    ): void {
        // Re-add the achievements
        if ($total == $amount1) {
            $this->removeAchievements(
                $user,
                $achievement1,
                $achievement2,
                $achievement3,
                $achievement4,
                $achievement5,
                $achievement6,
                $achievement7,
                $achievement8,
                $achievement9,
                $achievement10
            );
        } elseif ($total >= $amount2) {
            $this->removeAchievements(
                $user,
                $achievement2,
                $achievement1,
                $achievement3,
                $achievement4,
                $achievement5,
                $achievement6,
                $achievement7,
                $achievement8,
                $achievement9,
                $achievement10
            );
        } elseif ($total >= $amount3) {
            $this->removeAchievements(
                $user,
                $achievement3,
                $achievement1,
                $achievement2,
                $achievement4,
                $achievement5,
                $achievement6,
                $achievement7,
                $achievement8,
                $achievement9,
                $achievement10
            );
        } elseif ($total >= $amount4) {
            $this->removeAchievements(
                $user,
                $achievement4,
                $achievement1,
                $achievement2,
                $achievement3,
                $achievement5,
                $achievement6,
                $achievement7,
                $achievement8,
                $achievement9,
                $achievement10
            );
        } elseif ($total >= $amount5) {
            $this->removeAchievements(
                $user,
                $achievement5,
                $achievement1,
                $achievement2,
                $achievement3,
                $achievement4,
                $achievement6,
                $achievement7,
                $achievement8,
                $achievement9,
                $achievement10
            );
        } elseif ($total >= $amount6) {
            $this->removeAchievements(
                $user,
                $achievement6,
                $achievement1,
                $achievement2,
                $achievement3,
                $achievement4,
                $achievement5,
                $achievement7,
                $achievement8,
                $achievement9,
                $achievement10
            );
        } elseif ($total >= $amount7) {
            $this->removeAchievements(
                $user,
                $achievement7,
                $achievement1,
                $achievement2,
                $achievement3,
                $achievement4,
                $achievement5,
                $achievement6,
                $achievement8,
                $achievement9,
                $achievement10
            );
        } elseif ($total >= $amount8) {
            $this->removeAchievements(
                $user,
                $achievement8,
                $achievement1,
                $achievement2,
                $achievement3,
                $achievement4,
                $achievement5,
                $achievement6,
                $achievement7,
                $achievement9,
                $achievement10
            );
        } elseif ($total >= $amount9) {
            $this->removeAchievements(
                $user,
                $achievement9,
                $achievement1,
                $achievement2,
                $achievement3,
                $achievement4,
                $achievement5,
                $achievement6,
                $achievement7,
                $achievement8,
                $achievement10
            );
        } elseif ($total >= $amount10) {
            $this->removeAchievements(
                $user,
                $achievement10,
                $achievement1,
                $achievement2,
                $achievement3,
                $achievement4,
                $achievement5,
                $achievement6,
                $achievement7,
                $achievement8,
                $achievement9
            );
        } else {
            $this->removeAllAchievements(
                $user,
                $achievement1,
                $achievement2,
                $achievement3,
                $achievement4,
                $achievement5,
                $achievement6,
                $achievement7,
                $achievement8,
                $achievement9
            );
            $this->removeAchievementFromUser($user, $achievement10);
        }
    }

    /**
     * Set the first achievement and remove all other 9 achievements from a user.
     *
     * @param  mixed  $user  The user from whom to remove the achievements.
     * @param  mixed  $achievement1  The first achievement to remove.
     * @param  mixed  $achievement2  The second achievement to remove.
     * @param  mixed  $achievement3  The third achievement to remove.
     * @param  mixed  $achievement4  The fourth achievement to remove.
     * @param  mixed  $achievement5  The fifth achievement to remove.
     * @param  mixed  $achievement6  The sixth achievement to remove.
     * @param  mixed  $achievement7  The seventh achievement to remove.
     * @param  mixed  $achievement8  The eighth achievement to remove.
     * @param  mixed  $achievement9  The ninth achievement to remove.
     * @param  mixed  $achievement10  The tenth achievement to remove.
     *
     * @throws Exception
     */
    public function removeAchievements(
        mixed $user,
        mixed $achievement1,
        mixed $achievement2,
        mixed $achievement3,
        mixed $achievement4,
        mixed $achievement5,
        mixed $achievement6,
        mixed $achievement7,
        mixed $achievement8,
        mixed $achievement9,
        mixed $achievement10
    ): void {
        $this->addAchievementToUser($user, $achievement1);
        $this->removeAllAchievements(
            $user,
            $achievement2,
            $achievement3,
            $achievement4,
            $achievement5,
            $achievement6,
            $achievement7,
            $achievement8,
            $achievement9,
            $achievement10
        );
    }

    /**
     * Removes all achievements from a user.
     *
     * @param  mixed  $user  The user from whom to remove the achievements.
     * @param  mixed  $achievement1  The first achievement to remove.
     * @param  mixed  $achievement2  The second achievement to remove.
     * @param  mixed  $achievement3  The third achievement to remove.
     * @param  mixed  $achievement4  The fourth achievement to remove.
     * @param  mixed  $achievement5  The fifth achievement to remove.
     * @param  mixed  $achievement6  The sixth achievement to remove.
     * @param  mixed  $achievement7  The seventh achievement to remove.
     * @param  mixed  $achievement8  The eighth achievement to remove.
     * @param  mixed  $achievement9  The ninth achievement to remove.
     */
    public function removeAllAchievements(
        mixed $user,
        mixed $achievement1,
        mixed $achievement2,
        mixed $achievement3,
        mixed $achievement4,
        mixed $achievement5,
        mixed $achievement6,
        mixed $achievement7,
        mixed $achievement8,
        mixed $achievement9
    ): void {
        $this->removeAchievementFromUser($user, $achievement1);
        $this->removeAchievementFromUser($user, $achievement2);
        $this->removeAchievementFromUser($user, $achievement3);
        $this->removeAchievementFromUser($user, $achievement4);
        $this->removeAchievementFromUser($user, $achievement5);
        $this->removeAchievementFromUser($user, $achievement6);
        $this->removeAchievementFromUser($user, $achievement7);
        $this->removeAchievementFromUser($user, $achievement8);
        $this->removeAchievementFromUser($user, $achievement9);
    }
}
