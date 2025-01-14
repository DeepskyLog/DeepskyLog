<?php

namespace App\Http\Controllers;

use App\Models\Instrument;

class InstrumentController extends Controller
{
    public function index()
    {
        return Instrument::all();
    }
    //
    //    public function store(Request $request)
    //    {
    //        $data = $request->validate([
    //
    //        ]);
    //
    //        return Instrument::create($data);
    //    }

    public function show_from_user(string $user_id)
    {
        return Instrument::where('observer', $user_id)->get();
    }

    public function show(Instrument $instrument)
    {
        // TODO: Check if there is an image for this instrument
        $image = '/images/telescope.png';

        return view(
            'instrument.show',
            ['instrument' => $instrument, 'image' => $image]
        );
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
