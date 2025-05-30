<?php

namespace Database\Seeders;

use App\Models\Lens;
use App\Models\LensesOld;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LensSeeder extends Seeder
{
    public function run(): void
    {
        $lensData = LensesOld::all();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('eyepieces')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($lensData as $lens) {
            $observer = User::where('username', html_entity_decode($lens->observer))->pluck('id');
            if (count($observer) > 0) {
                if ($lens->timestamp == '') {
                    $date = date('Y-m-d H:i:s');
                } else {
                    [$year, $month, $day, $hour, $minute, $second]
                        = sscanf($lens->timestamp, '%4d%2d%2d%2d%2d%d');
                    $date = date(
                        'Y-m-d H:i:s',
                        mktime($hour, $minute, $second, $month, $day, $year)
                    );
                }
                Lens::create(
                    [
                        'id' => $lens->id,
                        'make_id' => 1,
                        'name' => html_entity_decode($lens->name),
                        'factor' => $lens->factor,
                        'user_id' => $observer[0],
                        'active' => $lens->lensactive,
                        'observer' => $lens->observer,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]
                );
            }
        }
    }
}
