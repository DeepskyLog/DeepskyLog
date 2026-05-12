<?php

namespace Database\Seeders;

use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LevelUp\Experience\Models\Achievement;

class addAchievementsSeeder extends Seeder
{
    public function run(): void
    {
        // First, dedupe any existing duplicate achievements so seeding is safe
        $this->dedupeAchievements();

        // Create Early adopter achievements (idempotent)
        $this->createAchievement([
            'name' => 'Early adopter',
            'is_secret' => false,
            'description' => 'One of the first users of DeepskyLog. Registered before 2006.',
            'image' => '/images/achievements/earlyAdopter.svg',
        ]);

        // Create Top ten observer achievement
        $this->createAchievement([
            'name' => 'Top ten observer',
            'is_secret' => false,
            'description' => 'In top ten of most observations logged',
            'image' => '/images/achievements/topTenObserver.svg',
        ]);

        // Messier
        $this->createAchievement([
            'name' => 'Messier Bronze',
            'is_secret' => false,
            'description' => 'Observe 25 Messier objects.',
            'image' => '/images/achievements/messierBronze.svg',
        ]);

        $this->createAchievement([
            'name' => 'Messier Silver',
            'is_secret' => false,
            'description' => 'Observe 50 Messier objects.',
            'image' => '/images/achievements/messierSilver.svg',
        ]);

        $this->createAchievement([
            'name' => 'Messier Gold',
            'is_secret' => false,
            'description' => 'Observe all 110 Messier objects.',
            'image' => '/images/achievements/messierGold.svg',
        ]);

        $this->createAchievement([
            'name' => 'Messier Drawing Bronze',
            'is_secret' => false,
            'description' => 'Draw 25 Messier objects.',
            'image' => '/images/achievements/messierBronzeDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Messier Drawing Silver',
            'is_secret' => false,
            'description' => 'Draw 50 Messier objects.',
            'image' => '/images/achievements/messierSilverDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Messier Drawing Gold',
            'is_secret' => false,
            'description' => 'Draw all 110 Messier objects.',
            'image' => '/images/achievements/messierGoldDrawing.svg',
        ]);

        // Caldwell
        $this->createAchievement([
            'name' => 'Caldwell Bronze',
            'is_secret' => false,
            'description' => 'Observe 25 Caldwell objects.',
            'image' => '/images/achievements/caldwellBronze.svg',
        ]);

        $this->createAchievement([
            'name' => 'Caldwell Silver',
            'is_secret' => false,
            'description' => 'Observe 50 Caldwell objects.',
            'image' => '/images/achievements/caldwellSilver.svg',
        ]);

        $this->createAchievement([
            'name' => 'Caldwell Gold',
            'is_secret' => false,
            'description' => 'Observe all 109 Caldwell objects.',
            'image' => '/images/achievements/caldwellGold.svg',
        ]);

        $this->createAchievement([
            'name' => 'Caldwell Drawing Bronze',
            'is_secret' => false,
            'description' => 'Draw 25 Caldwell objects.',
            'image' => '/images/achievements/caldwellBronzeDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Caldwell Drawing Silver',
            'is_secret' => false,
            'description' => 'Draw 50 Caldwell objects.',
            'image' => '/images/achievements/caldwellSilverDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Caldwell Drawing Gold',
            'is_secret' => false,
            'description' => 'Draw all 109 Caldwell objects.',
            'image' => '/images/achievements/caldwellGoldDrawing.svg',
        ]);

        // Herschel-400
        $this->createAchievement([
            'name' => 'Herschel 400 Bronze',
            'is_secret' => false,
            'description' => 'Observe 25 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400Bronze.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel 400 Silver',
            'is_secret' => false,
            'description' => 'Observe 50 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400Silver.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel 400 Gold',
            'is_secret' => false,
            'description' => 'Observe 100 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400Gold.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel 400 Diamond',
            'is_secret' => false,
            'description' => 'Observe 200 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400Diamond.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel 400 Platinum',
            'is_secret' => false,
            'description' => 'Observe all 400 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400Platinum.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel 400 Drawing Bronze',
            'is_secret' => false,
            'description' => 'Draw 25 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400BronzeDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel 400 Drawing Silver',
            'is_secret' => false,
            'description' => 'Draw 50 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400SilverDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel 400 Drawing Gold',
            'is_secret' => false,
            'description' => 'Draw 100 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400GoldDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel 400 Drawing Diamond',
            'is_secret' => false,
            'description' => 'Draw 200 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400DiamondDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel 400 Drawing Platinum',
            'is_secret' => false,
            'description' => 'Draw all 400 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400PlatinumDrawing.svg',
        ]);

        // Herschel-II
        $this->createAchievement([
            'name' => 'Herschel II Bronze',
            'is_secret' => false,
            'description' => 'Observe 25 Herschel II objects.',
            'image' => '/images/achievements/herschelIIBronze.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel II Silver',
            'is_secret' => false,
            'description' => 'Observe 50 Herschel II objects.',
            'image' => '/images/achievements/herschelIISilver.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel II Gold',
            'is_secret' => false,
            'description' => 'Observe 100 Herschel II objects.',
            'image' => '/images/achievements/herschelIIGold.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel II Diamond',
            'is_secret' => false,
            'description' => 'Observe 200 Herschel II objects.',
            'image' => '/images/achievements/herschelIIDiamond.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel II Platinum',
            'is_secret' => false,
            'description' => 'Observe all 400 Herschel II objects.',
            'image' => '/images/achievements/herschelIIPlatinum.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel II Drawing Bronze',
            'is_secret' => false,
            'description' => 'Draw 25 Herschel II objects.',
            'image' => '/images/achievements/herschelIIBronzeDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel II Drawing Silver',
            'is_secret' => false,
            'description' => 'Draw 50 Herschel II objects.',
            'image' => '/images/achievements/herschelIISilverDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel II Drawing Gold',
            'is_secret' => false,
            'description' => 'Draw 100 Herschel II objects.',
            'image' => '/images/achievements/herschelIIGoldDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel II Drawing Diamond',
            'is_secret' => false,
            'description' => 'Draw 200 Herschel II objects.',
            'image' => '/images/achievements/herschelIIDiamondDrawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Herschel II Drawing Platinum',
            'is_secret' => false,
            'description' => 'Draw all 400 Herschel II objects.',
            'image' => '/images/achievements/herschelIIPlatinumDrawing.svg',
        ]);

        // Total number of drawings: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Drawing 1',
            'is_secret' => false,
            'description' => 'Make 1 drawing.',
            'image' => '/images/achievements/drawings1.svg',
        ]);

        $this->createAchievement([
            'name' => 'Drawings 10',
            'is_secret' => false,
            'description' => 'Make 10 drawings.',
            'image' => '/images/achievements/drawings10.svg',
        ]);

        $this->createAchievement([
            'name' => 'Drawings 25',
            'is_secret' => false,
            'description' => 'Make 25 drawings.',
            'image' => '/images/achievements/drawings25.svg',
        ]);

        $this->createAchievement([
            'name' => 'Drawings 50',
            'is_secret' => false,
            'description' => 'Make 50 drawings.',
            'image' => '/images/achievements/drawings50.svg',
        ]);

        $this->createAchievement([
            'name' => 'Drawings 100',
            'is_secret' => false,
            'description' => 'Make 100 drawings.',
            'image' => '/images/achievements/drawings100.svg',
        ]);

        $this->createAchievement([
            'name' => 'Drawings 250',
            'is_secret' => false,
            'description' => 'Make 250 drawings.',
            'image' => '/images/achievements/drawings250.svg',
        ]);

        $this->createAchievement([
            'name' => 'Drawings 500',
            'is_secret' => false,
            'description' => 'Make 500 drawings.',
            'image' => '/images/achievements/drawings500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Drawings 1000',
            'is_secret' => false,
            'description' => 'Make 1000 drawings.',
            'image' => '/images/achievements/drawings1000.svg',
        ]);

        $this->createAchievement([
            'name' => 'Drawings 2500',
            'is_secret' => false,
            'description' => 'Make 2500 drawings.',
            'image' => '/images/achievements/drawings2500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Drawings 5000',
            'is_secret' => false,
            'description' => 'Make 5000 drawings.',
            'image' => '/images/achievements/drawings5000.svg',
        ]);

        // Open cluster: 1, 5, 10, 25, 50, 100, 200, 500, 1000, 1500
        $this->createAchievement([
            'name' => 'Open Cluster 1',
            'is_secret' => false,
            'description' => 'Observe 1 open cluster.',
            'image' => '/images/achievements/openClusters1.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 5',
            'is_secret' => false,
            'description' => 'Observe 5 open clusters.',
            'image' => '/images/achievements/openClusters5.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 10',
            'is_secret' => false,
            'description' => 'Observe 10 open clusters.',
            'image' => '/images/achievements/openClusters10.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 25',
            'is_secret' => false,
            'description' => 'Observe 25 open clusters.',
            'image' => '/images/achievements/openClusters25.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 50',
            'is_secret' => false,
            'description' => 'Observe 50 open clusters.',
            'image' => '/images/achievements/openClusters50.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 100',
            'is_secret' => false,
            'description' => 'Observe 100 open clusters.',
            'image' => '/images/achievements/openClusters100.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 200',
            'is_secret' => false,
            'description' => 'Observe 200 open clusters.',
            'image' => '/images/achievements/openClusters200.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 500',
            'is_secret' => false,
            'description' => 'Observe 500 open clusters.',
            'image' => '/images/achievements/openClusters500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 open clusters.',
            'image' => '/images/achievements/openClusters1000.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 2500',
            'is_secret' => false,
            'description' => 'Observe 2500 open clusters.',
            'image' => '/images/achievements/openClusters2500.svg',
        ]);

        // Drawings of open clusters
        $this->createAchievement([
            'name' => 'Open Cluster 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 open cluster.',
            'image' => '/images/achievements/openClusters1Drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 5 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5 open clusters.',
            'image' => '/images/achievements/openClusters5Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 open clusters.',
            'image' => '/images/achievements/openClusters10Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 open clusters.',
            'image' => '/images/achievements/openClusters25Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 open clusters.',
            'image' => '/images/achievements/openClusters50Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 open clusters.',
            'image' => '/images/achievements/openClusters100Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 200 Drawings',
            'is_secret' => false,
            'description' => 'Draw 200 open clusters.',
            'image' => '/images/achievements/openClusters200Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 open clusters.',
            'image' => '/images/achievements/openClusters500Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 open clusters.',
            'image' => '/images/achievements/openClusters1000Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Open Clusters 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 2500 open clusters.',
            'image' => '/images/achievements/openClusters2500Drawings.svg',
        ]);

        // Globular clusters: 1, 3, 5, 10, 15, 25, 50, 75, 100, 150
        $this->createAchievement([
            'name' => 'Globular Cluster 1',
            'is_secret' => false,
            'description' => 'Observe 1 globular cluster.',
            'image' => '/images/achievements/globularClusters1.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 3',
            'is_secret' => false,
            'description' => 'Observe 3 globular clusters.',
            'image' => '/images/achievements/globularClusters3.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 5',
            'is_secret' => false,
            'description' => 'Observe 5 globular clusters.',
            'image' => '/images/achievements/globularClusters5.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 10',
            'is_secret' => false,
            'description' => 'Observe 10 globular clusters.',
            'image' => '/images/achievements/globularClusters10.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 15',
            'is_secret' => false,
            'description' => 'Observe 15 globular clusters.',
            'image' => '/images/achievements/globularClusters15.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 25',
            'is_secret' => false,
            'description' => 'Observe 25 globular clusters.',
            'image' => '/images/achievements/globularClusters25.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 50',
            'is_secret' => false,
            'description' => 'Observe 50 globular clusters.',
            'image' => '/images/achievements/globularClusters50.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 75',
            'is_secret' => false,
            'description' => 'Observe 75 globular clusters.',
            'image' => '/images/achievements/globularClusters75.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 100',
            'is_secret' => false,
            'description' => 'Observe 100 globular clusters.',
            'image' => '/images/achievements/globularClusters100.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 150',
            'is_secret' => false,
            'description' => 'Observe 150 globular clusters.',
            'image' => '/images/achievements/globularClusters150.svg',
        ]);

        // Drawings of globular clusters
        $this->createAchievement([
            'name' => 'Globular Cluster 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 globular cluster.',
            'image' => '/images/achievements/globularClusters1Drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 3 Drawings',
            'is_secret' => false,
            'description' => 'Draw 3 globular clusters.',
            'image' => '/images/achievements/globularClusters3Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 5 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5 globular clusters.',
            'image' => '/images/achievements/globularClusters5Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 globular clusters.',
            'image' => '/images/achievements/globularClusters10Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 15 Drawings',
            'is_secret' => false,
            'description' => 'Draw 15 globular clusters.',
            'image' => '/images/achievements/globularClusters15Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 globular clusters.',
            'image' => '/images/achievements/globularClusters25Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 globular clusters.',
            'image' => '/images/achievements/globularClusters50Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 75 Drawings',
            'is_secret' => false,
            'description' => 'Draw 75 globular clusters.',
            'image' => '/images/achievements/globularClusters75Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 globular clusters.',
            'image' => '/images/achievements/globularClusters100Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Globular Clusters 150 Drawings',
            'is_secret' => false,
            'description' => 'Draw 150 globular clusters.',
            'image' => '/images/achievements/globularClusters150Drawings.svg',
        ]);

        // Planetary nebulae: 1, 3, 5, 10, 25, 50, 100, 250, 500, 1000
        $this->createAchievement([
            'name' => 'Planetary Nebula 1',
            'is_secret' => false,
            'description' => 'Observe 1 planetary nebula.',
            'image' => '/images/achievements/planetaryNebulae1.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 3',
            'is_secret' => false,
            'description' => 'Observe 3 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae3.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 5',
            'is_secret' => false,
            'description' => 'Observe 5 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae5.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 10',
            'is_secret' => false,
            'description' => 'Observe 10 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae10.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 25',
            'is_secret' => false,
            'description' => 'Observe 25 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae25.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 50',
            'is_secret' => false,
            'description' => 'Observe 50 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae50.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 100',
            'is_secret' => false,
            'description' => 'Observe 100 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae100.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 250',
            'is_secret' => false,
            'description' => 'Observe 250 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae250.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 500',
            'is_secret' => false,
            'description' => 'Observe 500 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae1000.svg',
        ]);

        // Drawings of planetary nebulae
        $this->createAchievement([
            'name' => 'Planetary Nebula 1 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1 planetary nebula.',
            'image' => '/images/achievements/planetaryNebulae1Drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 3 Drawings',
            'is_secret' => false,
            'description' => 'Draw 3 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae3Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 5 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae5Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae10Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae25Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae50Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae100Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw 250 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae250Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae500Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planetary Nebula 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae1000Drawings.svg',
        ]);

        // Galaxies: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Galaxy 1',
            'is_secret' => false,
            'description' => 'Observe 1 galaxy.',
            'image' => '/images/achievements/galaxies1.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 10',
            'is_secret' => false,
            'description' => 'Observe 10 galaxies.',
            'image' => '/images/achievements/galaxies10.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 25',
            'is_secret' => false,
            'description' => 'Observe 25 galaxies.',
            'image' => '/images/achievements/galaxies25.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 50',
            'is_secret' => false,
            'description' => 'Observe 50 galaxies.',
            'image' => '/images/achievements/galaxies50.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 100',
            'is_secret' => false,
            'description' => 'Observe 100 galaxies.',
            'image' => '/images/achievements/galaxies100.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 250',
            'is_secret' => false,
            'description' => 'Observe 250 galaxies.',
            'image' => '/images/achievements/galaxies250.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 500',
            'is_secret' => false,
            'description' => 'Observe 500 galaxies.',
            'image' => '/images/achievements/galaxies500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 galaxies.',
            'image' => '/images/achievements/galaxies1000.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 2500',
            'is_secret' => false,
            'description' => 'Observe 2500 galaxies.',
            'image' => '/images/achievements/galaxies2500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 5000',
            'is_secret' => false,
            'description' => 'Observe 5000 galaxies.',
            'image' => '/images/achievements/galaxies5000.svg',
        ]);

        // Drawings of galaxies
        $this->createAchievement([
            'name' => 'Galaxy 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 galaxy.',
            'image' => '/images/achievements/galaxies1Drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 galaxies.',
            'image' => '/images/achievements/galaxies10Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 galaxies.',
            'image' => '/images/achievements/galaxies25Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 galaxies.',
            'image' => '/images/achievements/galaxies50Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 galaxies.',
            'image' => '/images/achievements/galaxies100Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw 250 galaxies.',
            'image' => '/images/achievements/galaxies250Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 galaxies.',
            'image' => '/images/achievements/galaxies500Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 galaxies.',
            'image' => '/images/achievements/galaxies1000Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 2500 galaxies.',
            'image' => '/images/achievements/galaxies2500Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Galaxies 5000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5000 galaxies.',
            'image' => '/images/achievements/galaxies5000Drawings.svg',
        ]);

        // Nebulae: 1, 5, 10, 25, 50, 75, 100, 150, 200, 300
        $this->createAchievement([
            'name' => 'Nebula 1',
            'is_secret' => false,
            'description' => 'Observe 1 nebula.',
            'image' => '/images/achievements/nebulae1.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 5',
            'is_secret' => false,
            'description' => 'Observe 5 nebulae.',
            'image' => '/images/achievements/nebulae5.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 10',
            'is_secret' => false,
            'description' => 'Observe 10 nebulae.',
            'image' => '/images/achievements/nebulae10.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 25',
            'is_secret' => false,
            'description' => 'Observe 25 nebulae.',
            'image' => '/images/achievements/nebulae25.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 50',
            'is_secret' => false,
            'description' => 'Observe 50 nebulae.',
            'image' => '/images/achievements/nebulae50.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 75',
            'is_secret' => false,
            'description' => 'Observe 75 nebulae.',
            'image' => '/images/achievements/nebulae75.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 100',
            'is_secret' => false,
            'description' => 'Observe 100 nebulae.',
            'image' => '/images/achievements/nebulae100.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 150',
            'is_secret' => false,
            'description' => 'Observe 150 nebulae.',
            'image' => '/images/achievements/nebulae150.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 200',
            'is_secret' => false,
            'description' => 'Observe 200 nebulae.',
            'image' => '/images/achievements/nebulae200.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 300',
            'is_secret' => false,
            'description' => 'Observe 300 nebulae.',
            'image' => '/images/achievements/nebulae300.svg',
        ]);

        // Drawings of nebulae
        $this->createAchievement([
            'name' => 'Nebula 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 nebula.',
            'image' => '/images/achievements/nebulae1Drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 5 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5 nebulae.',
            'image' => '/images/achievements/nebulae5Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 nebulae.',
            'image' => '/images/achievements/nebulae10Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 nebulae.',
            'image' => '/images/achievements/nebulae25Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 nebulae.',
            'image' => '/images/achievements/nebulae50Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 75 Drawings',
            'is_secret' => false,
            'description' => 'Draw 75 nebulae.',
            'image' => '/images/achievements/nebulae75Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 nebulae.',
            'image' => '/images/achievements/nebulae100Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 150 Drawings',
            'is_secret' => false,
            'description' => 'Draw 150 nebulae.',
            'image' => '/images/achievements/nebulae150Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 200 Drawings',
            'is_secret' => false,
            'description' => 'Draw 200 nebulae.',
            'image' => '/images/achievements/nebulae200Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Nebulae 300 Drawings',
            'is_secret' => false,
            'description' => 'Draw 300 nebulae.',
            'image' => '/images/achievements/nebulae300Drawings.svg',
        ]);

        // Different objects: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Object 1',
            'is_secret' => false,
            'description' => 'Observe 1 object.',
            'image' => '/images/achievements/objects1.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 10',
            'is_secret' => false,
            'description' => 'Observe 10 different objects.',
            'image' => '/images/achievements/objects10.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 25',
            'is_secret' => false,
            'description' => 'Observe 25 different objects.',
            'image' => '/images/achievements/objects25.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 50',
            'is_secret' => false,
            'description' => 'Observe 50 different objects.',
            'image' => '/images/achievements/objects50.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 100',
            'is_secret' => false,
            'description' => 'Observe 100 different objects.',
            'image' => '/images/achievements/objects100.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 250',
            'is_secret' => false,
            'description' => 'Observe 250 different objects.',
            'image' => '/images/achievements/objects250.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 500',
            'is_secret' => false,
            'description' => 'Observe 500 different objects.',
            'image' => '/images/achievements/objects500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 different objects.',
            'image' => '/images/achievements/objects1000.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 2500',
            'is_secret' => false,
            'description' => 'Observe 2500 different objects.',
            'image' => '/images/achievements/objects2500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 5000',
            'is_secret' => false,
            'description' => 'Observe 5000 different objects.',
            'image' => '/images/achievements/objects5000.svg',
        ]);

        // Drawings of different objects
        $this->createAchievement([
            'name' => 'Object 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 object.',
            'image' => '/images/achievements/objects1Drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 different objects.',
            'image' => '/images/achievements/objects10Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 different objects.',
            'image' => '/images/achievements/objects25Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 different objects.',
            'image' => '/images/achievements/objects50Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 different objects.',
            'image' => '/images/achievements/objects100Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw 250 different objects.',
            'image' => '/images/achievements/objects250Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 different objects.',
            'image' => '/images/achievements/objects500Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 different objects.',
            'image' => '/images/achievements/objects1000Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 2500 different objects.',
            'image' => '/images/achievements/objects2500Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Objects 5000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5000 different objects.',
            'image' => '/images/achievements/objects5000Drawings.svg',
        ]);

        // Total comet observations: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Comet 1',
            'is_secret' => false,
            'description' => 'Make 1 comet observation.',
            'image' => '/images/achievements/comets1.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 10',
            'is_secret' => false,
            'description' => 'Make 10 comet observations.',
            'image' => '/images/achievements/comets10.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 25',
            'is_secret' => false,
            'description' => 'Make 25 comet observations.',
            'image' => '/images/achievements/comets25.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 50',
            'is_secret' => false,
            'description' => 'Make 50 comet observations.',
            'image' => '/images/achievements/comets50.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 100',
            'is_secret' => false,
            'description' => 'Make 100 comet observations.',
            'image' => '/images/achievements/comets100.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 250',
            'is_secret' => false,
            'description' => 'Make 250 comet observations.',
            'image' => '/images/achievements/comets250.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 500',
            'is_secret' => false,
            'description' => 'Make 500 comet observations.',
            'image' => '/images/achievements/comets500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 1000',
            'is_secret' => false,
            'description' => 'Make 1000 comet observations.',
            'image' => '/images/achievements/comets1000.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 2500',
            'is_secret' => false,
            'description' => 'Make 2500 comet observations.',
            'image' => '/images/achievements/comets2500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 5000',
            'is_secret' => false,
            'description' => 'Make 5000 comet observations.',
            'image' => '/images/achievements/comets5000.svg',
        ]);

        // Drawings of comets: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Comet 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 comet.',
            'image' => '/images/achievements/comets1Drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 comets.',
            'image' => '/images/achievements/comets10Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 comets.',
            'image' => '/images/achievements/comets25Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 comets.',
            'image' => '/images/achievements/comets50Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 comets.',
            'image' => '/images/achievements/comets100Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw 250 comets.',
            'image' => '/images/achievements/comets250Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 comets.',
            'image' => '/images/achievements/comets500Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 comets.',
            'image' => '/images/achievements/comets1000Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 2500 comets.',
            'image' => '/images/achievements/comets2500Drawings.svg',
        ]);

        $this->createAchievement([
            'name' => 'Comets 5000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5000 comets.',
            'image' => '/images/achievements/comets5000Drawings.svg',
        ]);

        // Different comets seen: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Different Comets 1',
            'is_secret' => false,
            'description' => 'Observe 1 comet.',
            'image' => '/images/achievements/differentComets1.svg',
        ]);

        $this->createAchievement([
            'name' => 'Different Comets 10',
            'is_secret' => false,
            'description' => 'Observe 10 different comets.',
            'image' => '/images/achievements/differentComets10.svg',
        ]);

        $this->createAchievement([
            'name' => 'Different Comets 25',
            'is_secret' => false,
            'description' => 'Observe 25 different comets.',
            'image' => '/images/achievements/differentComets25.svg',
        ]);

        $this->createAchievement([
            'name' => 'Different Comets 50',
            'is_secret' => false,
            'description' => 'Observe 50 different comets.',
            'image' => '/images/achievements/differentComets50.svg',
        ]);

        $this->createAchievement([
            'name' => 'Different Comets 100',
            'is_secret' => false,
            'description' => 'Observe 100 different comets.',
            'image' => '/images/achievements/differentComets100.svg',
        ]);

        $this->createAchievement([
            'name' => 'Different Comets 250',
            'is_secret' => false,
            'description' => 'Observe 250 different comets.',
            'image' => '/images/achievements/differentComets250.svg',
        ]);

        $this->createAchievement([
            'name' => 'Different Comets 500',
            'is_secret' => false,
            'description' => 'Observe 500 different comets.',
            'image' => '/images/achievements/differentComets500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Different Comets 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 different comets.',
            'image' => '/images/achievements/differentComets1000.svg',
        ]);

        $this->createAchievement([
            'name' => 'Different Comets 2500',
            'is_secret' => false,
            'description' => 'Observe 2500 different comets.',
            'image' => '/images/achievements/differentComets2500.svg',
        ]);

        $this->createAchievement([
            'name' => 'Different Comets 5000',
            'is_secret' => false,
            'description' => 'Observe 5000 different comets.',
            'image' => '/images/achievements/differentComets5000.svg',
        ]);

        // DeepskyLog sketch of the week
        $this->createAchievement([
            'name' => 'DeepskyLog sketch of the week',
            'is_secret' => false,
            'description' => 'One of your observations was selected as DeepskyLog Sketch of the week.',
            'image' => '/images/achievements/sketchOfTheWeek.svg',
        ]);

        // DeepskyLog sketch of the month
        $this->createAchievement([
            'name' => 'DeepskyLog sketch of the month',
            'is_secret' => false,
            'description' => 'One of your observations was selected as DeepskyLog Sketch of the month.',
            'image' => '/images/achievements/sketchOfTheMonth.svg',
        ]);

        // Planets: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Planet 1',
            'is_secret' => false,
            'description' => 'Observe 1 planet.',
            'image' => '/images/achievements/planets.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 10',
            'is_secret' => false,
            'description' => 'Observe 10 planets.',
            'image' => '/images/achievements/planets.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 25',
            'is_secret' => false,
            'description' => 'Observe 25 planets.',
            'image' => '/images/achievements/planets.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 50',
            'is_secret' => false,
            'description' => 'Observe 50 planets.',
            'image' => '/images/achievements/planets.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 100',
            'is_secret' => false,
            'description' => 'Observe 100 planets.',
            'image' => '/images/achievements/planets.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 250',
            'is_secret' => false,
            'description' => 'Observe 250 planets.',
            'image' => '/images/achievements/planets.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 500',
            'is_secret' => false,
            'description' => 'Observe 500 planets.',
            'image' => '/images/achievements/planets.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 planets.',
            'image' => '/images/achievements/planets.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 2500',
            'is_secret' => false,
            'description' => 'Observe 2500 planets.',
            'image' => '/images/achievements/planets.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 5000',
            'is_secret' => false,
            'description' => 'Observe 5000 planets.',
            'image' => '/images/achievements/planets.svg',
        ]);

        // Planets drawings: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Planet 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 planet.',
            'image' => '/images/achievements/planets_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 planets.',
            'image' => '/images/achievements/planets_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 planets.',
            'image' => '/images/achievements/planets_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 planets.',
            'image' => '/images/achievements/planets_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 planets.',
            'image' => '/images/achievements/planets_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw 250 planets.',
            'image' => '/images/achievements/planets_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 planets.',
            'image' => '/images/achievements/planets_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 planets.',
            'image' => '/images/achievements/planets_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 2500 planets.',
            'image' => '/images/achievements/planets_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Planets 5000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5000 planets.',
            'image' => '/images/achievements/planets_drawing.svg',
        ]);

        // Moon + lunar features: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Moon 1',
            'is_secret' => false,
            'description' => 'Observe 1 lunar target (moon or lunar feature).',
            'image' => '/images/achievements/moon.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 10',
            'is_secret' => false,
            'description' => 'Observe 10 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 25',
            'is_secret' => false,
            'description' => 'Observe 25 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 50',
            'is_secret' => false,
            'description' => 'Observe 50 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 100',
            'is_secret' => false,
            'description' => 'Observe 100 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 250',
            'is_secret' => false,
            'description' => 'Observe 250 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 500',
            'is_secret' => false,
            'description' => 'Observe 500 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 2500',
            'is_secret' => false,
            'description' => 'Observe 2500 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 5000',
            'is_secret' => false,
            'description' => 'Observe 5000 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon.svg',
        ]);

        // Moon + lunar feature drawings: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Moon 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 lunar target (moon or lunar feature).',
            'image' => '/images/achievements/moon_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw 250 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 2500 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Moons 5000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5000 lunar targets (moon or lunar feature).',
            'image' => '/images/achievements/moon_drawing.svg',
        ]);

        // Sun: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Sun 1',
            'is_secret' => false,
            'description' => 'Observe the Sun 1 time.',
            'image' => '/images/achievements/sun.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 10',
            'is_secret' => false,
            'description' => 'Observe the Sun 10 times.',
            'image' => '/images/achievements/sun.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 25',
            'is_secret' => false,
            'description' => 'Observe the Sun 25 times.',
            'image' => '/images/achievements/sun.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 50',
            'is_secret' => false,
            'description' => 'Observe the Sun 50 times.',
            'image' => '/images/achievements/sun.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 100',
            'is_secret' => false,
            'description' => 'Observe the Sun 100 times.',
            'image' => '/images/achievements/sun.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 250',
            'is_secret' => false,
            'description' => 'Observe the Sun 250 times.',
            'image' => '/images/achievements/sun.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 500',
            'is_secret' => false,
            'description' => 'Observe the Sun 500 times.',
            'image' => '/images/achievements/sun.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 1000',
            'is_secret' => false,
            'description' => 'Observe the Sun 1000 times.',
            'image' => '/images/achievements/sun.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 2500',
            'is_secret' => false,
            'description' => 'Observe the Sun 2500 times.',
            'image' => '/images/achievements/sun.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 5000',
            'is_secret' => false,
            'description' => 'Observe the Sun 5000 times.',
            'image' => '/images/achievements/sun.svg',
        ]);

        // Sun drawings: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Sun 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw the Sun 1 time.',
            'image' => '/images/achievements/sun_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw the Sun 10 times.',
            'image' => '/images/achievements/sun_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw the Sun 25 times.',
            'image' => '/images/achievements/sun_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw the Sun 50 times.',
            'image' => '/images/achievements/sun_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw the Sun 100 times.',
            'image' => '/images/achievements/sun_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw the Sun 250 times.',
            'image' => '/images/achievements/sun_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw the Sun 500 times.',
            'image' => '/images/achievements/sun_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw the Sun 1000 times.',
            'image' => '/images/achievements/sun_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw the Sun 2500 times.',
            'image' => '/images/achievements/sun_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Sun 5000 Drawings',
            'is_secret' => false,
            'description' => 'Draw the Sun 5000 times.',
            'image' => '/images/achievements/sun_drawing.svg',
        ]);

        // Double star / multiple system achievements
        $this->createAchievement([
            'name' => 'Double Star 1',
            'is_secret' => false,
            'description' => 'Observe 1 double star or multiple system.',
            'image' => '/images/achievements/double_stars.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 10',
            'is_secret' => false,
            'description' => 'Observe 10 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 25',
            'is_secret' => false,
            'description' => 'Observe 25 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 50',
            'is_secret' => false,
            'description' => 'Observe 50 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 100',
            'is_secret' => false,
            'description' => 'Observe 100 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 250',
            'is_secret' => false,
            'description' => 'Observe 250 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 500',
            'is_secret' => false,
            'description' => 'Observe 500 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 2500',
            'is_secret' => false,
            'description' => 'Observe 2500 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 5000',
            'is_secret' => false,
            'description' => 'Observe 5000 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars.svg',
        ]);

        // Double star drawings: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        $this->createAchievement([
            'name' => 'Double Star 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 double star or multiple system.',
            'image' => '/images/achievements/double_stars_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw 250 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 2500 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars_drawing.svg',
        ]);

        $this->createAchievement([
            'name' => 'Double Stars 5000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5000 double stars or multiple systems.',
            'image' => '/images/achievements/double_stars_drawing.svg',
        ]);

        // Get all users
        $users = User::all();

        // Get the Early adopter achievement
        $achievement = Achievement::where('name', 'Early adopter')->first();

        // Loop over all users
        foreach ($users as $user) {
            if ($user->isEarlyAdopter()) {
                // Add achievement to user
                $this->addAchievementToUser($user, $achievement);

            }
        }
    }

    /**
     * Create or update an achievement by name (idempotent).
     */
    protected function createAchievement(array $data): Achievement
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Achievement requires a name');
        }

        $name = $data['name'];

        return Achievement::updateOrCreate(['name' => $name], $data);
    }

    /**
     * Merge duplicate achievement rows (same name) into a canonical id.
     * Moves pivot rows from duplicates to canonical and deletes duplicates.
     */
    protected function dedupeAchievements(): void
    {
        // Find duplicate names
        $dupes = DB::table('achievements')
            ->select(DB::raw('name, GROUP_CONCAT(id ORDER BY id ASC) as ids, COUNT(*) as cnt'))
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($dupes as $d) {
            $ids = explode(',', $d->ids);
            $canonical = array_shift($ids);

            foreach ($ids as $dupId) {
                // Move any pivot rows to canonical id (avoid duplicates)
                $rows = DB::table('achievement_user')->where('achievement_id', $dupId)->get();
                foreach ($rows as $row) {
                    $rowArr = (array) $row;
                    $rowArr['achievement_id'] = (int) $canonical;
                    if (isset($rowArr['id'])) {
                        unset($rowArr['id']);
                    }

                    $exists = DB::table('achievement_user')
                        ->where('achievement_id', $canonical)
                        ->where('user_id', $row->user_id)
                        ->exists();

                    if (!$exists) {
                        try {
                            DB::table('achievement_user')->insert($rowArr);
                        } catch (\Exception $e) {
                            // best effort: ignore insert failures
                        }
                    }
                }

                // Remove any remaining pivot rows referencing the duplicate id
                DB::table('achievement_user')->where('achievement_id', $dupId)->delete();

                // Delete duplicate achievement row
                DB::table('achievements')->where('id', $dupId)->delete();
            }
        }
    }

    public function addAchievementToUser(mixed $user, mixed $achievement): void
    {
        // Add achievement to user
        try {
            $user->grantAchievement($achievement);
        } catch (Exception $e) {
            dump('Unable to add achievement: ' . $e->getMessage());
        }
    }
}
