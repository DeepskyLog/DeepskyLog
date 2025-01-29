<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\User;

class InstrumentController extends Controller
{
    public function index()
    {
        return view('instrument.index');
    }

    public function create()
    {
        return view('instrument.create');
    }

    public function show_from_user(string $user_id)
    {
        return Instrument::where('observer', $user_id)->get();
    }

    public function show(string $user_slug, string $instrument_slug)
    {
        $user_id = User::where('slug', $user_slug)->first()->id;
        $instrument = Instrument::where('slug', $instrument_slug)->where('user_id', $user_id)->first();

        // Check if there is an image for this instrument
        if ($instrument->picture != null) {
            $image = '/storage/'.asset($instrument->picture);
        } else {
            $image = '/images/telescope.png';
        }

        return view(
            'instrument.show',
            ['instrument' => $instrument, 'image' => $image]
        );
    }

    public function edit(Instrument $instrument)
    {
        return view('instrument.create', ['instrument' => $instrument]);
    }
    //    public function update(Request $request, Instrument $instrument)
    //    {
    //        $data = $request->validate([
    //
    //        ]);
    //
    //        $instrument->update($data);
    //
    //        return $instrument;
    //    }
    //
    //    public function destroy(Instrument $instrument)
    //    {
    //        $instrument->delete();
    //
    //        return response()->json();
    //    }
}
