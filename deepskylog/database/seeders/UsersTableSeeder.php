<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use App\Models\ObserversOld;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $licenses = [
            'Attribution CC BY',
            'Attribution-ShareAlike CC BY-SA',
            'Attribution-NoDerivs CC BY-ND',
            'Attribution-NonCommercial CC BY-NC',
            'Attribution-NonCommercial-ShareAlike CC BY-NC-SA',
            'Attribution-NonCommercial-NoDerivs CC BY-NC-ND',
        ];

        $accountData = ObserversOld::all();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->truncate();

        $firstUser = $accountData->where('id', 'wim')[2];

        [$year, $month, $day, $hour, $minute] = sscanf($firstUser->registrationDate, '%4d%2d%2d %2d:%2d');
        $date = date('Y-m-d H:i:s', mktime($hour, $minute, 0, $month, $day, $year));
        $name = html_entity_decode($firstUser->firstname) . ' ' . html_entity_decode($firstUser->name);
        $atlas = $firstUser->standardAtlasCode == '' ? 'Interstellarum' : $firstUser->standardAtlasCode;
        $stdlocation = $firstUser->stdlocation == 0 ? null : $firstUser->stdlocation;
        $stdtelescope = $firstUser->stdtelescope == 0 ? null : $firstUser->stdtelescope;
        $language = 'en';

        // First add user wim
        $user = User::create(
            [
                'username'            => html_entity_decode($firstUser->id),
                'name'                => $name,
                'email'               => $firstUser->email,
                'email_verified_at'   => $date,
                'stdlocation'         => $stdlocation,
                'stdtelescope'        => $stdtelescope,
                'language'            => $language,
                'icqname'             => $firstUser->icqname,
                'observationlanguage' => $firstUser->observationlanguage,
                'standardAtlasCode'   => $atlas,
                'fstOffset'           => $firstUser->fstOffset,
                'copyrightSelection'  => $firstUser->copyright,
                'copyright'           => $firstUser->copyright,
                'overviewdsos'        => $firstUser->overviewdsos,
                'lookupdsos'          => $firstUser->lookupdsos,
                'detaildsos'          => $firstUser->detaildsos,
                'overviewstars'       => $firstUser->overviewstars,
                'lookupstars'         => $firstUser->lookupstars,
                'detailstars'         => $firstUser->detailstars,
                'atlaspagefont'       => $firstUser->atlaspagefont,
                'photosize1'          => $firstUser->photosize1,
                'overviewFoV'         => $firstUser->overviewFoV,
                'photosize2'          => $firstUser->photosize2,
                'lookupFoV'           => $firstUser->lookupFoV,
                'detailFoV'           => $firstUser->detailFoV,
                'sendMail'            => $firstUser->sendMail,
                'version'             => $firstUser->version,
                'showInches'          => $firstUser->showInches,
                'created_at'          => $date,
            ]
        );

        $user->password = $firstUser->password;
        $user->save();

        $team = Team::where('name', 'Administrators')->first();
        // Attach the user to the team
        $user->teams()->attach($team);

        $team = Team::where('name', 'Database Experts')->first();
        // Attach the user to the team
        $user->teams()->attach($team);

        $team = Team::where('name', 'Observers')->first();
        // Attach the user to the team
        $user->teams()->attach($team);

        // Switch the user to the specified team
        $user->switchTeam($team);

        $filename = 'observer_pics/wim.jpg';

        if (file_exists($filename)) {
            $path = Storage::putFile('public/profile-photos', new File($filename));
            $user->profile_photo_path = Str::replace('public/', '', $path);
            $user->save();
        }
        foreach ($accountData as $accountSingle) {
            $language = $accountSingle->language;
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
            [$year, $month, $day, $hour, $minute] = sscanf($accountSingle->registrationDate, '%4d%2d%2d %2d:%2d');
            $date = date('Y-m-d H:i:s', mktime($hour, $minute, 0, $month, $day, $year));

            $name = html_entity_decode($accountSingle->firstname) . ' ' . html_entity_decode($accountSingle->name);

            $atlas = $accountSingle->standardAtlasCode == '' ? 'Interstellarum' : $accountSingle->standardAtlasCode;
            $stdlocation = $accountSingle->stdlocation == 0 ? null : $accountSingle->stdlocation;
            $stdtelescope = $accountSingle->stdtelescope == 0 ? null : $accountSingle->stdtelescope;

            if ($accountSingle->id !== 'vvs04478Admin'
                && $accountSingle->id !== 'evdjadmin'
                && $accountSingle->id !== 'TomC_developer'
                && $accountSingle->id !== 'wvreeven-admin'
                && $accountSingle->id !== 'Bert Admin'
                && $accountSingle->id !== 'Jef Admin'
                && $accountSingle->id !== 'adminbob'
                && $accountSingle->id !== 'yann_admin'
                && $accountSingle->id !== 'vanderkouwe-admin'
                && $accountSingle->id !== 'FrankHolBeheerder'
                && $accountSingle->id !== 'admin'
                && $accountSingle->id !== 'wim'
            ) {
                if ($accountSingle->copyright === '') {
                    $copyrightSelection = 'No license (Not recommended)';
                    $copyright = '';
                } elseif (in_array($accountSingle->copyright, $licenses)) {
                    $copyrightSelection = $accountSingle->copyright;
                    $copyright = $accountSingle->copyright;
                } else {
                    $copyrightSelection = 'Enter your own copyright text';
                    $copyright = $accountSingle->copyright;
                }
                $user = User::create(
                    [
                        'username'            => html_entity_decode($accountSingle->id),
                        'name'                => $name,
                        'email'               => $accountSingle->email,
                        'email_verified_at'   => $date,
                        'stdlocation'         => $stdlocation,
                        'stdtelescope'        => $stdtelescope,
                        'language'            => $language,
                        'icqname'             => $accountSingle->icqname,
                        'observationlanguage' => $accountSingle->observationlanguage,
                        'standardAtlasCode'   => $atlas,
                        'fstOffset'           => $accountSingle->fstOffset,
                        'copyrightSelection'  => $copyrightSelection,
                        'copyright'           => $copyright,
                        'overviewdsos'        => $accountSingle->overviewdsos,
                        'lookupdsos'          => $accountSingle->lookupdsos,
                        'detaildsos'          => $accountSingle->detaildsos,
                        'overviewstars'       => $accountSingle->overviewstars,
                        'lookupstars'         => $accountSingle->lookupstars,
                        'detailstars'         => $accountSingle->detailstars,
                        'atlaspagefont'       => $accountSingle->atlaspagefont,
                        'photosize1'          => $accountSingle->photosize1 ? $accountSingle->photosize1 : 60,
                        'overviewFoV'         => $accountSingle->overviewFoV ? $accountSingle->overviewFoV : 120,
                        'photosize2'          => $accountSingle->photosize2 ? $accountSingle->photosize2 : 25,
                        'lookupFoV'           => $accountSingle->lookupFoV ? $accountSingle->lookupFoV : 60,
                        'detailFoV'           => $accountSingle->detailFoV ? $accountSingle->detailFoV : 15,
                        'sendMail'            => $accountSingle->sendMail,
                        'version'             => $accountSingle->version,
                        'showInches'          => $accountSingle->showInches,
                        'created_at'          => $date,
                    ]
                );
                $user->password = $accountSingle->password;
                $user->save();

                // Add the user to the correct teams
                if ($accountSingle->id == 'vvs04478') {
                    $team = Team::where('name', 'Administrators')->first();
                    // Attach the user to the team
                    $user->teams()->attach($team);
                }

                if ($accountSingle->id == 'vvs04478Admin' ||
                    $accountSingle->id == 'Eric VdJ' ||
                    $accountSingle->id == 'vvs03296' ||
                    $accountSingle->id == 'wvreeven' ||
                    $accountSingle->id == 'Albireo' ||
                    $accountSingle->id == 'Jef De Wit' ||
                    $accountSingle->id == 'yapo' ||
                    $accountSingle->id == 'J.W. van der Kouwe' ||
                    $accountSingle->id == 'SkyHeerlen' ||
                    $accountSingle->id == 'Bob Hogeveen') {
                    $team = Team::where('name', 'Database Experts')->first();
                    // Attach the user to the team
                    $user->teams()->attach($team);
                }
                $team = Team::where('name', 'Observers')->first();
                // Attach the user to the team
                $user->teams()->attach($team);

                // Switch the user to the specified team
                $user->switchTeam($team);

                // TODO: Make sure to make a link to the correct directory!
                $filename = 'observer_pics/'
                    . $user->username . '.jpg';

                if (file_exists($filename)) {
                    $path = Storage::putFile('public/profile-photos', new File($filename));
                    $user->profile_photo_path = Str::replace('public/', '', $path);
                    $user->save();
                }
            }
        }
    }
}
