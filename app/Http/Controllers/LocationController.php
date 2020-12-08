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
     * @param string            $user      user for a normal user, admin for an admin
     *
     * @return \Illuminate\Http\Response
     */
    private function _indexView($user)
    {
        return view('layout.location.view');
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
