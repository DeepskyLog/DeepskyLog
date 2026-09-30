<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentMake;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        $user_id = User::where('slug', $user_slug)->firstOrFail()->id;
        $instrument = Instrument::where('slug', $instrument_slug)->where('user_id', $user_id)->firstOrFail();
        abort_unless($instrument->canBeEditedBy(Auth::user()), 403);

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
        $user_id = User::where('slug', $user_slug)->firstOrFail()->id;
        $instrument = Instrument::where('slug', $instrument_slug)->where('user_id', $user_id)->firstOrFail();

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
        $data = $request->validate([
            'id' => 'required|integer|not_in:1|exists:instrument_makes,id',
            'instrument_make' => ['required', 'string', 'max:255', Rule::unique('instrument_makes', 'name')->ignore($request->id)],
        ]);

        InstrumentMake::whereKey($data['id'])->update(['name' => trim($data['instrument_make'])]);

        return redirect()->route('instrument.indexAdmin');
    }

    public function destroyMake(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|not_in:1|exists:instrument_makes,id',
            'new_make' => 'required|integer|different:id|exists:instrument_makes,id',
        ]);

        DB::transaction(function () use ($data) {
            Instrument::where('make_id', $data['id'])->update(['make_id' => $data['new_make']]);
            InstrumentMake::whereKey($data['id'])->delete();
        });

        return redirect()->route('instrument.indexAdmin');
    }
}
