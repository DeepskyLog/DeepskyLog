<?php

namespace App\Http\Controllers;

use App\Models\CometObservationsOld;
use App\Models\ObservationLike;
use App\Models\ObservationsOld;
use Illuminate\Http\Request;

class PopularObservationController extends Controller
{
    public function index(Request $request)
    {
        // Group likes by type and id and count them, order by count desc
        $likes = ObservationLike::selectRaw('observation_type, observation_id, COUNT(*) as likes, MIN(id) as id')
            ->groupBy('observation_type', 'observation_id')
            ->orderByDesc('likes')
            ->with(['deepsky', 'comet'])
            ->paginate(30);

        // Map each like group to an observation record
        $items = $likes->map(function ($row) {
            if ($row->observation_type === 'deepsky') {
                $obs = ObservationsOld::find($row->observation_id);
                if (! $obs) {
                    return null;
                }

                return (object) [
                    'type' => 'deepsky',
                    'id' => $obs->id,
                    'date' => $obs->date,
                    'objectname' => $obs->objectname,
                    'observerid' => $obs->observerid,
                    'likes' => $row->likes,
                    'record' => $obs,
                ];
            }

            if ($row->observation_type === 'comet') {
                $obs = CometObservationsOld::find($row->observation_id);
                if (! $obs) {
                    return null;
                }

                return (object) [
                    'type' => 'comet',
                    'id' => $obs->id,
                    'date' => $obs->date,
                    'objectname' => $obs->object->name ?? null,
                    'observerid' => $obs->observerid,
                    'likes' => $row->likes,
                    'record' => $obs,
                ];
            }

            return null;
        })->filter();

        // Keep pagination but replace items with our mapped items
        $paginated = $likes->setCollection($items);

        return view('observations.popular', ['observations' => $paginated]);
    }
}
