<?php
/**
 * Instrument Controller.
 *
 * PHP Version 7
 *
 * @category Instruments
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Instrument;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\DataTables\InstrumentDataTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Instrument Controller.
 *
 * @category Instruments
 * @package  DeepskyLog
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
        $this->middleware(['auth', 'verified'])->except(['show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param InstrumentDataTable $dataTable The instrument datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function index(InstrumentDataTable $dataTable)
    {
        return $this->_indexView($dataTable, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @param InstrumentDataTable $dataTable The instrument datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin(InstrumentDataTable $dataTable)
    {
        if (auth()->user()->isAdmin()) {
            return $this->_indexView($dataTable, 'admin');
        } else {
            abort(401);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param InstrumentDataTable $dataTable The instrument datatable
     * @param String              $user      user for a normal user, admin for an admin
     *
     * @return \Illuminate\Http\Response
     */
    private function _indexView($dataTable, $user)
    {
        return $dataTable->with('user', $user)->render('layout.instrument.view');
    }

    /**
     * Display a listing of the resource in JSON format.
     *
     * @param int $id The id of the instrument to return
     *
     * @return \Illuminate\Http\Response
     */
    public function getInstrumentJson(int $id)
    {
        $instrument = Instrument::findOrFail($id);

        return response($instrument->jsonSerialize(), Response::HTTP_OK);
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
     * @param \Illuminate\Http\Request $request The request with all information
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['user_id'] = auth()->id();

        $validated = $this->validateInput($request);

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
        return redirect('/instrument');
    }

    /**
     * Validate the values of the form.
     *
     * @param \Illuminate\Http\Request $request The request with all information
     *
     * @return \Illuminate\Http\Request The validated request
     */
    public function validateInput(Request $request)
    {
        return $request->validate(
            [
                'user_id' => 'required',
                'name' => 'required|min:6',
                'type' => 'required',
                'diameter' => 'required|numeric|gt:0',
                'fd' => 'gte:1|required_without_all:fixedMagnification',
                'fixedMagnification' => 'gte:0|required_without_all:fd'
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Instrument $instrument The instrument to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Instrument $instrument)
    {
        return view('layout.instrument.show', ['instrument' => $instrument]);
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
     * Update the specified resource in storage.
     *
     * @param Request    $request    The request with all information
     * @param Instrument $instrument The instrument to adapt
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Instrument $instrument)
    {
        $this->authorize('update', $instrument);

        $request['user_id'] = $instrument->user_id;

        // If the factor is set, the name should also be set in the form.
        if ($request->has('type')) {
            $this->validateInput($request);

            $instrument->update(['type' => $request->get('type')]);
            $instrument->update(['name' => $request->get('name')]);

            if (Auth::user()->showInches) {
                $instrument->update(
                    ['diameter' => $request->get('diameter') * 25.4]
                );
            } else {
                $instrument->update(['diameter' => $request->get('diameter')]);
            }
            $instrument->update(['fd' => $request->get('fd')]);
            $instrument->update(
                ['fixedMagnification' => $request->get('fixedMagnification')]
            );

            if ($request->picture != null) {
                if (Instrument::find($instrument->id)->getFirstMedia('instrument') != null
                ) {
                    // First remove the current image
                    Instrument::find($instrument->id)
                    ->getFirstMedia('instrument')
                    ->delete();
                }

                // Update the picture
                Instrument::find($instrument->id)
                    ->addMedia($request->picture->path())
                    ->usingFileName($instrument->id . '.png')
                    ->toMediaCollection('instrument');
            }

            laraflash(_i('Instrument %s updated', $instrument->name))->warning();
        } else {
            // This is only reached when clicking the active checkbox in the
            // instrument overview.
            if ($request->has('active')) {
                $instrument->active();
                laraflash(
                    _i('Instrument %s is active', $instrument->name)
                )->warning();
            } else {
                if ($instrument->id == Auth::user()->stdtelescope) {
                    laraflash(
                        _i(
                            'Impossible to deactivate the default instrument %s',
                            $instrument->name
                        )
                    )->danger();
                } else {
                    $instrument->inactive();
                    laraflash(
                        _i('Instrument %s is not longer active', $instrument->name)
                    )->warning();
                }
            }
        }

        return redirect('/instrument');
    }

    /**
     * Returns the image of the instrument.
     *
     * @param int $id The id of the instrument
     *
     * @return MediaObject the image of the instrument
     */
    public function getImage($id)
    {
        if (Instrument::find($id)->hasMedia('instrument')) {
            return Instrument::find($id)
                ->getFirstMedia('instrument');
        } else {
            Instrument::find($id)
                ->addMediaFromUrl(asset('images/telescopeCartoon.png'))
                ->usingFileName($id . '.png')
                ->toMediaCollection('instrument');

            return Instrument::find($id)
                ->getFirstMedia('instrument');
        }
    }

    /**
     * Remove the image of the instrument
     *
     * @param integer $id The id of the instrument
     *
     * @return None
     */
    public function deleteImage($id)
    {
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

    /**
     * Ajax request for select2.
     *
     * @param Request $request The request
     *
     * @return string the JSON response
     */
    public function dataAjax(Request $request)
    {
        $search = trim($request->q);

        if ($search === '') {
            return \Response::json([]);
        }

        $data = [];

        if ($request->has('q')) {
            $data = DB::table('instruments')
                ->groupBy('name')
                ->select('id', 'name')
                ->where('name', 'LIKE', "%$search%")
                ->limit(20)
                ->get();
        }

        return response()->json($data);
    }
}
