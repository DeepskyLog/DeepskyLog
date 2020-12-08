<?php
/**
 * Instrument Controller.
 *
 * PHP Version 7
 *
 * @category Instruments
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Models\Instrument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\InstrumentRequest;

/**
 * Instrument Controller.
 *
 * @category Instruments
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class InstrumentController extends Controller
{
    /**
     * Make sure the instrument pages can only be seen if the user is authenticated
     * and verified.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified'])->except(['show', 'getImage']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->_indexView('user');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin()
    {
        if (auth()->user()->isAdmin()) {
            return $this->_indexView('admin');
        } else {
            abort(401);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param string              $user      user for a normal user, admin for an admin
     *
     * @return \Illuminate\Http\Response
     */
    private function _indexView($user)
    {
        return view('layout.instrument.view');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Instrument $instrument The instrument to fill out in the fields
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Instrument $instrument)
    {
        return view(
            'layout.instrument.create',
            ['instrument' => $instrument, 'update' => false]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param InstrumentRequest $request The request with all information
     *
     * @return \Illuminate\Http\Response
     */
    public function store(InstrumentRequest $request)
    {
        $validated            = $request->validated();
        $validated['user_id'] = auth()->id();

        $instrument = Instrument::create($validated);

        if (Auth::user()->showInches) {
            $instrument->update(['diameter' => $request->get('diameter') * 25.4]);
        }

        if ($request->picture != null) {
            // Add the picture
            Instrument::find($instrument->id)
                ->addMedia($request->picture->path())
                ->usingFileName($instrument->id . '.png')
                ->toMediaCollection('instrument');
        }

        laraflash(_i('Instrument %s created', $request->name))->success();

        // View the page with all instruments for the user
        return redirect(route('instrument.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Instrument $instrument The instrument to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Instrument $instrument)
    {
        $media = $this->getImage($instrument);

        return view(
            'layout.instrument.show',
            ['instrument' => $instrument, 'media' => $media]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Instrument $instrument The instrument to edit
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Instrument $instrument)
    {
        $this->authorize('update', $instrument);

        return view(
            'layout.instrument.create',
            ['instrument' => $instrument, 'update' => true]
        );
    }

    /**
     * Returns the image of the instrument.
     *
     * @param Instrument $instrument The instrument
     *
     * @return MediaObject the image of the instrument
     */
    public function getImage(Instrument $instrument)
    {
        if (!$instrument->hasMedia('instrument')) {
            $instrument->addMediaFromUrl(asset('images/telescopeCartoon.png'))
                ->usingFileName($instrument->id . '.png')
                ->toMediaCollection('instrument');
        }

        return $instrument->getFirstMedia('instrument');
    }

    /**
     * Remove the image of the instrument.
     *
     * @param int $id The id of the instrument
     *
     * @return None
     */
    public function deleteImage($id)
    {
        $this->authorize('update', Instrument::find($id));

        Instrument::find($id)
            ->getFirstMedia('instrument')
            ->delete();

        return '{}';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Instrument $instrument The instrument to remove
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Instrument $instrument)
    {
        $this->authorize('update', $instrument);

        if ($instrument->observations > 0) {
            laraflash(
                _i(
                    'Instrument %s has observations. Impossible to delete.',
                    $instrument->name
                )
            )->info();
        } elseif ($instrument->id == Auth::user()->stdtelescope) {
            laraflash(
                _i(
                    'Impossible to delete the default instrument %s',
                    $instrument->name
                )
            )->danger();
        } else {
            if (Instrument::find($instrument->id)->hasMedia('instrument')) {
                Instrument::find($instrument->id)
                    ->getFirstMedia('instrument')
                    ->delete();
            }
            $instrument->delete();

            laraflash(_i('Instrument %s deleted', $instrument->name))->info();
        }

        return redirect()->back();
    }
}
