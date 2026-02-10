<?php

namespace App\Console\Commands;

use App\Models\CometObservationsOld;
use App\Models\ObservationsOld;
use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use LevelUp\Experience\Models\Achievement;
use LevelUp\Experience\Models\Pivots\AchievementUser;

class updateAchievementsCommand extends Command
{
    protected $signature = 'update:achievements';

    protected $description = 'Updates the achievements';
    /**
     * Cached achievements keyed by name to avoid repeating DB queries.
     *
     * @var \Illuminate\Support\Collection|null
     */
    private $achievementMap = null;
    /**
     * Cached counts maps to avoid per-user queries.
     */
    private $catalogObservedMap = [];
    private $catalogDrawingsMap = [];
    private $typeObservedMap = [];
    private $typeDrawingsMap = [];
    private $totalDrawingsMap = [];
    private $uniqueObjectsMap = [];
    private $uniqueObjectsDrawingsMap = [];
    private $cometObservationsMap = [];
    private $cometDrawingsMap = [];
    private $uniqueCometMap = [];
    private $userAchievementsMap = [];

    public function handle(): void
    {
        $this->info('Updating achievements');

        // Load all achievements once and keep in memory to avoid repeated DB queries
        $this->achievementMap = Achievement::all()->keyBy('name');

        // Preload all existing user achievements to avoid per-user queries
        $existingAchievements = AchievementUser::all();
        foreach ($existingAchievements as $ua) {
            if (!isset($this->userAchievementsMap[$ua->user_id])) {
                $this->userAchievementsMap[$ua->user_id] = [];
            }
            $this->userAchievementsMap[$ua->user_id][$ua->achievement_id] = true;
        }

        // Get all DeepskyLog sketches of the week
        $sketches = SketchOfTheWeek::all();

        // Preload observations and users for sketches to avoid N+1 queries
        $sketchObsIds = $sketches->pluck('observation_id')->filter(fn($id) => $id > 0)->all();
        $sketchCometObsIds = $sketches->pluck('observation_id')->filter(fn($id) => $id < 0)->map(fn($id) => -$id)->all();
        
        $sketchObservations = ObservationsOld::whereIn('id', $sketchObsIds)->get()->keyBy('id');
        $sketchCometObservations = CometObservationsOld::whereIn('id', $sketchCometObsIds)->get()->keyBy('id');
        
        $observerIds = $sketchObservations->pluck('observerid')
            ->merge($sketchCometObservations->pluck('observerid'))
            ->map(fn($id) => html_entity_decode($id))
            ->unique()
            ->all();
        $sketchUsers = User::whereIn('username', $observerIds)->get()->keyBy('username');

        // Loop over all sketches
        foreach ($sketches as $sketch) {
            try {
                $user = $this->getSketchDataFromCache($sketch, $sketchObservations, $sketchCometObservations, $sketchUsers);

                // Get the SketchOfTheWeek achievement
                $achievement = $this->getAchievement('DeepskyLog sketch of the week');

                // Add achievement to user
                $this->addAchievementToUser($user, $achievement);
            } catch (Exception) {
                // dump('Unable to add achievement: '.$e->getMessage());
            }
        }

        // Get all DeepskyLog sketches of the month
        $sketches = SketchOfTheMonth::all();

        // Preload observations and users for sketches to avoid N+1 queries
        $sketchObsIds = $sketches->pluck('observation_id')->filter(fn($id) => $id > 0)->all();
        $sketchCometObsIds = $sketches->pluck('observation_id')->filter(fn($id) => $id < 0)->map(fn($id) => -$id)->all();
        
        $sketchObservations = ObservationsOld::whereIn('id', $sketchObsIds)->get()->keyBy('id');
        $sketchCometObservations = CometObservationsOld::whereIn('id', $sketchCometObsIds)->get()->keyBy('id');
        
        $observerIds = $sketchObservations->pluck('observerid')
            ->merge($sketchCometObservations->pluck('observerid'))
            ->map(fn($id) => html_entity_decode($id))
            ->unique()
            ->all();
        $sketchUsers = User::whereIn('username', $observerIds)->get()->keyBy('username');

        // Loop over all sketches
        foreach ($sketches as $sketch) {
            try {
                $user = $this->getSketchDataFromCache($sketch, $sketchObservations, $sketchCometObservations, $sketchUsers);

                // Get the SketchOfTheWeek achievement
                $achievement = $this->getAchievement('DeepskyLog sketch of the month');

                // Add achievement to user
                $this->addAchievementToUser($user, $achievement);
            } catch (Exception $e) {
                dump('Unable to add achievement: '.$e->getMessage());
            }
        }

        // Get all users
        $users = User::all();

        // Precompute top-ten observers once to avoid re-running the heavy aggregation per user
        $topTenObservers = collect(DB::connection('mysqlOld')->table('observations')
            ->select(DB::raw('count(*) as count, observerid'))
            ->groupBy('observerid')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('observerid'))
            ->map(fn ($v) => (string) $v)
            ->toArray();

        // Build maps for catalog-level observed/drawing counts
        $catalogRows = DB::connection('mysqlOld')->table('objectnames')
            ->join('observations', 'objectnames.objectname', '=', 'observations.objectname')
            ->where('observations.visibility', '!=', 7)
            ->select('observations.observerid', 'objectnames.catalog', DB::raw('COUNT(DISTINCT objectnames.catindex) as cnt'))
            ->groupBy('observations.observerid', 'objectnames.catalog')
            ->get();

        foreach ($catalogRows as $r) {
            $this->catalogObservedMap[$r->observerid][(string)$r->catalog] = (int)$r->cnt;
        }

        $catalogDrawingRows = DB::connection('mysqlOld')->table('objectnames')
            ->join('observations', 'objectnames.objectname', '=', 'observations.objectname')
            ->where('observations.visibility', '!=', 7)
            ->where('observations.hasDrawing', 1)
            ->select('observations.observerid', 'objectnames.catalog', DB::raw('COUNT(DISTINCT objectnames.catindex) as cnt'))
            ->groupBy('observations.observerid', 'objectnames.catalog')
            ->get();

        foreach ($catalogDrawingRows as $r) {
            $this->catalogDrawingsMap[$r->observerid][(string)$r->catalog] = (int)$r->cnt;
        }

        // Build maps for object type counts (distinct object names)
        $typeRows = DB::connection('mysqlOld')->table('objects')
            ->join('observations', 'objects.name', '=', 'observations.objectname')
            ->select('observations.observerid', 'objects.type', DB::raw('COUNT(DISTINCT objects.name) as cnt'))
            ->groupBy('observations.observerid', 'objects.type')
            ->get();

        foreach ($typeRows as $r) {
            $this->typeObservedMap[$r->observerid][(string)$r->type] = (int)$r->cnt;
        }

        $typeDrawingRows = DB::connection('mysqlOld')->table('objects')
            ->join('observations', 'objects.name', '=', 'observations.objectname')
            ->where('observations.hasDrawing', 1)
            ->select('observations.observerid', 'objects.type', DB::raw('COUNT(DISTINCT objects.name) as cnt'))
            ->groupBy('observations.observerid', 'objects.type')
            ->get();

        foreach ($typeDrawingRows as $r) {
            $this->typeDrawingsMap[$r->observerid][(string)$r->type] = (int)$r->cnt;
        }

        // Total drawings per user
        $td = DB::connection('mysqlOld')->table('observations')
            ->where('hasDrawing', 1)
            ->select('observerid', DB::raw('COUNT(*) as cnt'))
            ->groupBy('observerid')
            ->get();
        foreach ($td as $r) {
            $this->totalDrawingsMap[$r->observerid] = (int)$r->cnt;
        }

        // Unique objects observed/drawn per user
        $uo = DB::connection('mysqlOld')->table('observations')
            ->select('observerid', DB::raw('COUNT(DISTINCT objectname) as cnt'))
            ->groupBy('observerid')
            ->get();
        foreach ($uo as $r) {
            $this->uniqueObjectsMap[$r->observerid] = (int)$r->cnt;
        }

        $uod = DB::connection('mysqlOld')->table('observations')
            ->where('hasDrawing', 1)
            ->select('observerid', DB::raw('COUNT(DISTINCT objectname) as cnt'))
            ->groupBy('observerid')
            ->get();
        foreach ($uod as $r) {
            $this->uniqueObjectsDrawingsMap[$r->observerid] = (int)$r->cnt;
        }

        // Comet counts
        $cometObs = DB::connection('mysqlOld')->table('cometobservations')
            ->select('observerid', DB::raw('COUNT(*) as cnt'))
            ->groupBy('observerid')
            ->get();
        foreach ($cometObs as $r) {
            $this->cometObservationsMap[$r->observerid] = (int)$r->cnt;
        }

        $cometDraw = DB::connection('mysqlOld')->table('cometobservations')
            ->where('hasDrawing', 1)
            ->select('observerid', DB::raw('COUNT(*) as cnt'))
            ->groupBy('observerid')
            ->get();
        foreach ($cometDraw as $r) {
            $this->cometDrawingsMap[$r->observerid] = (int)$r->cnt;
        }

        $uniqueComet = DB::connection('mysqlOld')->table('cometobservations')
            ->select('observerid', DB::raw('COUNT(DISTINCT objectid) as cnt'))
            ->groupBy('observerid')
            ->get();
        foreach ($uniqueComet as $r) {
            $this->uniqueCometMap[$r->observerid] = (int)$r->cnt;
        }

        // Loop over all users
        foreach ($users as $user) {
            // Get the Accomplishments for the selected user
            $achievement = $this->getAchievement('Top ten observer');
            try {
                //                dump($user->);
                if (in_array($user->username, $topTenObservers, true)) {
                    // Add the top ten observer achievement
                    $this->addAchievementToUser($user, $achievement);
                } else {
                    $this->removeAchievementFromUser($user, $achievement);
                }

                // Calculate number of Messier objects seen
                $total = $this->getCatalogObserved($user->username, 'M');

                // Remove the messier achievements
                $messierGold = $this->getAchievement('Messier Gold');
                $messierSilver = $this->getAchievement('Messier Silver');
                $messierBronze = $this->getAchievement('Messier Bronze');

                $this->setAchievement3($user, $messierGold, $messierSilver, $messierBronze, $total, 110);

                // Calculate number of Messier objects drawn
                $total = $this->getCatalogDrawings($user->username, 'M');

                // Remove the messier achievements
                $messierGoldDrawing = $this->getAchievement('Messier Drawing Gold');
                $messierSilverDrawing = $this->getAchievement('Messier Drawing Silver');
                $messierBronzeDrawing = $this->getAchievement('Messier Drawing Bronze');

                $this->setAchievement3(
                    $user,
                    $messierGoldDrawing,
                    $messierSilverDrawing,
                    $messierBronzeDrawing,
                    $total,
                    110
                );

                // Calculate number of Caldwell objects seen
                $total = $this->getCatalogObserved($user->username, 'Caldwell');

                // Remove the Caldwell achievements
                $caldwellGold = $this->getAchievement('Caldwell Gold');
                $caldwellSilver = $this->getAchievement('Caldwell Silver');
                $caldwellBronze = $this->getAchievement('Caldwell Bronze');

                $this->setAchievement3(
                    $user,
                    $caldwellGold,
                    $caldwellSilver,
                    $caldwellBronze,
                    $total,
                    109
                );

                // Calculate number of Caldwell objects drawn
                $total = $this->getCatalogDrawings($user->username, 'Caldwell');

                // Remove the Caldwell achievements
                $caldwellGoldDrawing = $this->getAchievement('Caldwell Drawing Gold');
                $caldwellSilverDrawing = $this->getAchievement('Caldwell Drawing Silver');
                $caldwellBronzeDrawing = $this->getAchievement('Caldwell Drawing Bronze');

                $this->setAchievement3(
                    $user,
                    $caldwellGoldDrawing,
                    $caldwellSilverDrawing,
                    $caldwellBronzeDrawing,
                    $total,
                    109
                );

                // Calculate number of Herschel 400 objects seen
                $total = $this->getCatalogObserved($user->username, 'H400');

                $h400Platinum = $this->getAchievement('Herschel 400 Platinum');
                $h400Diamond = $this->getAchievement('Herschel 400 Diamond');
                $h400Gold = $this->getAchievement('Herschel 400 Gold');
                $h400Silver = $this->getAchievement('Herschel 400 Silver');
                $h400Bronze = $this->getAchievement('Herschel 400 Bronze');

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
                $total = $this->getCatalogDrawings($user->username, 'H400');

                $h400PlatinumDrawing = $this->getAchievement('Herschel 400 Drawing Platinum');
                $h400DiamondDrawing = $this->getAchievement('Herschel 400 Drawing Diamond');
                $h400GoldDrawing = $this->getAchievement('Herschel 400 Drawing Gold');
                $h400SilverDrawing = $this->getAchievement('Herschel 400 Drawing Silver');
                $h400BronzeDrawing = $this->getAchievement('Herschel 400 Drawing Bronze');

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
                $total = $this->getCatalogObserved($user->username, 'H400-II');

                $hIIPlatinum = $this->getAchievement('Herschel II Platinum');
                $hIIDiamond = $this->getAchievement('Herschel II Diamond');
                $hIIGold = $this->getAchievement('Herschel II Gold');
                $hIISilver = $this->getAchievement('Herschel II Silver');
                $hIIBronze = $this->getAchievement('Herschel II Bronze');

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
                $total = $this->getCatalogDrawings($user->username, 'H400-II');

                $hIIPlatinumDrawing = $this->getAchievement('Herschel II Drawing Platinum');
                $hIIDiamondDrawing = $this->getAchievement('Herschel II Drawing Diamond');
                $hIIGoldDrawing = $this->getAchievement('Herschel II Drawing Gold');
                $hIISilverDrawing = $this->getAchievement('Herschel II Drawing Silver');
                $hIIBronzeDrawing = $this->getAchievement('Herschel II Drawing Bronze');

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
                $total = $this->getTotalDrawingsForUser($user->username);

                $achievement10 = $this->getAchievement('Drawing 1');
                $achievement9 = $this->getAchievement('Drawings 10');
                $achievement8 = $this->getAchievement('Drawings 25');
                $achievement7 = $this->getAchievement('Drawings 50');
                $achievement6 = $this->getAchievement('Drawings 100');
                $achievement5 = $this->getAchievement('Drawings 250');
                $achievement4 = $this->getAchievement('Drawings 500');
                $achievement3 = $this->getAchievement('Drawings 1000');
                $achievement2 = $this->getAchievement('Drawings 2500');
                $achievement1 = $this->getAchievement('Drawings 5000');

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
                $total = ($this->getTypeObserved($user->username, 'OPNCL') ?? 0) + ($this->getTypeObserved($user->username, 'CLANB') ?? 0);

                $achievement10 = $this->getAchievement('Open Cluster 1');
                $achievement9 = $this->getAchievement('Open Clusters 5');
                $achievement8 = $this->getAchievement('Open Clusters 10');
                $achievement7 = $this->getAchievement('Open Clusters 25');
                $achievement6 = $this->getAchievement('Open Clusters 50');
                $achievement5 = $this->getAchievement('Open Clusters 100');
                $achievement4 = $this->getAchievement('Open Clusters 200');
                $achievement3 = $this->getAchievement('Open Clusters 500');
                $achievement2 = $this->getAchievement('Open Clusters 1000');
                $achievement1 = $this->getAchievement('Open Clusters 2500');

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
                $total = ($this->getTypeDrawings($user->username, 'OPNCL') ?? 0) + ($this->getTypeDrawings($user->username, 'CLANB') ?? 0);

                $achievement10 = $this->getAchievement('Open Cluster 1 Drawing');
                $achievement9 = $this->getAchievement('Open Clusters 5 Drawings');
                $achievement8 = $this->getAchievement('Open Clusters 10 Drawings');
                $achievement7 = $this->getAchievement('Open Clusters 25 Drawings');
                $achievement6 = $this->getAchievement('Open Clusters 50 Drawings');
                $achievement5 = $this->getAchievement('Open Clusters 100 Drawings');
                $achievement4 = $this->getAchievement('Open Clusters 200 Drawings');
                $achievement3 = $this->getAchievement('Open Clusters 500 Drawings');
                $achievement2 = $this->getAchievement('Open Clusters 1000 Drawings');
                $achievement1 = $this->getAchievement('Open Clusters 2500 Drawings');

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
                $total = $this->getTypeObserved($user->username, 'GLOCL');

                $achievement10 = $this->getAchievement('Globular Cluster 1');
                $achievement9 = $this->getAchievement('Globular Clusters 3');
                $achievement8 = $this->getAchievement('Globular Clusters 5');
                $achievement7 = $this->getAchievement('Globular Clusters 10');
                $achievement6 = $this->getAchievement('Globular Clusters 15');
                $achievement5 = $this->getAchievement('Globular Clusters 25');
                $achievement4 = $this->getAchievement('Globular Clusters 50');
                $achievement3 = $this->getAchievement('Globular Clusters 75');
                $achievement2 = $this->getAchievement('Globular Clusters 100');
                $achievement1 = $this->getAchievement('Globular Clusters 150');

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
                $total = $this->getTypeDrawings($user->username, 'GLOCL');

                $achievement10 = $this->getAchievement('Globular Cluster 1 Drawing');
                $achievement9 = $this->getAchievement('Globular Clusters 3 Drawings');
                $achievement8 = $this->getAchievement('Globular Clusters 5 Drawings');
                $achievement7 = $this->getAchievement('Globular Clusters 10 Drawings');
                $achievement6 = $this->getAchievement('Globular Clusters 15 Drawings');
                $achievement5 = $this->getAchievement('Globular Clusters 25 Drawings');
                $achievement4 = $this->getAchievement('Globular Clusters 50 Drawings');
                $achievement3 = $this->getAchievement('Globular Clusters 75 Drawings');
                $achievement2 = $this->getAchievement('Globular Clusters 100 Drawings');
                $achievement1 = $this->getAchievement('Globular Clusters 150 Drawings');

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
                $total = $this->getTypeObserved($user->username, 'PLNNB');

                $achievement10 = $this->getAchievement('Planetary Nebula 1');
                $achievement9 = $this->getAchievement('Planetary Nebula 3');
                $achievement8 = $this->getAchievement('Planetary Nebula 5');
                $achievement7 = $this->getAchievement('Planetary Nebula 10');
                $achievement6 = $this->getAchievement('Planetary Nebula 25');
                $achievement5 = $this->getAchievement('Planetary Nebula 50');
                $achievement4 = $this->getAchievement('Planetary Nebula 100');
                $achievement3 = $this->getAchievement('Planetary Nebula 250');
                $achievement2 = $this->getAchievement('Planetary Nebula 500');
                $achievement1 = $this->getAchievement('Planetary Nebula 1000');

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
                $total = $this->getTypeDrawings($user->username, 'PLNNB');

                $achievement10 = $this->getAchievement('Planetary Nebula 1 Drawings');
                $achievement9 = $this->getAchievement('Planetary Nebula 3 Drawings');
                $achievement8 = $this->getAchievement('Planetary Nebula 5 Drawings');
                $achievement7 = $this->getAchievement('Planetary Nebula 10 Drawings');
                $achievement6 = $this->getAchievement('Planetary Nebula 25 Drawings');
                $achievement5 = $this->getAchievement('Planetary Nebula 50 Drawings');
                $achievement4 = $this->getAchievement('Planetary Nebula 100 Drawings');
                $achievement3 = $this->getAchievement('Planetary Nebula 250 Drawings');
                $achievement2 = $this->getAchievement('Planetary Nebula 500 Drawings');
                $achievement1 = $this->getAchievement('Planetary Nebula 1000 Drawings');

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
                $total = $this->getTypeObserved($user->username, 'GALXY');

                $achievement10 = $this->getAchievement('Galaxy 1');
                $achievement9 = $this->getAchievement('Galaxies 10');
                $achievement8 = $this->getAchievement('Galaxies 25');
                $achievement7 = $this->getAchievement('Galaxies 50');
                $achievement6 = $this->getAchievement('Galaxies 100');
                $achievement5 = $this->getAchievement('Galaxies 250');
                $achievement4 = $this->getAchievement('Galaxies 500');
                $achievement3 = $this->getAchievement('Galaxies 1000');
                $achievement2 = $this->getAchievement('Galaxies 2500');
                $achievement1 = $this->getAchievement('Galaxies 5000');

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
                $total = $this->getTypeDrawings($user->username, 'GALXY');

                $achievement10 = $this->getAchievement('Galaxy 1 Drawing');
                $achievement9 = $this->getAchievement('Galaxies 10 Drawings');
                $achievement8 = $this->getAchievement('Galaxies 25 Drawings');
                $achievement7 = $this->getAchievement('Galaxies 50 Drawings');
                $achievement6 = $this->getAchievement('Galaxies 100 Drawings');
                $achievement5 = $this->getAchievement('Galaxies 250 Drawings');
                $achievement4 = $this->getAchievement('Galaxies 500 Drawings');
                $achievement3 = $this->getAchievement('Galaxies 1000 Drawings');
                $achievement2 = $this->getAchievement('Galaxies 2500 Drawings');
                $achievement1 = $this->getAchievement('Galaxies 5000 Drawings');

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
                $nebulaTypes = ['EMINB','ENRNN','ENSTR','REFNB','RNHII','HII','SNREM','WRNEB'];
                $total = 0;
                foreach ($nebulaTypes as $t) {
                    $total += $this->getTypeObserved($user->username, $t) ?? 0;
                }

                $achievement10 = $this->getAchievement('Nebula 1');
                $achievement9 = $this->getAchievement('Nebulae 5');
                $achievement8 = $this->getAchievement('Nebulae 10');
                $achievement7 = $this->getAchievement('Nebulae 25');
                $achievement6 = $this->getAchievement('Nebulae 50');
                $achievement5 = $this->getAchievement('Nebulae 75');
                $achievement4 = $this->getAchievement('Nebulae 100');
                $achievement3 = $this->getAchievement('Nebulae 150');
                $achievement2 = $this->getAchievement('Nebulae 200');
                $achievement1 = $this->getAchievement('Nebulae 300');

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
                $nebulaTypes = ['EMINB','ENRNN','ENSTR','REFNB','RNHII','HII','SNREM','WRNEB'];
                $total = 0;
                foreach ($nebulaTypes as $t) {
                    $total += $this->getTypeDrawings($user->username, $t) ?? 0;
                }

                $achievement10 = $this->getAchievement('Nebula 1 Drawing');
                $achievement9 = $this->getAchievement('Nebulae 5 Drawings');
                $achievement8 = $this->getAchievement('Nebulae 10 Drawings');
                $achievement7 = $this->getAchievement('Nebulae 25 Drawings');
                $achievement6 = $this->getAchievement('Nebulae 50 Drawings');
                $achievement5 = $this->getAchievement('Nebulae 75 Drawings');
                $achievement4 = $this->getAchievement('Nebulae 100 Drawings');
                $achievement3 = $this->getAchievement('Nebulae 150 Drawings');
                $achievement2 = $this->getAchievement('Nebulae 200 Drawings');
                $achievement1 = $this->getAchievement('Nebulae 300 Drawings');

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
                $total = $this->getUniqueObjectsObserved($user->username);

                $achievement10 = $this->getAchievement('Object 1');
                $achievement9 = $this->getAchievement('Objects 10');
                $achievement8 = $this->getAchievement('Objects 25');
                $achievement7 = $this->getAchievement('Objects 50');
                $achievement6 = $this->getAchievement('Objects 100');
                $achievement5 = $this->getAchievement('Objects 250');
                $achievement4 = $this->getAchievement('Objects 500');
                $achievement3 = $this->getAchievement('Objects 1000');
                $achievement2 = $this->getAchievement('Objects 2500');
                $achievement1 = $this->getAchievement('Objects 5000');

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
                $total = $this->getUniqueObjectsDrawn($user->username);

                $achievement10 = $this->getAchievement('Object 1 Drawing');
                $achievement9 = $this->getAchievement('Objects 10 Drawings');
                $achievement8 = $this->getAchievement('Objects 25 Drawings');
                $achievement7 = $this->getAchievement('Objects 50 Drawings');
                $achievement6 = $this->getAchievement('Objects 100 Drawings');
                $achievement5 = $this->getAchievement('Objects 250 Drawings');
                $achievement4 = $this->getAchievement('Objects 500 Drawings');
                $achievement3 = $this->getAchievement('Objects 1000 Drawings');
                $achievement2 = $this->getAchievement('Objects 2500 Drawings');
                $achievement1 = $this->getAchievement('Objects 5000 Drawings');

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
                $total = $this->getCometObservationsForUser($user->username);

                $achievement10 = $this->getAchievement('Comet 1');
                $achievement9 = $this->getAchievement('Comets 10');
                $achievement8 = $this->getAchievement('Comets 25');
                $achievement7 = $this->getAchievement('Comets 50');
                $achievement6 = $this->getAchievement('Comets 100');
                $achievement5 = $this->getAchievement('Comets 250');
                $achievement4 = $this->getAchievement('Comets 500');
                $achievement3 = $this->getAchievement('Comets 1000');
                $achievement2 = $this->getAchievement('Comets 2500');
                $achievement1 = $this->getAchievement('Comets 5000');

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
                $total = $this->getCometDrawingsForUser($user->username);

                $achievement10 = $this->getAchievement('Comet 1 Drawing');
                $achievement9 = $this->getAchievement('Comets 10 Drawings');
                $achievement8 = $this->getAchievement('Comets 25 Drawings');
                $achievement7 = $this->getAchievement('Comets 50 Drawings');
                $achievement6 = $this->getAchievement('Comets 100 Drawings');
                $achievement5 = $this->getAchievement('Comets 250 Drawings');
                $achievement4 = $this->getAchievement('Comets 500 Drawings');
                $achievement3 = $this->getAchievement('Comets 1000 Drawings');
                $achievement2 = $this->getAchievement('Comets 2500 Drawings');
                $achievement1 = $this->getAchievement('Comets 5000 Drawings');

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
                $total = $this->getUniqueCometObservationsForUser($user->username);

                $achievement10 = $this->getAchievement('Different Comets 1');
                $achievement9 = $this->getAchievement('Different Comets 10');
                $achievement8 = $this->getAchievement('Different Comets 25');
                $achievement7 = $this->getAchievement('Different Comets 50');
                $achievement6 = $this->getAchievement('Different Comets 100');
                $achievement5 = $this->getAchievement('Different Comets 250');
                $achievement4 = $this->getAchievement('Different Comets 500');
                $achievement3 = $this->getAchievement('Different Comets 1000');
                $achievement2 = $this->getAchievement('Different Comets 2500');
                $achievement1 = $this->getAchievement('Different Comets 5000');

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
     * Retrieves the user associated with a given sketch using preloaded data.
     *
     * @param  mixed  $sketch  The sketch object.
     * @param  \Illuminate\Support\Collection  $observations  Preloaded observations.
     * @param  \Illuminate\Support\Collection  $cometObservations  Preloaded comet observations.
     * @param  \Illuminate\Support\Collection  $users  Preloaded users.
     * @return User The user object associated with the sketch.
     *
     * @throws Exception If the sketch observation ID is invalid or if there is an error retrieving the observation or user.
     */
    public function getSketchDataFromCache(mixed $sketch, $observations, $cometObservations, $users): User
    {
        if ($sketch->observation_id < 0) {
            $observation = $cometObservations->get(-$sketch->observation_id);
        } else {
            $observation = $observations->get($sketch->observation_id);
        }
        
        if (!$observation) {
            throw new Exception('Observation not found for sketch: ' . $sketch->observation_id);
        }
        
        $observerid = html_entity_decode($observation->observerid);
        
        $user = $users->get($observerid);
        if (!$user) {
            throw new Exception('User not found: ' . $observerid);
        }
        
        return $user;
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
        // Check if user already has this achievement using our in-memory map
        if (!isset($this->userAchievementsMap[$user->id][$achievement->id])) {
            try {
                $user->grantAchievement($achievement);
                // Update our in-memory map
                if (!isset($this->userAchievementsMap[$user->id])) {
                    $this->userAchievementsMap[$user->id] = [];
                }
                $this->userAchievementsMap[$user->id][$achievement->id] = true;
            } catch (Exception) {
                // dump('Unable to add achievement: '.$e->getMessage());
            }
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
        // Check if user has this achievement using our in-memory map
        if (isset($this->userAchievementsMap[$user->id][$achievement->id])) {
            try {
                AchievementUser::where('user_id', $user->id)->where('achievement_id', $achievement->id)->delete();
                // Update our in-memory map
                unset($this->userAchievementsMap[$user->id][$achievement->id]);
            } catch (Exception) {
                // dump('Unable to remove achievement: '.$e->getMessage());
            }
        }
    }

    /**
     * Removes multiple achievements from a user in a single query.
     *
     * @param  mixed  $user  The user object.
     * @param  array  $achievements  Array of achievement objects.
     */
    private function removeMultipleAchievementsFromUser(mixed $user, array $achievements): void
    {
        $achievementIds = [];
        foreach ($achievements as $achievement) {
            if (isset($this->userAchievementsMap[$user->id][$achievement->id])) {
                $achievementIds[] = $achievement->id;
            }
        }
        
        if (!empty($achievementIds)) {
            try {
                AchievementUser::where('user_id', $user->id)->whereIn('achievement_id', $achievementIds)->delete();
                // Update our in-memory map
                foreach ($achievementIds as $achievementId) {
                    unset($this->userAchievementsMap[$user->id][$achievementId]);
                }
            } catch (Exception) {
                // dump('Unable to remove achievements: '.$e->getMessage());
            }
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
            $this->removeMultipleAchievementsFromUser($user, [$silver, $bronze]);
        } elseif ($total >= 50) {
            $this->addAchievementToUser($user, $silver);
            $this->removeMultipleAchievementsFromUser($user, [$gold, $bronze]);
        } elseif ($total >= 25) {
            $this->addAchievementToUser($user, $bronze);
            $this->removeMultipleAchievementsFromUser($user, [$gold, $silver]);
        } else {
            $this->removeMultipleAchievementsFromUser($user, [$gold, $silver, $bronze]);
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
            $this->removeMultipleAchievementsFromUser($user, [$diamond, $gold, $silver, $bronze]);
        } elseif ($total >= 200) {
            $this->addAchievementToUser($user, $diamond);
            $this->removeMultipleAchievementsFromUser($user, [$platinum, $gold, $silver, $bronze]);
        } elseif ($total >= 100) {
            $this->addAchievementToUser($user, $gold);
            $this->removeMultipleAchievementsFromUser($user, [$platinum, $diamond, $silver, $bronze]);
        } elseif ($total >= 50) {
            $this->addAchievementToUser($user, $silver);
            $this->removeMultipleAchievementsFromUser($user, [$platinum, $diamond, $gold, $bronze]);
        } elseif ($total >= 25) {
            $this->addAchievementToUser($user, $bronze);
            $this->removeMultipleAchievementsFromUser($user, [$platinum, $diamond, $gold, $silver]);
        } else {
            $this->removeMultipleAchievementsFromUser($user, [$platinum, $diamond, $gold, $silver, $bronze]);
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
        $this->removeMultipleAchievementsFromUser(
            $user,
            [
                $achievement2,
                $achievement3,
                $achievement4,
                $achievement5,
                $achievement6,
                $achievement7,
                $achievement8,
                $achievement9,
                $achievement10
            ]
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
        $this->removeMultipleAchievementsFromUser(
            $user,
            [
                $achievement1,
                $achievement2,
                $achievement3,
                $achievement4,
                $achievement5,
                $achievement6,
                $achievement7,
                $achievement8,
                $achievement9
            ]
        );
    }

    /**
     * Get an achievement by name from the cached map.
     *
     * @param string $name
     * @return Achievement
     * @throws Exception
     */
    private function getAchievement(string $name): Achievement
    {
        if ($this->achievementMap === null) {
            $this->achievementMap = Achievement::all()->keyBy('name');
        }

        $achievement = $this->achievementMap->get($name);

        if ($achievement === null) {
            throw new Exception('Achievement not found: '.$name);
        }

        return $achievement;
    }

    private function getCatalogObserved(string $username, string $catalog): int
    {
        return $this->catalogObservedMap[$username][$catalog] ?? 0;
    }

    private function getCatalogDrawings(string $username, string $catalog): int
    {
        return $this->catalogDrawingsMap[$username][$catalog] ?? 0;
    }

    private function getTypeObserved(string $username, string $type): int
    {
        return $this->typeObservedMap[$username][$type] ?? 0;
    }

    private function getTypeDrawings(string $username, string $type): int
    {
        return $this->typeDrawingsMap[$username][$type] ?? 0;
    }

    private function getTotalDrawingsForUser(string $username): int
    {
        return $this->totalDrawingsMap[$username] ?? 0;
    }

    private function getUniqueObjectsObserved(string $username): int
    {
        return $this->uniqueObjectsMap[$username] ?? 0;
    }

    private function getUniqueObjectsDrawn(string $username): int
    {
        return $this->uniqueObjectsDrawingsMap[$username] ?? 0;
    }

    private function getCometObservationsForUser(string $username): int
    {
        return $this->cometObservationsMap[$username] ?? 0;
    }

    private function getCometDrawingsForUser(string $username): int
    {
        return $this->cometDrawingsMap[$username] ?? 0;
    }

    private function getUniqueCometObservationsForUser(string $username): int
    {
        return $this->uniqueCometMap[$username] ?? 0;
    }
}
