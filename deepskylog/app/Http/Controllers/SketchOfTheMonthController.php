<?php

namespace App\Http\Controllers;

use App\Models\Constellation;
use App\Models\Observation;
use App\Models\Target;
use App\Models\SketchOfTheMonth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SketchOfTheMonthController extends Controller
{
    public function index()
    {
    }

    public function create()
    {
        return view('sketch-of-the-month.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'observation_id' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date'],
        ]);

        // Check if the observation is already a sketch of the month
        if (SketchOfTheMonth::where('observation_id', $request['observation_id'])->exists()) {
            throw ValidationException::withMessages(['observation_id' => 'This observation is already a sketch of the month']);
        }

        $observation = Observation::findOrFail((int) $request['observation_id']);
        if (!$observation->has_drawing) {
            throw ValidationException::withMessages(['observation_id' => 'This observation does not have a sketch']);
        }

        // Get the user
        $observerUsername = html_entity_decode((string) $observation->observer_username);
        $user = User::where('username', $observerUsername)->firstOrFail();
        $userId = $user->id;
        $observerSegment = $user->slug ?: $observerUsername;
        // Create text for Facebook, X and Instagram
        $carbon_date = Carbon::createFromFormat('Y-m-d', $request['date'])->subMonth();

        $monthName = $carbon_date->format('F');
        $year = $carbon_date->format('Y');
        $text = 'The #deepskylog sketch of ' . $monthName . ' ' . $year . ' is this sketch of ';

        if ($observation->target_type === 'comet') {
            $text .= 'comet ' . ($observation->target_name ?? 'unknown object');
        } else {
            $objectName = $observation->target_name;
            $object = $objectName ? Target::where('name', $objectName)->first() : null;
            $type = $object ? $object->long_type() : null;

            if ($type && strtolower($type) !== 'object') {
                $text .= 'the ' . $type . ' ';
            }

            $text .= ($objectName ?? 'unknown object');

            if ($object) {
                $constellation = Constellation::where('id', $object->con)->first();
                if ($constellation) {
                    $text .= ' in ' . $constellation->name;
                }
            }
        }
        $text .= ' by ' . $user->name . '.';
        $text .= '
Congratulations, ' . explode(' ', $user->name)[0] . '!';

        $text .= '
More information can be found here:

';
        $text .= route('observations.detail', [$observerSegment, $observation->observed_on->format('Y-m-d'), Str::slug((string) $observation->target_name, '-')]);

        $text .= '

#sketch #sketchofthemonth #deepsky #astronomy #deepskydrawing #sketches';

        $sketch = new SketchOfTheMonth;
        $sketch->observation_id = (int) $request['observation_id'];
        $sketch->date = $request->date;
        $sketch->user_id = $userId;

        $sketch->save();

        // Resolve the drawing URL (supports both legacy path and storage-backed unified observations)
        $drawingSrc = null;
        $drawingFilename = $observation->drawing_filename ?? null;
        if (!empty($drawingFilename)) {
            try {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($drawingFilename)) {
                    $drawingSrc = \Illuminate\Support\Facades\Storage::disk('public')->url($drawingFilename);
                } else {
                    $drawingSrc = '/images/' . ltrim($drawingFilename, '/');
                }
            } catch (\Throwable $_) {
                $drawingSrc = '/images/' . ltrim($drawingFilename, '/');
            }
        }
        if (empty($drawingSrc)) {
            $legacyId = (int) $observation->legacy_id;
            if ($observation->legacy_source === 'comet') {
                $drawingSrc = '/images/cometdrawings/' . $legacyId . '.jpg';
            } else {
                $drawingSrc = '/images/drawings/' . $legacyId . '.jpg';
            }
        }

        // Redirect to the sketch of the month page
        return view('/sketch-of-the-week-month/detail', [
            'share' => $text,
            'observation' => $observation,
            'observation_id' => (int) $request['observation_id'],
            'legacy_observation_id' => (int) $observation->legacy_id,
            'is_comet' => $observation->target_type === \App\Models\Observation::TYPE_COMET,
            'drawing_src' => $drawingSrc,
            'date' => $request->date,
            'week_month' => 'month',
        ]);
    }
    public function destroy(int $id)
    {
        abort_unless(auth()->check() && auth()->user()->isAdministrator(), 403);

        SketchOfTheMonth::findOrFail($id)->delete();

        return back()->with('status', 'Sketch of the month removed.');
    }
}
