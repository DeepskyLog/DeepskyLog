<?php

use App\Console\Commands\updateAchievementsCommand;
// legacy updateOld* commands removed
// legacy update command removed: updateUserTableCommand
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(updateAchievementsCommand::class)->everySixHours();
// Schedule::command(updateUserTableCommand::class)->everyFifteenMinutes();
// update:observations command removed; counts are maintained by model observer
// updateOld* scheduled commands removed
