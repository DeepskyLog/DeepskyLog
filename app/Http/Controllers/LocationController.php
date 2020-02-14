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

        $validated = $this->validateInput($request);

        $location = Location::create($validated);

        if ($request->get('timezone') == "undefined") {
            $location->update(
                ['timezone' =>
                    'UTC']
            );
        }
        if ($request->get('lm')) {
            $location->update(
                ['limitingMagnitude' =>
                    $request->get('lm') + Auth::user()->fstOffset]
            );
        }
        $location->update(['skyBackground' => $request->get('sb')]);
        $location->update(['bortle' => $request->get('bortle')]);

        if ($request->picture != null) {
            // Add the picture
            Location::find($location->id)
                ->addMedia($request->picture->path())
                ->usingFileName($location->id . '.png')
                ->toMediaCollection('location');
        }

        laraflash(_i('Location %s created', $request->name))->success();

        // View the page with all locations for the user
        return redirect('/location');
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
                'name' => 'required|min:4',
                'latitude' => 'required', 'longitude' => 'required',
                'country' => 'required',
                'elevation' => 'required', 'timezone' => 'required'
            ]
        );
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
     * Get the value from lightpollutionmap.info
     *
     * @param Request $request The request with the longitude and latitude
     *
     * @return The value from lightpollutionmap.info
     */
    public function lightpollutionmap(Request $request)
    {
        return file_get_contents(
            "https://www.lightpollutionmap.info/QueryRaster/" .
             "?ql=wa_2015&qt=point&qd=" . $request->longitude
            . "," . $request->latitude . "&key="
            . env('LIGHTPOLLUTION_KEY')
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
        if ($request->has('latitude')) {
            $this->validateInput($request);

            $location->update(['name' => $request->get('name')]);
            $location->update(['latitude' => $request->get('latitude')]);
            $location->update(['longitude' => $request->get('longitude')]);
            $location->update(['country' => $request->get('country')]);
            $location->update(['elevation' => $request->get('elevation')]);

            if ($request->get('timezone') == "undefined") {
                $location->update(
                    ['timezone' =>
                        'UTC']
                );
            } else {
                $location->update(['timezone' => $request->get('timezone')]);
            }
            if ($request->get('lm')) {
                $location->update(
                    ['limitingMagnitude' =>
                        $request->get('lm') + Auth::user()->fstOffset]
                );
            }
            $location->update(['skyBackground' => $request->get('sb')]);
            $location->update(['bortle' => $request->get('bortle')]);

            if ($request->picture != null) {
                if (Location::find($location->id)->getFirstMedia('location') != null
                ) {
                    // First remove the current image
                    Location::find($location->id)
                    ->getFirstMedia('location')
                    ->delete();
                }
                // Update the picture
                Location::find($location->id)
                    ->addMedia($request->picture->path())
                    ->usingFileName($location->id . '.png')
                    ->toMediaCollection('location');
            }

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
                if ($location->id == Auth::user()->stdlocation) {
                    laraflash(
                        _i(
                            'Impossible to deactivate the default location %s',
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
     * Returns the image of the location.
     *
     * @param int $id The id of the location
     *
     * @return MediaObject the image of the location
     */
    public function getImage($id)
    {
        if (Location::find($id)->hasMedia('location')) {
            return Location::find($id)
                ->getFirstMedia('location');
        } else {
            Location::find($id)
                ->addMediaFromUrl(asset('images/location.png'))
                ->usingFileName($id . '.png')
                ->toMediaCollection('location');

            return Location::find($id)
                ->getFirstMedia('location');
        }
    }

    /**
     * Remove the image of the location
     *
     * @param integer $id The id of the location
     *
     * @return None
     */
    public function deleteImage($id)
    {
        Location::find($id)
            ->getFirstMedia('location')
            ->delete();

        return '{}';
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
            if (Location::find($location->id)->hasMedia('location')) {
                Location::find($location->id)
                    ->getFirstMedia('location')
                    ->delete();
            }
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
