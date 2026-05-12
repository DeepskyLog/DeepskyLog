<?php

namespace App\Http\Controllers;

use App\Models\Constellation;
use App\Models\Observation;
use App\Models\Target;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SketchOfTheWeekController extends Controller
{
    public function index()
    {
    }

    public function create()
    {
        return view('sketch-of-the-week.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'observation_id' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date'],
        ]);

        // Check if the observation is already a sketch of the week
        if (SketchOfTheWeek::where('observation_id', $request['observation_id'])->exists()) {
            throw ValidationException::withMessages(['observation_id' => 'This observation is already a sketch of the week']);
        }

        $observation = Observation::findOrFail((int) $request['observation_id']);
        if (!$observation->has_drawing) {
            throw ValidationException::withMessages(['observation_id' => 'This observation does not have a sketch']);
        }

        // Get the user id
        $observerUsername = html_entity_decode((string) $observation->observer_username);
        $userId = User::where('username', $observerUsername)->firstOrFail()->id;

        // Create text for Facebook, X
        $text = 'As new #deepskylog sketch of the week, we selected this sketch of ';

        if ($observation->target_type === 'comet') {
            $text .= 'comet ' . ($observation->target_name ?? 'unknown object');
        } else {
            $objectName = $observation->target_name;
            $object = $objectName ? Target::where('name', $objectName)->first() : null;
            $type = $object ? $object->long_type() : 'object';

            $text .= 'the ' . $type . ' ';

            $text .= ($objectName ?? 'unknown object');

            if ($object) {
                $constellation = Constellation::where('id', $object->con)->first();
                if ($constellation) {
                    $text .= ' in ' . $constellation->name;
                }
            }
        }
        $text .= ' by ' . User::where('username', $observerUsername)->firstOrFail()->name . '.';
        $text .= '
More information can be found here:

';
        if ($observation->legacy_source === 'comet') {
            $text .= 'https://www.deepskylog.org/index.php?indexAction=comets_detail_observation&observation=' . $observation->legacy_id;
        } else {
            $text .= 'https://www.deepskylog.org/index.php?indexAction=detail_observation&observation=' . $observation->legacy_id;
        }

        $text .= '

#sketch #sketchoftheweek #deepsky #astronomy #deepskydrawing #sketches';

        $sketch = new SketchOfTheWeek;
        $sketch->observation_id = (int) $request['observation_id'];
        $sketch->date = $request->date;
        $sketch->user_id = $userId;

        $sketch->save();

        // Redirect to the sketch of the week page
        return view('/sketch-of-the-week-month/detail', [
            'share' => $text,
            'observation_id' => (int) $request['observation_id'],
            'legacy_observation_id' => (int) $observation->legacy_id,
            'is_comet' => $observation->legacy_source === 'comet',
            'date' => $request->date,
            'week_month' => 'week',
        ]);
    }
}
