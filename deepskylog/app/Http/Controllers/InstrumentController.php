<?php

namespace App\Http\Controllers;

use App\Models\InstrumentsOld;

class InstrumentController extends Controller
{
    public function index()
    {
        return InstrumentsOld::all();
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
        return InstrumentsOld::where('observer', $user_id)->get();
    }
    //
    //
    //    public function show(Instrument $instrument)
    //    {
    //        return $instrument;
    //    }
    //
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
