<?php

namespace App\Http\Controllers;

use App\Models\CometObservationsOld;
use App\Models\Constellation;
use App\Models\ObjectsOld;
use App\Models\ObservationsOld;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SketchOfTheWeekController extends Controller
{
    public function index() {}

    public function create()
    {
        return view('sketch-of-the-week.create');
    }

    public function store(Request $request)
    {
        // Check if the observation is already a sketch of the week
        if (SketchOfTheWeek::where('observation_id', $request['observation_id'])->exists()) {
            throw ValidationException::withMessages(['observation_id' => 'This observation is already a sketch of the week']);
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
        $userId = User::where('username', html_entity_decode($observation->observerid))->first()->id;

        // Create text for Facebook, X
        $text = 'As new #deepskylog sketch of the week, we selected this sketch of ';

        if ($request['observation_id'] > 0) {
            $object = ObjectsOld::where('name', $observation->objectname)->get()[0];
            $type = $object->long_type();

            $text .= 'the '.$type.' ';

            $text .= $observation->objectname;

            $constellation = Constellation::where('id', $object->con)->first()->name;

            $text .= ' in '.$constellation;
        } else {
            $text .= 'comet '.$observation->object->name;
        }
        $text .= ' by '.User::where('username', html_entity_decode($observation->observerid))->first()->name.'.';
        $text .= '
More information can be found here:

';
        if ($request['observation_id'] > 0) {
            $text .= 'https://www.deepskylog.org/index.php?indexAction=detail_observation&observation='.$request['observation_id'];
        } else {
            $text .= 'https://www.deepskylog.org/index.php?indexAction=comets_detail_observation&observation='.-$request['observation_id'];
        }

        $text .= '

#sketch #sketchoftheweek #deepsky #astronomy #deepskydrawing #sketches';

        $sketch = new SketchOfTheWeek;
        $sketch->observation_id = $request['observation_id'];
        $sketch->date = $request->date;
        $sketch->user_id = $userId;

        $sketch->save();

        // Redirect to the sketch of the week page
        return view('/sketch-of-the-week-month/detail', [
            'share' => $text, 'observation_id' => $request['observation_id'], 'date' => $request->date, 'week_month' => 'week',
        ]);
    }
}
