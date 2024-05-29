<?php

namespace Database\Seeders;

use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use LevelUp\Experience\Models\Achievement;

class addAchievementsSeeder extends Seeder
{
    public function run(): void
    {
        // Create Early adopter achievements
        Achievement::create([
            'name' => 'Early adopter',
            'is_secret' => false,
            'description' => 'One of the first users of DeepskyLog. Registered before 2006.',
            'image' => '/images/achievements/earlyAdopter.svg',
        ]);

        // Create Top ten observer achievement
        Achievement::create([
            'name' => 'Top ten observer',
            'is_secret' => false,
            'description' => 'In top ten of most observations logged',
            'image' => '/images/achievements/topTenObserver.svg',
        ]);

        // Messier
        Achievement::create([
            'name' => 'Messier Bronze',
            'is_secret' => false,
            'description' => 'Observe 25 Messier objects.',
            'image' => '/images/achievements/messierBronze.svg',
        ]);

        Achievement::create([
            'name' => 'Messier Silver',
            'is_secret' => false,
            'description' => 'Observe 50 Messier objects.',
            'image' => '/images/achievements/messierSilver.svg',
        ]);

        Achievement::create([
            'name' => 'Messier Gold',
            'is_secret' => false,
            'description' => 'Observe all 110 Messier objects.',
            'image' => '/images/achievements/messierGold.svg',
        ]);

        Achievement::create([
            'name' => 'Messier Drawing Bronze',
            'is_secret' => false,
            'description' => 'Draw 25 Messier objects.',
            'image' => '/images/achievements/messierBronzeDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Messier Drawing Silver',
            'is_secret' => false,
            'description' => 'Draw 50 Messier objects.',
            'image' => '/images/achievements/messierSilverDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Messier Drawing Gold',
            'is_secret' => false,
            'description' => 'Draw all 110 Messier objects.',
            'image' => '/images/achievements/messierGoldDrawing.svg',
        ]);

        // Caldwell
        Achievement::create([
            'name' => 'Caldwell Bronze',
            'is_secret' => false,
            'description' => 'Observe 25 Caldwell objects.',
            'image' => '/images/achievements/caldwellBronze.svg',
        ]);

        Achievement::create([
            'name' => 'Caldwell Silver',
            'is_secret' => false,
            'description' => 'Observe 50 Caldwell objects.',
            'image' => '/images/achievements/caldwellSilver.svg',
        ]);

        Achievement::create([
            'name' => 'Caldwell Gold',
            'is_secret' => false,
            'description' => 'Observe all 109 Caldwell objects.',
            'image' => '/images/achievements/caldwellGold.svg',
        ]);

        Achievement::create([
            'name' => 'Caldwell Drawing Bronze',
            'is_secret' => false,
            'description' => 'Draw 25 Caldwell objects.',
            'image' => '/images/achievements/caldwellBronzeDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Caldwell Drawing Silver',
            'is_secret' => false,
            'description' => 'Draw 50 Caldwell objects.',
            'image' => '/images/achievements/caldwellSilverDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Caldwell Drawing Gold',
            'is_secret' => false,
            'description' => 'Draw all 109 Caldwell objects.',
            'image' => '/images/achievements/caldwellGoldDrawing.svg',
        ]);

        // Herschel-400
        Achievement::create([
            'name' => 'Herschel 400 Bronze',
            'is_secret' => false,
            'description' => 'Observe 25 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400Bronze.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel 400 Silver',
            'is_secret' => false,
            'description' => 'Observe 50 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400Silver.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel 400 Gold',
            'is_secret' => false,
            'description' => 'Observe 100 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400Gold.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel 400 Diamond',
            'is_secret' => false,
            'description' => 'Observe 200 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400Diamond.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel 400 Platinum',
            'is_secret' => false,
            'description' => 'Observe all 400 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400Platinum.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel 400 Drawing Bronze',
            'is_secret' => false,
            'description' => 'Draw 25 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400BronzeDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel 400 Drawing Silver',
            'is_secret' => false,
            'description' => 'Draw 50 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400SilverDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel 400 Drawing Gold',
            'is_secret' => false,
            'description' => 'Draw 100 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400GoldDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel 400 Drawing Diamond',
            'is_secret' => false,
            'description' => 'Draw 200 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400DiamondDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel 400 Drawing Platinum',
            'is_secret' => false,
            'description' => 'Draw all 400 Herschel 400 objects.',
            'image' => '/images/achievements/herschel400PlatinumDrawing.svg',
        ]);

        // Herschel-II
        Achievement::create([
            'name' => 'Herschel II Bronze',
            'is_secret' => false,
            'description' => 'Observe 25 Herschel II objects.',
            'image' => '/images/achievements/herschelIIBronze.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel II Silver',
            'is_secret' => false,
            'description' => 'Observe 50 Herschel II objects.',
            'image' => '/images/achievements/herschelIISilver.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel II Gold',
            'is_secret' => false,
            'description' => 'Observe 100 Herschel II objects.',
            'image' => '/images/achievements/herschelIIGold.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel II Diamond',
            'is_secret' => false,
            'description' => 'Observe 200 Herschel II objects.',
            'image' => '/images/achievements/herschelIIDiamond.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel II Platinum',
            'is_secret' => false,
            'description' => 'Observe all 400 Herschel II objects.',
            'image' => '/images/achievements/herschelIIPlatinum.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel II Drawing Bronze',
            'is_secret' => false,
            'description' => 'Draw 25 Herschel II objects.',
            'image' => '/images/achievements/herschelIIBronzeDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel II Drawing Silver',
            'is_secret' => false,
            'description' => 'Draw 50 Herschel II objects.',
            'image' => '/images/achievements/herschelIISilverDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel II Drawing Gold',
            'is_secret' => false,
            'description' => 'Draw 100 Herschel II objects.',
            'image' => '/images/achievements/herschelIIGoldDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel II Drawing Diamond',
            'is_secret' => false,
            'description' => 'Draw 200 Herschel II objects.',
            'image' => '/images/achievements/herschelIIDiamondDrawing.svg',
        ]);

        Achievement::create([
            'name' => 'Herschel II Drawing Platinum',
            'is_secret' => false,
            'description' => 'Draw all 400 Herschel II objects.',
            'image' => '/images/achievements/herschelIIPlatinumDrawing.svg',
        ]);

        // Total number of drawings: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        Achievement::create([
            'name' => 'Drawing 1',
            'is_secret' => false,
            'description' => 'Make 1 drawing.',
            'image' => '/images/achievements/drawings1.svg',
        ]);

        Achievement::create([
            'name' => 'Drawings 10',
            'is_secret' => false,
            'description' => 'Make 10 drawings.',
            'image' => '/images/achievements/drawings10.svg',
        ]);

        Achievement::create([
            'name' => 'Drawings 25',
            'is_secret' => false,
            'description' => 'Make 25 drawings.',
            'image' => '/images/achievements/drawings25.svg',
        ]);

        Achievement::create([
            'name' => 'Drawings 50',
            'is_secret' => false,
            'description' => 'Make 50 drawings.',
            'image' => '/images/achievements/drawings50.svg',
        ]);

        Achievement::create([
            'name' => 'Drawings 100',
            'is_secret' => false,
            'description' => 'Make 100 drawings.',
            'image' => '/images/achievements/drawings100.svg',
        ]);

        Achievement::create([
            'name' => 'Drawings 250',
            'is_secret' => false,
            'description' => 'Make 250 drawings.',
            'image' => '/images/achievements/drawings250.svg',
        ]);

        Achievement::create([
            'name' => 'Drawings 500',
            'is_secret' => false,
            'description' => 'Make 500 drawings.',
            'image' => '/images/achievements/drawings500.svg',
        ]);

        Achievement::create([
            'name' => 'Drawings 1000',
            'is_secret' => false,
            'description' => 'Make 1000 drawings.',
            'image' => '/images/achievements/drawings1000.svg',
        ]);

        Achievement::create([
            'name' => 'Drawings 2500',
            'is_secret' => false,
            'description' => 'Make 2500 drawings.',
            'image' => '/images/achievements/drawings2500.svg',
        ]);

        Achievement::create([
            'name' => 'Drawings 5000',
            'is_secret' => false,
            'description' => 'Make 5000 drawings.',
            'image' => '/images/achievements/drawings5000.svg',
        ]);

        // Open cluster: 1, 5, 10, 25, 50, 100, 200, 500, 1000, 1500
        Achievement::create([
            'name' => 'Open Cluster 1',
            'is_secret' => false,
            'description' => 'Observe 1 open cluster.',
            'image' => '/images/achievements/openClusters1.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 5',
            'is_secret' => false,
            'description' => 'Observe 5 open clusters.',
            'image' => '/images/achievements/openClusters5.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 10',
            'is_secret' => false,
            'description' => 'Observe 10 open clusters.',
            'image' => '/images/achievements/openClusters10.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 25',
            'is_secret' => false,
            'description' => 'Observe 25 open clusters.',
            'image' => '/images/achievements/openClusters25.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 50',
            'is_secret' => false,
            'description' => 'Observe 50 open clusters.',
            'image' => '/images/achievements/openClusters50.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 100',
            'is_secret' => false,
            'description' => 'Observe 100 open clusters.',
            'image' => '/images/achievements/openClusters100.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 200',
            'is_secret' => false,
            'description' => 'Observe 200 open clusters.',
            'image' => '/images/achievements/openClusters200.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 500',
            'is_secret' => false,
            'description' => 'Observe 500 open clusters.',
            'image' => '/images/achievements/openClusters500.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 open clusters.',
            'image' => '/images/achievements/openClusters1000.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 2500',
            'is_secret' => false,
            'description' => 'Observe 2500 open clusters.',
            'image' => '/images/achievements/openClusters2500.svg',
        ]);

        // Drawings of open clusters
        Achievement::create([
            'name' => 'Open Cluster 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 open cluster.',
            'image' => '/images/achievements/openClusters1Drawing.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 5 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5 open clusters.',
            'image' => '/images/achievements/openClusters5Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 open clusters.',
            'image' => '/images/achievements/openClusters10Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 open clusters.',
            'image' => '/images/achievements/openClusters25Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 open clusters.',
            'image' => '/images/achievements/openClusters50Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 open clusters.',
            'image' => '/images/achievements/openClusters100Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 200 Drawings',
            'is_secret' => false,
            'description' => 'Draw 200 open clusters.',
            'image' => '/images/achievements/openClusters200Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 open clusters.',
            'image' => '/images/achievements/openClusters500Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 open clusters.',
            'image' => '/images/achievements/openClusters1000Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Open Clusters 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 2500 open clusters.',
            'image' => '/images/achievements/openClusters2500Drawings.svg',
        ]);

        // Globular clusters: 1, 3, 5, 10, 15, 25, 50, 75, 100, 150
        Achievement::create([
            'name' => 'Globular Cluster 1',
            'is_secret' => false,
            'description' => 'Observe 1 globular cluster.',
            'image' => '/images/achievements/globularClusters1.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 3',
            'is_secret' => false,
            'description' => 'Observe 3 globular clusters.',
            'image' => '/images/achievements/globularClusters3.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 5',
            'is_secret' => false,
            'description' => 'Observe 5 globular clusters.',
            'image' => '/images/achievements/globularClusters5.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 10',
            'is_secret' => false,
            'description' => 'Observe 10 globular clusters.',
            'image' => '/images/achievements/globularClusters10.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 15',
            'is_secret' => false,
            'description' => 'Observe 15 globular clusters.',
            'image' => '/images/achievements/globularClusters15.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 25',
            'is_secret' => false,
            'description' => 'Observe 25 globular clusters.',
            'image' => '/images/achievements/globularClusters25.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 50',
            'is_secret' => false,
            'description' => 'Observe 50 globular clusters.',
            'image' => '/images/achievements/globularClusters50.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 75',
            'is_secret' => false,
            'description' => 'Observe 75 globular clusters.',
            'image' => '/images/achievements/globularClusters75.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 100',
            'is_secret' => false,
            'description' => 'Observe 100 globular clusters.',
            'image' => '/images/achievements/globularClusters100.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 150',
            'is_secret' => false,
            'description' => 'Observe 150 globular clusters.',
            'image' => '/images/achievements/globularClusters150.svg',
        ]);

        // Drawings of globular clusters
        Achievement::create([
            'name' => 'Globular Cluster 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 globular cluster.',
            'image' => '/images/achievements/globularClusters1Drawing.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 3 Drawings',
            'is_secret' => false,
            'description' => 'Draw 3 globular clusters.',
            'image' => '/images/achievements/globularClusters3Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 5 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5 globular clusters.',
            'image' => '/images/achievements/globularClusters5Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 globular clusters.',
            'image' => '/images/achievements/globularClusters10Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 15 Drawings',
            'is_secret' => false,
            'description' => 'Draw 15 globular clusters.',
            'image' => '/images/achievements/globularClusters15Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 globular clusters.',
            'image' => '/images/achievements/globularClusters25Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 globular clusters.',
            'image' => '/images/achievements/globularClusters50Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 75 Drawings',
            'is_secret' => false,
            'description' => 'Draw 75 globular clusters.',
            'image' => '/images/achievements/globularClusters75Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 globular clusters.',
            'image' => '/images/achievements/globularClusters100Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Globular Clusters 150 Drawings',
            'is_secret' => false,
            'description' => 'Draw 150 globular clusters.',
            'image' => '/images/achievements/globularClusters150Drawings.svg',
        ]);

        // Planetary nebulae: 1, 3, 5, 10, 25, 50, 100, 250, 500, 1000
        Achievement::create([
            'name' => 'Planetary Nebula 1',
            'is_secret' => false,
            'description' => 'Observe 1 planetary nebula.',
            'image' => '/images/achievements/planetaryNebulae1.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 3',
            'is_secret' => false,
            'description' => 'Observe 3 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae3.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 5',
            'is_secret' => false,
            'description' => 'Observe 5 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae5.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 10',
            'is_secret' => false,
            'description' => 'Observe 10 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae10.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 25',
            'is_secret' => false,
            'description' => 'Observe 25 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae25.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 50',
            'is_secret' => false,
            'description' => 'Observe 50 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae50.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 100',
            'is_secret' => false,
            'description' => 'Observe 100 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae100.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 250',
            'is_secret' => false,
            'description' => 'Observe 250 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae250.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 500',
            'is_secret' => false,
            'description' => 'Observe 500 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae500.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae1000.svg',
        ]);

        // Drawings of planetary nebulae
        Achievement::create([
            'name' => 'Planetary Nebula 1 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1 planetary nebula.',
            'image' => '/images/achievements/planetaryNebulae1Drawing.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 3 Drawings',
            'is_secret' => false,
            'description' => 'Draw 3 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae3Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 5 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae5Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae10Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae25Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae50Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae100Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw 250 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae250Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae500Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Planetary Nebula 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 planetary nebulae.',
            'image' => '/images/achievements/planetaryNebulae1000Drawings.svg',
        ]);

        // Galaxies: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        Achievement::create([
            'name' => 'Galaxy 1',
            'is_secret' => false,
            'description' => 'Observe 1 galaxy.',
            'image' => '/images/achievements/galaxies1.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 10',
            'is_secret' => false,
            'description' => 'Observe 10 galaxies.',
            'image' => '/images/achievements/galaxies10.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 25',
            'is_secret' => false,
            'description' => 'Observe 25 galaxies.',
            'image' => '/images/achievements/galaxies25.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 50',
            'is_secret' => false,
            'description' => 'Observe 50 galaxies.',
            'image' => '/images/achievements/galaxies50.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 100',
            'is_secret' => false,
            'description' => 'Observe 100 galaxies.',
            'image' => '/images/achievements/galaxies100.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 250',
            'is_secret' => false,
            'description' => 'Observe 250 galaxies.',
            'image' => '/images/achievements/galaxies250.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 500',
            'is_secret' => false,
            'description' => 'Observe 500 galaxies.',
            'image' => '/images/achievements/galaxies500.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 galaxies.',
            'image' => '/images/achievements/galaxies1000.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 2500',
            'is_secret' => false,
            'description' => 'Observe 2500 galaxies.',
            'image' => '/images/achievements/galaxies2500.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 5000',
            'is_secret' => false,
            'description' => 'Observe 5000 galaxies.',
            'image' => '/images/achievements/galaxies5000.svg',
        ]);

        // Drawings of galaxies
        Achievement::create([
            'name' => 'Galaxy 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 galaxy.',
            'image' => '/images/achievements/galaxies1Drawing.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 galaxies.',
            'image' => '/images/achievements/galaxies10Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 galaxies.',
            'image' => '/images/achievements/galaxies25Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 galaxies.',
            'image' => '/images/achievements/galaxies50Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 galaxies.',
            'image' => '/images/achievements/galaxies100Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw 250 galaxies.',
            'image' => '/images/achievements/galaxies250Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 galaxies.',
            'image' => '/images/achievements/galaxies500Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 galaxies.',
            'image' => '/images/achievements/galaxies1000Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 2500 galaxies.',
            'image' => '/images/achievements/galaxies2500Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Galaxies 5000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5000 galaxies.',
            'image' => '/images/achievements/galaxies5000Drawings.svg',
        ]);

        // Nebulae: 1, 5, 10, 25, 50, 75, 100, 150, 200, 300
        Achievement::create([
            'name' => 'Nebula 1',
            'is_secret' => false,
            'description' => 'Observe 1 nebula.',
            'image' => '/images/achievements/nebulae1.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 5',
            'is_secret' => false,
            'description' => 'Observe 5 nebulae.',
            'image' => '/images/achievements/nebulae5.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 10',
            'is_secret' => false,
            'description' => 'Observe 10 nebulae.',
            'image' => '/images/achievements/nebulae10.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 25',
            'is_secret' => false,
            'description' => 'Observe 25 nebulae.',
            'image' => '/images/achievements/nebulae25.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 50',
            'is_secret' => false,
            'description' => 'Observe 50 nebulae.',
            'image' => '/images/achievements/nebulae50.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 75',
            'is_secret' => false,
            'description' => 'Observe 75 nebulae.',
            'image' => '/images/achievements/nebulae75.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 100',
            'is_secret' => false,
            'description' => 'Observe 100 nebulae.',
            'image' => '/images/achievements/nebulae100.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 150',
            'is_secret' => false,
            'description' => 'Observe 150 nebulae.',
            'image' => '/images/achievements/nebulae150.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 200',
            'is_secret' => false,
            'description' => 'Observe 200 nebulae.',
            'image' => '/images/achievements/nebulae200.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 300',
            'is_secret' => false,
            'description' => 'Observe 300 nebulae.',
            'image' => '/images/achievements/nebulae300.svg',
        ]);

        // Drawings of nebulae
        Achievement::create([
            'name' => 'Nebula 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 nebula.',
            'image' => '/images/achievements/nebulae1Drawing.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 5 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5 nebulae.',
            'image' => '/images/achievements/nebulae5Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 nebulae.',
            'image' => '/images/achievements/nebulae10Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 nebulae.',
            'image' => '/images/achievements/nebulae25Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 nebulae.',
            'image' => '/images/achievements/nebulae50Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 75 Drawings',
            'is_secret' => false,
            'description' => 'Draw 75 nebulae.',
            'image' => '/images/achievements/nebulae75Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 nebulae.',
            'image' => '/images/achievements/nebulae100Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 150 Drawings',
            'is_secret' => false,
            'description' => 'Draw 150 nebulae.',
            'image' => '/images/achievements/nebulae150Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 200 Drawings',
            'is_secret' => false,
            'description' => 'Draw 200 nebulae.',
            'image' => '/images/achievements/nebulae200Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Nebulae 300 Drawings',
            'is_secret' => false,
            'description' => 'Draw 300 nebulae.',
            'image' => '/images/achievements/nebulae300Drawings.svg',
        ]);

        // Different objects: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        Achievement::create([
            'name' => 'Object 1',
            'is_secret' => false,
            'description' => 'Observe 1 object.',
            'image' => '/images/achievements/objects1.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 10',
            'is_secret' => false,
            'description' => 'Observe 10 different objects.',
            'image' => '/images/achievements/objects10.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 25',
            'is_secret' => false,
            'description' => 'Observe 25 different objects.',
            'image' => '/images/achievements/objects25.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 50',
            'is_secret' => false,
            'description' => 'Observe 50 different objects.',
            'image' => '/images/achievements/objects50.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 100',
            'is_secret' => false,
            'description' => 'Observe 100 different objects.',
            'image' => '/images/achievements/objects100.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 250',
            'is_secret' => false,
            'description' => 'Observe 250 different objects.',
            'image' => '/images/achievements/objects250.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 500',
            'is_secret' => false,
            'description' => 'Observe 500 different objects.',
            'image' => '/images/achievements/objects500.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 different objects.',
            'image' => '/images/achievements/objects1000.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 2500',
            'is_secret' => false,
            'description' => 'Observe 2500 different objects.',
            'image' => '/images/achievements/objects2500.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 5000',
            'is_secret' => false,
            'description' => 'Observe 5000 different objects.',
            'image' => '/images/achievements/objects5000.svg',
        ]);

        // Drawings of different objects
        Achievement::create([
            'name' => 'Object 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 object.',
            'image' => '/images/achievements/objects1Drawing.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 different objects.',
            'image' => '/images/achievements/objects10Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 different objects.',
            'image' => '/images/achievements/objects25Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 different objects.',
            'image' => '/images/achievements/objects50Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 different objects.',
            'image' => '/images/achievements/objects100Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw 250 different objects.',
            'image' => '/images/achievements/objects250Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 different objects.',
            'image' => '/images/achievements/objects500Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 different objects.',
            'image' => '/images/achievements/objects1000Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 2500 different objects.',
            'image' => '/images/achievements/objects2500Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Objects 5000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5000 different objects.',
            'image' => '/images/achievements/objects5000Drawings.svg',
        ]);

        // Total comet observations: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        Achievement::create([
            'name' => 'Comet 1',
            'is_secret' => false,
            'description' => 'Make 1 comet observation.',
            'image' => '/images/achievements/comets1.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 10',
            'is_secret' => false,
            'description' => 'Make 10 comet observations.',
            'image' => '/images/achievements/comets10.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 25',
            'is_secret' => false,
            'description' => 'Make 25 comet observations.',
            'image' => '/images/achievements/comets25.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 50',
            'is_secret' => false,
            'description' => 'Make 50 comet observations.',
            'image' => '/images/achievements/comets50.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 100',
            'is_secret' => false,
            'description' => 'Make 100 comet observations.',
            'image' => '/images/achievements/comets100.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 250',
            'is_secret' => false,
            'description' => 'Make 250 comet observations.',
            'image' => '/images/achievements/comets250.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 500',
            'is_secret' => false,
            'description' => 'Make 500 comet observations.',
            'image' => '/images/achievements/comets500.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 1000',
            'is_secret' => false,
            'description' => 'Make 1000 comet observations.',
            'image' => '/images/achievements/comets1000.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 2500',
            'is_secret' => false,
            'description' => 'Make 2500 comet observations.',
            'image' => '/images/achievements/comets2500.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 5000',
            'is_secret' => false,
            'description' => 'Make 5000 comet observations.',
            'image' => '/images/achievements/comets5000.svg',
        ]);

        // Drawings of comets: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        Achievement::create([
            'name' => 'Comet 1 Drawing',
            'is_secret' => false,
            'description' => 'Draw 1 comet.',
            'image' => '/images/achievements/comets1Drawing.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 10 Drawings',
            'is_secret' => false,
            'description' => 'Draw 10 comets.',
            'image' => '/images/achievements/comets10Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 25 Drawings',
            'is_secret' => false,
            'description' => 'Draw 25 comets.',
            'image' => '/images/achievements/comets25Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 50 Drawings',
            'is_secret' => false,
            'description' => 'Draw 50 comets.',
            'image' => '/images/achievements/comets50Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 100 Drawings',
            'is_secret' => false,
            'description' => 'Draw 100 comets.',
            'image' => '/images/achievements/comets100Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 250 Drawings',
            'is_secret' => false,
            'description' => 'Draw 250 comets.',
            'image' => '/images/achievements/comets250Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 500 comets.',
            'image' => '/images/achievements/comets500Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 1000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 1000 comets.',
            'image' => '/images/achievements/comets1000Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 2500 Drawings',
            'is_secret' => false,
            'description' => 'Draw 2500 comets.',
            'image' => '/images/achievements/comets2500Drawings.svg',
        ]);

        Achievement::create([
            'name' => 'Comets 5000 Drawings',
            'is_secret' => false,
            'description' => 'Draw 5000 comets.',
            'image' => '/images/achievements/comets5000Drawings.svg',
        ]);

        // Different comets seen: 1, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000
        Achievement::create([
            'name' => 'Different Comets 1',
            'is_secret' => false,
            'description' => 'Observe 1 comet.',
            'image' => '/images/achievements/differentComets1.svg',
        ]);

        Achievement::create([
            'name' => 'Different Comets 10',
            'is_secret' => false,
            'description' => 'Observe 10 different comets.',
            'image' => '/images/achievements/differentComets10.svg',
        ]);

        Achievement::create([
            'name' => 'Different Comets 25',
            'is_secret' => false,
            'description' => 'Observe 25 different comets.',
            'image' => '/images/achievements/differentComets25.svg',
        ]);

        Achievement::create([
            'name' => 'Different Comets 50',
            'is_secret' => false,
            'description' => 'Observe 50 different comets.',
            'image' => '/images/achievements/differentComets50.svg',
        ]);

        Achievement::create([
            'name' => 'Different Comets 100',
            'is_secret' => false,
            'description' => 'Observe 100 different comets.',
            'image' => '/images/achievements/differentComets100.svg',
        ]);

        Achievement::create([
            'name' => 'Different Comets 250',
            'is_secret' => false,
            'description' => 'Observe 250 different comets.',
            'image' => '/images/achievements/differentComets250.svg',
        ]);

        Achievement::create([
            'name' => 'Different Comets 500',
            'is_secret' => false,
            'description' => 'Observe 500 different comets.',
            'image' => '/images/achievements/differentComets500.svg',
        ]);

        Achievement::create([
            'name' => 'Different Comets 1000',
            'is_secret' => false,
            'description' => 'Observe 1000 different comets.',
            'image' => '/images/achievements/differentComets1000.svg',
        ]);

        Achievement::create([
            'name' => 'Different Comets 2500',
            'is_secret' => false,
            'description' => 'Observe 2500 different comets.',
            'image' => '/images/achievements/differentComets2500.svg',
        ]);

        Achievement::create([
            'name' => 'Different Comets 5000',
            'is_secret' => false,
            'description' => 'Observe 5000 different comets.',
            'image' => '/images/achievements/differentComets5000.svg',
        ]);

        // DeepskyLog sketch of the week
        Achievement::create([
            'name' => 'DeepskyLog sketch of the week',
            'is_secret' => false,
            'description' => 'One of your observations was selected as DeepskyLog Sketch of the week.',
            'image' => '/images/achievements/sketchOfTheWeek.svg',
        ]);

        // DeepskyLog sketch of the month
        Achievement::create([
            'name' => 'DeepskyLog sketch of the month',
            'is_secret' => false,
            'description' => 'One of your observations was selected as DeepskyLog Sketch of the month.',
            'image' => '/images/achievements/sketchOfTheMonth.svg',
        ]);

        // Get all users
        $users = User::all();

        // Get the Early adopter achievement
        $achievement = Achievement::where('name', 'Early adopter')->get()[0];

        // Loop over all users
        foreach ($users as $user) {
            if ($user->isEarlyAdopter()) {
                // Add achievement to user
                $this->addAchievementToUser($user, $achievement);

            }
        }
    }

    public function addAchievementToUser(mixed $user, mixed $achievement): void
    {
        // Add achievement to user
        try {
            $user->grantAchievement($achievement);
        } catch (Exception $e) {
            dump('Unable to add achievement: '.$e->getMessage());
        }
    }
}
