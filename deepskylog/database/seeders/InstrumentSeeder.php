<?php

namespace Database\Seeders;

use App\Models\Instrument;
use App\Models\InstrumentsOld;
use App\Models\InstrumentType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $instrumentData = InstrumentsOld::all();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('instruments')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($instrumentData as $instrument) {
            $observer = User::where('username', html_entity_decode($instrument->observer))->pluck('id');
            if (count($observer) > 0) {
                if ($instrument->timestamp == '') {
                    $date = date('Y-m-d H:i:s');
                } else {
                    [$year, $month, $day, $hour, $minute, $second]
                        = sscanf($instrument->timestamp, '%4d%2d%2d%2d%2d%d');
                    $date = date(
                        'Y-m-d H:i:s',
                        mktime($hour, $minute, $second, $month, $day, $year)
                    );
                }

                $type = $instrument->type;

                if ($type === -1) {
                    $type = 5;
                }

                $fm = $instrument->fixedMagnification;

                if ($instrument->fixedMagnification === 0) {
                    $fm = null;
                }

                $fd = $instrument->fd;

                if ($instrument->fd === 0.0) {
                    $fd = null;
                }

                $diameter = $instrument->diameter;
                if ($diameter < 1 || $diameter === null) {
                    $diameter = 1;
                }
                $focal_length = round($fd * $instrument->diameter);
                if ($focal_length === 0.0) {
                    $focal_length = 1;
                }
                // mount_type is set to Alt/Az.

                // Flip, flop image and obstruction come from the instrument type.
                $flip_image = InstrumentType::where('id', $type + 1)->pluck('flip_image')[0];
                $flop_image = InstrumentType::where('id', $type + 1)->pluck('flop_image')[0];

                $obstruction_perc = null;
                if ($instrument->type === 0 || $instrument->type === 1 || $instrument->type === 2 || $instrument->type === 4) {
                    $obstruction_perc = 0;
                }

                // Set obstruction to 0 for refractors, naked eye, ...
                Instrument::create(
                    [
                        'id' => $instrument->id,
                        'make_id' => 1,
                        'name' => html_entity_decode($instrument->name),
                        'aperture_mm' => $diameter,
                        'focal_length_mm' => $focal_length,
                        'instrument_type_id' => $type + 1,
                        'fixedMagnification' => $fm,
                        'mount_type_id' => 1,
                        'flip_image' => $flip_image,
                        'flop_image' => $flop_image,
                        'obstruction_perc' => $obstruction_perc,
                        'user_id' => $observer[0],
                        'active' => $instrument->instrumentactive,
                        'observer' => $instrument->observer,
                        'created_at' => $date,
                    ]
                );
            }
        }
    }
}
