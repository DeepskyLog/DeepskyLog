<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Team::forceCreate(['user_id' => 1, 'name' => 'Observers', 'personal_team' => 0]);
        Team::forceCreate(['user_id' => 1, 'name' => 'Administrators', 'personal_team' => 0]);
        Team::forceCreate(['user_id' => 1, 'name' => 'Database Experts', 'personal_team' => 0]);
    }
}
