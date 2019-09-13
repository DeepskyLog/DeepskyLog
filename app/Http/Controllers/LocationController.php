<?php
/**
 * Location Controller.
 *
 * PHP Version 7
 *
 * @category Locations
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\DataTables\LocationDataTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Location Controller.
 *
 * @category Locations
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LocationController extends Controller
{
    /**
     * Make sure the location pages can only be seen if the user is authenticated
     * and verified.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified'])->except(['show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param LocationDataTable $dataTable The location datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function index(LocationDataTable $dataTable)
    {
        return $this->_indexView($dataTable, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @param LocationDataTable $dataTable The location datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin(LocationDataTable $dataTable)
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
     * @param LocationDataTable $dataTable The location datatable
     * @param String            $user      user for a normal user, admin for an admin
     *
     * @return \Illuminate\Http\Response
     */
    private function _indexView($dataTable, $user)
    {
        return $dataTable->with('user', $user)->render('layout.location.view');
    }

    /**
     * Display a listing of the resource in JSON format.
     *
     * @param int $id The id of the location to return
     *
     * @return \Illuminate\Http\Response
     */
    public function getLocationJson(int $id)
    {
        $location = Location::findOrFail($id);

        return response($location->jsonSerialize(), Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Location $location The location to fill out in the fields
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Location $location)
    {
        return view(
            'layout.location.create',
            ['location' => $location, 'update' => false]
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

        $validated = request()->validate(
            [
                'user_id' => 'required',
                'name' => 'required|min:6',
                'type' => 'required',
                'diameter' => 'required|numeric|gt:0',
                'fd' => 'gte:1',
                'fixedMagnification' => 'gte:0'
            ]
        );

        $location = Location::create($validated);

        if (Auth::user()->showInches) {
            $location->update(['diameter' => $request->get('diameter') * 25.4]);
        }

        laraflash(_i('Location %s created', $request->name))->success();

        // View the page with all locations for the user
        return redirect('/location');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Location $location The location to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Location $location)
    {
        return view('layout.location.show', ['location' => $location]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Location $location The location to edit
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Location $location)
    {
        $this->authorize('update', $location);

        return view(
            'layout.location.create',
            ['location' => $location, 'update' => true]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request  $request  The request with all information
     * @param Location $location The location to adapt
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {
        $this->authorize('update', $location);

        $request['user_id'] = $location->user_id;

        // If the factor is set, the name should also be set in the form.
        if ($request->has('type')) {
            request()->validate(
                [
                    'user_id' => 'required',
                    'name' => 'required|min:6',
                    'type' => 'required',
                    'diameter' => 'required|numeric|gt:0',
                    'fd' => 'gte:1',
                    'fixedMagnification' => 'gte:0'
                ]
            );

            $location->update(['type' => $request->get('type')]);
            $location->update(['name' => $request->get('name')]);

            if (Auth::user()->showInches) {
                $location->update(
                    ['diameter' => $request->get('diameter') * 25.4]
                );
            } else {
                $location->update(['diameter' => $request->get('diameter')]);
            }
            $location->update(['fd' => $request->get('fd')]);
            $location->update(
                ['fixedMagnification' => $request->get('fixedMagnification')]
            );

            laraflash(_i('Location %s updated', $location->name))->warning();
        } else {
            // This is only reached when clicking the active checkbox in the
            // location overview.
            if ($request->has('active')) {
                $location->active();
                laraflash(
                    _i('Location %s is active', $location->name)
                )->warning();
            } else {
                if ($location->id == Auth::user()->stdtelescope) {
                    laraflash(
                        _i(
                            'Impossible to deactive the default location %s',
                            $location->name
                        )
                    )->danger();
                } else {
                    $location->inactive();
                    laraflash(
                        _i('Location %s is not longer active', $location->name)
                    )->warning();
                }
            }
        }

        return redirect('/location');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Location $location The location to remove
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        $this->authorize('update', $location);

        if ($location->observations > 0) {
            laraflash(
                _i(
                    'Location %s has observations. Impossible to delete.',
                    $location->name
                )
            )->info();
        } elseif ($location->id == Auth::user()->stdtelescope) {
            laraflash(
                _i(
                    'Impossible to delete the default location %s',
                    $location->name
                )
            )->danger();
        } else {
            $location->delete();

            laraflash(_i('Location %s deleted', $location->name))->info();
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
            $data = DB::table('locations')
                ->groupBy('name')
                ->select('id', 'name')
                ->where('name', 'LIKE', "%$search%")
                ->limit(20)
                ->get();
        }

        return response()->json($data);
    }
}
