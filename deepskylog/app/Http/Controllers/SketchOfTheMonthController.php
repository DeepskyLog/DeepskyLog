<?php

namespace App\Http\Controllers;

use App\Models\CometObservationsOld;
use App\Models\ObservationsOld;
use App\Models\SketchOfTheMonth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SketchOfTheMonthController extends Controller
{
    public function index() {}

    public function create()
    {
        return view('sketch-of-the-month.create');
    }

    public function store(Request $request)
    {
        // Check if the observation is already a sketch of the month
        if (SketchOfTheMonth::where('observation_id', $request['observation_id'])->exists()) {
            throw ValidationException::withMessages(['observation_id' => 'This observation is already a sketch of the month']);
        }
        // Check if the observation has a sketch
        if ($request['observation_id'] > 0) {
            $observation = ObservationsOld::findorFail($request['observation_id']);
        } else {
            // Comets
            $observation = CometObservationsOld::findorFail(-$request['observation_id']);
        }
        if ($observation['hasDrawing'] == 0) {
            throw ValidationException::withMessages(['observation_id' => 'This observation does not have a sketch']);
        }

        // Get the user id
        $userId = User::where('username', $observation->observerid)->first()->id;

        $sketch = new SketchOfTheMonth;
        $sketch->observation_id = $request['observation_id'];
        $sketch->date = $request->date;
        $sketch->user_id = $userId;

        $sketch->save();

        // Redirect to the sketch of the month page
        return redirect()->route('sketch-of-the-month');
    }
}
