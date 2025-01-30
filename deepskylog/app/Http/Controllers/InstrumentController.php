<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentMake;
use App\Models\User;
use Illuminate\Http\Request;

class InstrumentController extends Controller
{
    public function index()
    {
        return view('instrument.index');
    }

    public function create()
    {
        return view('instrument.create', ['update' => false]);
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

    public function edit(string $user_slug, string $instrument_slug)
    {
        $user_id = User::where('slug', $user_slug)->first()->id;
        $instrument = Instrument::where('slug', $instrument_slug)->where('user_id', $user_id)->first();

        return view(
            'instrument.create',
            ['instrument' => $instrument, 'update' => true]
        );
    }

    public function indexAdmin()
    {
        return view('instrument-admin.index');
    }

    public function editMake(InstrumentMake $make)
    {
        return view('instrument-admin.edit-make', ['make' => $make]);
    }

    public function storeMake(Request $request)
    {
        InstrumentMake::where('id', $request->id)->update(['name' => $request->instrument_make]);

        return redirect()->route('instrument.indexAdmin');
    }

    public function destroyMake(Request $request)
    {
        Instrument::where('make_id', $request->id)->update(['make_id' => $request->new_make]);
        InstrumentMake::where('id', $request->id)->delete();

        return redirect()->route('instrument.indexAdmin');
    }
}
