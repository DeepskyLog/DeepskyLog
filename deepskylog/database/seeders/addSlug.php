<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class addSlug extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Loop over the users
        $users = User::all();

        foreach ($users as $user) {
            // Add the slugs to the users
            $user->save();
        }

        // Loop over the targetnames
        $teams = Team::all();

        foreach ($teams as $team) {
            // Add the slugs to the users
            $team->save();
        }
    }
}
