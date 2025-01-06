<?php

namespace App\Console\Commands;

use App\Models\ObserversOld;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;

class updateUserTableCommand extends Command
{
    protected $signature = 'update:user-table';

    protected $description = 'Updates the user table with the changes from the old version of DeepskyLog.';

    public function handle(): void
    {
        $licenses = [
            'Attribution CC BY',
            'Attribution-ShareAlike CC BY-SA',
            'Attribution-NoDerivs CC BY-ND',
            'Attribution-NonCommercial CC BY-NC',
            'Attribution-NonCommercial-ShareAlike CC BY-NC-SA',
            'Attribution-NonCommercial-NoDerivs CC BY-NC-ND',
        ];

        $this->info('Updating Users table...');

        // Get all observers for the old database
        $observers = ObserversOld::all();

        $team = Team::where('name', 'Observers')->first();

        // Check if the user with the given username already exists in the new database
        // If not, create a new user with the given username
        foreach ($observers as $observer) {
            $username = html_entity_decode($observer->id);
            $user = User::where('username', html_entity_decode($username))->first();
            if (! $user) {
                // Check if the user has been approved
                if ($observer->role === 1) {
                    echo 'Adding user: '.$observer->id.PHP_EOL;
                    $language = $observer->language;
                    if ($language == 'de_DE') {
                        $language = 'de';
                    } elseif ($language == 'en_US') {
                        $language = 'en';
                    } elseif ($language == 'fr_FR') {
                        $language = 'fr';
                    } elseif ($language == 'nl_NL') {
                        $language = 'nl';
                    } elseif ($language == 'sv_SV') {
                        $language = 'sv';
                    }
                    [$year, $month, $day, $hour, $minute] = sscanf($observer->registrationDate, '%4d%2d%2d %2d:%2d');
                    $date = date('Y-m-d H:i:s', mktime($hour, $minute, 0, $month, $day, $year));

                    $name = html_entity_decode($observer->firstname).' '.html_entity_decode($observer->name);

                    $atlas = $observer->standardAtlasCode == '' ? 'Interstellarum' : $observer->standardAtlasCode;
                    $stdlocation = $observer->stdlocation == 0 ? null : $observer->stdlocation;
                    $stdtelescope = $observer->stdtelescope == 0 ? null : $observer->stdtelescope;

                    if ($observer->copyright === '') {
                        $copyrightSelection = 'No license (Not recommended)';
                        $copyright = '';
                    } elseif (in_array($observer->copyright, $licenses)) {
                        $copyrightSelection = $observer->copyright;
                        $copyright = $observer->copyright;
                    } else {
                        $copyrightSelection = 'Enter your own copyright text';
                        $copyright = $observer->copyright;
                    }
                    $user = User::create(
                        [
                            'username' => html_entity_decode($observer->id),
                            'name' => $name,
                            'email' => $observer->email,
                            'email_verified_at' => $date,
                            'stdlocation' => $stdlocation,
                            'stdtelescope' => $stdtelescope,
                            'language' => $language,
                            'icqname' => $observer->icqname,
                            'observationlanguage' => $observer->observationlanguage,
                            'standardAtlasCode' => $atlas,
                            'fstOffset' => $observer->fstOffset,
                            'copyrightSelection' => $copyrightSelection,
                            'copyright' => $copyright,
                            'overviewdsos' => $observer->overviewdsos,
                            'lookupdsos' => $observer->lookupdsos,
                            'detaildsos' => $observer->detaildsos,
                            'overviewstars' => $observer->overviewstars,
                            'lookupstars' => $observer->lookupstars,
                            'detailstars' => $observer->detailstars,
                            'atlaspagefont' => $observer->atlaspagefont,
                            'photosize1' => $observer->photosize1 ? $observer->photosize1 : 60,
                            'overviewFoV' => $observer->overviewFoV ? $observer->overviewFoV : 120,
                            'photosize2' => $observer->photosize2 ? $observer->photosize2 : 25,
                            'lookupFoV' => $observer->lookupFoV ? $observer->lookupFoV : 60,
                            'detailFoV' => $observer->detailFoV ? $observer->detailFoV : 15,
                            'sendMail' => $observer->sendMail,
                            'version' => $observer->version,
                            'showInches' => $observer->showInches,
                            'created_at' => $date,
                        ]
                    );
                    $user->password = $observer->password;
                    $user->save();

                    // Attach the user to the team
                    $user->teams()->attach($team);

                    // Switch the user to the specified team
                    $user->switchTeam($team);
                }
            }
        }
    }
}
