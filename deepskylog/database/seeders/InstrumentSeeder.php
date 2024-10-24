<?php

namespace Database\Seeders;

use App\Models\Instrument;
use App\Models\InstrumentsOld;
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
            $observer = User::where('username', $instrument->observer)->pluck('id');
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

                Instrument::create(
                    [
                        'id' => $instrument->id,
                        'name' => html_entity_decode($instrument->name),
                        'diameter' => $instrument->diameter,
                        'fd' => $fd,
                        'type' => $type + 1,
                        'fixedMagnification' => $fm,
                        'user_id' => $observer[0],
                        'active' => $instrument->instrumentactive,
                        'created_at' => $date,
                    ]
                );
            }
        }
    }
}
