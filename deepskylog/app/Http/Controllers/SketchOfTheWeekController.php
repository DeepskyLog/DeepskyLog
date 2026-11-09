<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use App\Services\SketchShareText;
use Carbon\Carbon;
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

        // A sketch of the week starts on a Sunday, today or later, that has none yet
        if (! SketchOfTheWeek::isFreeSunday(Carbon::parse($request['date'])->startOfDay())) {
            throw ValidationException::withMessages(['date' => __('Choose a Sunday, today or later, that has no sketch of the week yet.')]);
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
        $text = SketchShareText::forWeek($observation, $user);

        $sketch = new SketchOfTheWeek;
        $sketch->observation_id = (int) $request['observation_id'];
        $sketch->date = Carbon::parse($request['date'])->toDateString();
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

        // Redirect to the sketch of the week page
        return view('/sketch-of-the-week-month/detail', [
            'share' => $text,
            'observation' => $observation,
            'observation_id' => (int) $request['observation_id'],
            'legacy_observation_id' => (int) $observation->legacy_id,
            'is_comet' => $observation->target_type === \App\Models\Observation::TYPE_COMET,
            'drawing_src' => $drawingSrc,
            'date' => Carbon::parse($request['date'])->toDateString(),
            'week_month' => 'week',
        ]);
    }

    public function destroy(int $id)
    {
        abort_unless(auth()->check() && auth()->user()->isAdministrator(), 403);

        SketchOfTheWeek::findOrFail($id)->delete();

        return back()->with('status', 'Sketch of the week removed.');
    }
}
