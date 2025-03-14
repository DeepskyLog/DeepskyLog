<?php

namespace Database\Seeders;

use App\Models\Eyepiece;
use App\Models\EyepiecesOld;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EyepieceSeeder extends Seeder
{
    public function run(): void
    {
        $eyepieceData = EyepiecesOld::all();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('eyepieces')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($eyepieceData as $eyepiece) {
            $observer = User::where('username', html_entity_decode($eyepiece->observer))->pluck('id');
            if (count($observer) > 0) {
                if ($eyepiece->timestamp == '') {
                    $date = date('Y-m-d H:i:s');
                } else {
                    [$year, $month, $day, $hour, $minute, $second]
                        = sscanf($eyepiece->timestamp, '%4d%2d%2d%2d%2d%d');
                    $date = date(
                        'Y-m-d H:i:s',
                        mktime($hour, $minute, $second, $month, $day, $year)
                    );
                }

                Eyepiece::create(
                    [
                        'id' => $eyepiece->id,
                        'make_id' => 1,
                        'type_id' => 1,
                        'name' => html_entity_decode($eyepiece->name),
                        'focal_length_mm' => $eyepiece->focalLength,
                        'apparentFOV' => $eyepiece->apparentFOV,
                        'max_focal_length_mm' => $eyepiece->maxFocalLength,
                        'field_stop_mm' => 0,
                        'user_id' => $observer[0],
                        'active' => $eyepiece->eyepieceactive,
                        'observer' => $eyepiece->observer,
                    ]
                );
            }
        }
    }
}
