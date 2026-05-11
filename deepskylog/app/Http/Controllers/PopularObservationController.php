<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\ObservationLike;
use Illuminate\Http\Request;
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

            if (!empty($canonical)) {
                // Filter to deepsky observations whose objectname matches any canonical name.
                $likes->where(function ($sub) use ($canonical) {
                    $sub->where('observation_type', 'comet'); // keep comets when searching (or adjust if desired)
                    // For deepsky types, we need to join to observations to match objectname.
                    // We'll limit using a WHERE EXISTS to avoid altering group-by behavior.
                    $sub->orWhereExists(function ($query) use ($canonical) {
                        $query->selectRaw('1')
                            ->from('observations as o')
                            ->whereRaw('o.id = observation_id')
                            ->whereIn('o.target_name', $canonical);
                    });
                });
            }
        }

        // Paginate after applying possible filters
        $likes = $likes->paginate(30);

        // Map each like group to an observation record
        $items = $likes->map(function ($row) {
            $obs = Observation::find($row->observation_id);
            if (!$obs) {
                return null;
            }

            return (object) [
                'type' => $obs->legacy_source ?? $row->observation_type,
                'id' => $obs->id,
                'date' => $obs->observed_on,
                'objectname' => $obs->target_name,
                'observerid' => $obs->observer_username,
                'likes' => $row->likes,
                'record' => $obs,
            ];
        })->filter();

        // Keep pagination but replace items with our mapped items
        $paginated = $likes->setCollection($items);

        return view('observations.popular', ['observations' => $paginated]);
    }
}
