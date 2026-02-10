<?php

namespace App\Http\Controllers;

use App\Models\CometObservationsOld;
use App\Models\ObservationLike;
use App\Models\ObservationsOld;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ResolvesLocalizedNames;

class PopularObservationController extends Controller
{
    use ResolvesLocalizedNames;
    public function index(Request $request)
    {
    $q = (string) $request->query('q', '');
    $locale = $request->query('locale') ?? app()->getLocale();
        // Group likes by type and id and count them, order by count desc
        $likes = ObservationLike::selectRaw('observation_type, observation_id, COUNT(*) as likes, MIN(id) as id')
            ->groupBy('observation_type', 'observation_id')
            ->orderByDesc('likes')
            ->with(['deepsky', 'comet'])
        ;

        // If a query is provided, try to resolve localized names (eg. 'Pluton' -> 'Pluto')
        if ($q !== '') {
            // Attempt exact locale-specific translation first, then fallback to any locale.
            $canonical = $this->resolveLocalizedToCanonical($q, $locale);

            if (empty($canonical)) {
                $canonical = $this->resolveLocalizedToCanonical($q, null);
            }

            if (! empty($canonical)) {
                // Filter to deepsky observations whose objectname matches any canonical name.
                $likes->where(function ($sub) use ($canonical) {
                    $sub->where('observation_type', 'comet'); // keep comets when searching (or adjust if desired)
                    // For deepsky types, we need to join to observations to match objectname.
                    // We'll limit using a WHERE EXISTS to avoid altering group-by behavior.
                    $sub->orWhereExists(function ($query) use ($canonical) {
                        $oldDb = config('database.connections.mysqlOld.database') ?? env('DB_DATABASE_OLD');
                        $obsTable = $oldDb ? "\"{$oldDb}\".\"observations\"" : 'observations';

                        $query->selectRaw('1')
                            ->from(DB::raw($obsTable.' as o'))
                            ->whereRaw('o.id = observation_id')
                            ->whereIn('o.objectname', $canonical);
                    });
                });
            }
        }

        // Paginate after applying possible filters
        $likes = $likes->paginate(30);

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
