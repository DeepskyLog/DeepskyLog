<?php

namespace Database\Seeders;

use App\Models\Filter;
use App\Models\FiltersOld;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FilterSeeder extends Seeder
{
    public function run(): void
    {
        $filterData = FiltersOld::all();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('filters')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($filterData as $filter) {
            $observer = User::where('username', html_entity_decode($filter->observer))->pluck('id');
            if (count($observer) > 0) {
                if ($filter->timestamp == '') {
                    $date = date('Y-m-d H:i:s');
                } else {
                    [$year, $month, $day, $hour, $minute, $second]
                        = sscanf($filter->timestamp, '%4d%2d%2d%2d%2d%d');
                    $date = date(
                        'Y-m-d H:i:s',
                        mktime($hour, $minute, $second, $month, $day, $year)
                    );
                }
                $wratten = $filter->wratten == '' ? null : $filter->wratten;
                $schott = $filter->schott == '' ? null : $filter->schott;

                Filter::create(
                    [
                        'id' => $filter->id,
                        'make_id' => 1,
                        'name' => html_entity_decode($filter->name),
                        'type_id' => $filter->type + 1,
                        'color_id' => $filter->color + 1,
                        'wratten' => $wratten,
                        'schott' => $schott,
                        'user_id' => $observer[0],
                        'active' => $filter->filteractive,
                        'observer' => $filter->observer,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]
                );
            }
        }
    }
}
