<?php
/**
 * Location Controller.
 *
 * PHP Version 7
 *
 * @category Locations
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DataTables\LocationDataTable;
use App\Http\Requests\LocationRequest;

/**
 * Location Controller.
 *
 * @category Locations
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
        $this->middleware(['auth', 'verified'])->except(['show', 'getImage']);
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
     * @param string            $user      user for a normal user, admin for an admin
     *
     * @return \Illuminate\Http\Response
     */
    private function _indexView($dataTable, $user)
    {
        return $dataTable->with('user', $user)->render('layout.location.view');
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
     * @param LocationRequest $request The request with all information
     *
     * @return \Illuminate\Http\Response
     */
    public function store(LocationRequest $request)
    {
        $validated            = $request->validated();
        $validated['user_id'] = auth()->id();

        $location = Location::create($validated);

        if ($request->get('timezone') == 'undefined') {
            $location->update(
                ['timezone' => 'UTC']
            );
        }
        if ($request->get('lm')) {
            $location->update(
                ['limitingMagnitude' => $request->get('lm') + Auth::user()->fstOffset]
            );
        }
        $location->update(['skyBackground' => $request->get('sb')]);
        $location->update(['bortle' => $request->get('bortle')]);

        if ($request->picture != null) {
            // Add the picture
            $location->addMedia($request->picture->path())
                ->usingFileName($location->id . '.png')
                ->toMediaCollection('location');
        }

        laraflash(_i('Location %s created', $request->name))->success();

        // View the page with all locations for the user
        return redirect(route('location.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Location $location The location to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Location $location)
    {
        $media = $this->getImage($location);

        return view(
            'layout.location.show',
            ['location' => $location, 'media' => $media]
        );
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
     * @param LocationRequest $request  The request with all information
     * @param Location        $location The location to adapt
     *
     * @return \Illuminate\Http\Response
     */
    public function update(LocationRequest $request, Location $location)
    {
        $this->authorize('update', $location);
        $request['user_id'] = $location->user_id;

        // If the factor is set, the name should also be set in the form.
        if ($request->has('latitude')) {
            $validated            = $request->validated();
            $validated['user_id'] = auth()->id();

            $location->update(['name' => $request->get('name')]);
            $location->update(['latitude' => $request->get('latitude')]);
            $location->update(['longitude' => $request->get('longitude')]);
            $location->update(['country' => $request->get('country')]);
            $location->update(['elevation' => $request->get('elevation')]);

            if ($request->get('timezone') == 'undefined') {
                $location->update(
                    ['timezone' => 'UTC']
                );
            } else {
                $location->update(['timezone' => $request->get('timezone')]);
            }
            if ($request->get('lm')) {
                $location->update(
                    ['limitingMagnitude' => $request->get('lm') + Auth::user()->fstOffset]
                );
            }
            $location->update(['skyBackground' => $request->get('sb')]);
            $location->update(['bortle' => $request->get('bortle')]);

            if ($request->picture != null) {
                if ($location->getFirstMedia('location') != null
                ) {
                    // First remove the current image
                    $location->getFirstMedia('location')
                        ->delete();
                }
                // Update the picture
                $location->addMedia($request->picture->path())
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

        return redirect(route('location.index'));
    }

    /**
     * Returns the image of the location.
     *
     * @param Location $location The location
     *
     * @return MediaObject the image of the location
     */
    public function getImage(Location $location)
    {
        if (!$location->hasMedia('location')) {
            $location->addMediaFromUrl(asset('images/location.png'))
                ->usingFileName($location->id . '.png')
                ->toMediaCollection('location');
        }

        return $location->getFirstMedia('location');
    }

    /**
     * Remove the image of the location.
     *
     * @param int $id The id of the location
     *
     * @return None
     */
    public function deleteImage($id)
    {
        $this->authorize('update', Location::find($id));

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
}