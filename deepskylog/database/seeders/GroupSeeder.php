<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // The roles are checked on these ids (see User::isAdministrator()).
        Team::forceCreate(['id' => Team::OBSERVERS, 'user_id' => 1, 'name' => 'Observers', 'personal_team' => 0]);
        Team::forceCreate(['id' => Team::ADMINISTRATORS, 'user_id' => 1, 'name' => 'Administrators', 'personal_team' => 0]);
        Team::forceCreate(['id' => Team::DATABASE_EXPERTS, 'user_id' => 1, 'name' => 'Database Experts', 'personal_team' => 0]);
    }
}
