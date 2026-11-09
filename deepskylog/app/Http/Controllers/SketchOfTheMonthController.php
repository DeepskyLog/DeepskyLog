<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\SketchOfTheMonth;
use App\Models\User;
use App\Services\SketchShareText;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        // The sketch of the month is chosen for the 8th of a month without one, from the
        // sketches of the week of the month before
        $date = Carbon::parse($request['date'])->startOfDay();
        if (! SketchOfTheMonth::isFreeDate($date)) {
            throw ValidationException::withMessages(['date' => __('Choose the 8th of this month or a later one that has no sketch of the month yet.')]);
        }
        if (! SketchOfTheMonth::candidates($date)->contains('observation_id', (int) $request['observation_id'])) {
            throw ValidationException::withMessages(['observation_id' => __('The sketch of the month has to be one of the sketches of the week of the month before.')]);
        }

        $observation = Observation::findOrFail((int) $request['observation_id']);
        if (!$observation->has_drawing) {
            throw ValidationException::withMessages(['observation_id' => 'This observation does not have a sketch']);
        }

        // Get the user
        $observerUsername = html_entity_decode((string) $observation->observer_username);
        $user = User::where('username', $observerUsername)->firstOrFail();
        $userId = $user->id;

        // The post for Facebook, X, …
        $text = SketchShareText::forMonth($observation, $date, $user);

        $sketch = new SketchOfTheMonth;
        $sketch->observation_id = (int) $request['observation_id'];
        $sketch->date = $date->toDateString();
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
            'date' => $date->toDateString(),
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
