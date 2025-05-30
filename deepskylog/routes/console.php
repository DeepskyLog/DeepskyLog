<?php

use App\Console\Commands\updateAchievementsCommand;
use App\Console\Commands\updateObservationsCommand;
use App\Console\Commands\updateOldEyepieceTableCommand;
use App\Console\Commands\updateOldInstrumentTableCommand;
use App\Console\Commands\updateOldLensTableCommand;
use App\Console\Commands\updateUserTableCommand;
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
Schedule::command(updateUserTableCommand::class)->everyFiveMinutes();
Schedule::command(updateObservationsCommand::class)->daily();
Schedule::command(updateOldInstrumentTableCommand::class)->everyFiveMinutes();
Schedule::command(updateOldEyepieceTableCommand::class)->everyFiveMinutes();
Schedule::command(updateOldLensTableCommand::class)->everyFifteenMinutes();
