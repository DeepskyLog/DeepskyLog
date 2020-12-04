<?php
/**
 * Eyepiece Controller.
 *
 * PHP Version 7
 *
 * @category Eyepieces
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Models\Eyepiece;
use Illuminate\Http\Request;
use App\Http\Requests\EyepieceRequest;

/**
 * Eyepiece Controller.
 *
 * @category Eyepieces
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class EyepieceController extends Controller
{
    /**
     * Make sure the eyepiece pages can only be seen if the user is authenticated
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
        return view('layout.eyepiece.view', ['user' => $user]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Eyepiece $eyepiece The eyepiece to fill out in the fields
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Eyepiece $eyepiece)
    {
        return view(
            'layout.eyepiece.create',
            ['eyepiece' => $eyepiece, 'update' => false]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EyepieceRequest $request The request with all information
     *
     * @return \Illuminate\Http\Response
     */
    public function store(EyepieceRequest $request)
    {
        $validated            = $request->validated();
        $validated['user_id'] = auth()->id();

        // Check if brand is already in the database.
        if (\App\Models\EyepieceBrand::where(
            'brand',
            $request->get('brand')
        )->get()->isEmpty()
        ) {
            // Add the new brand to the database
            \App\Models\EyepieceBrand::create(
                [
                    'brand' => $request->get('brand'),
                ]
            );
        }

        // Check if brand is already in the database.
        if (\App\Models\EyepieceType::where(
            'type',
            $request->get('type')
        )->where('brand', $request->get('brand'))->get()->isEmpty()
        ) {
            // Add the new brand to the database
            \App\Models\EyepieceType::create(
                [
                    'type'  => $request->get('type'),
                    'brand' => $request->get('brand'),
                ]
            );
        }

        $eyepiece = Eyepiece::create($validated);

        if ($request->picture != null) {
            // Add the picture
            Eyepiece::find($eyepiece->id)
                ->addMedia($request->picture->path())
                ->usingFileName($eyepiece->id . '.png')
                ->toMediaCollection('eyepiece');
        }

        laraflash(_i('Eyepiece %s created', $request->name))->success();

        // View the page with all eyepieces for the user
        return redirect(route('eyepiece.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Eyepiece $eyepiece The eyepiece to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Eyepiece $eyepiece)
    {
        $media = $this->getImage($eyepiece);

        return view(
            'layout.eyepiece.show',
            ['eyepiece' => $eyepiece, 'media' => $media]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Eyepiece $eyepiece The eyepiece to edit
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Eyepiece $eyepiece)
    {
        $this->authorize('update', $eyepiece);

        return view(
            'layout.eyepiece.create',
            ['eyepiece' => $eyepiece, 'update' => true]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param EyepieceRequest $request  The request with all information
     * @param Eyepiece        $eyepiece The eyepiece to adapt
     *
     * @return \Illuminate\Http\Response
     */
    public function update(EyepieceRequest $request, Eyepiece $eyepiece)
    {
        $this->authorize('update', $eyepiece);

        $request['user_id'] = $eyepiece->user_id;

        // If the factor is set, the name should also be set in the form.
        if ($request->has('focalLength')) {
            $validated            = $request->validated();
            $validated['user_id'] = auth()->id();

            // Check if brand is already in the database.
            if (\App\Models\EyepieceBrand::where(
                'brand',
                $request->get('brand')
            )->get()->isEmpty()
            ) {
                // Add the new brand to the database
                \App\Models\EyepieceBrand::create(
                    [
                        'brand' => $request->get('brand'),
                    ]
                );
            }

            // Check if type is already in the database.
            if (\App\Models\EyepieceType::where(
                'type',
                $request->get('type')
            )->where('brand', $request->get('brand'))->get()->isEmpty()
            ) {
                // Add the new type to the database
                \App\Models\EyepieceType::create(
                    [
                        'type'  => $request->get('type'),
                        'brand' => $request->get('brand'),
                    ]
                );
            }

            $eyepiece->update(['focalLength' => $request->get('focalLength')]);
            $eyepiece->update(['name' => $request->get('name')]);
            $eyepiece->update(['brand' => $request->get('brand')]);
            $eyepiece->update(['type' => $request->get('type')]);
            $eyepiece->update(['apparentFOV' => $request->get('apparentFOV')]);
            $eyepiece->update(['maxFocalLength' => $request->get('maxFocalLength')]);

            if ($request->picture != null) {
                if (Eyepiece::find($eyepiece->id)->getFirstMedia('eyepiece') != null
                ) {
                    // First remove the current image
                    Eyepiece::find($eyepiece->id)
                    ->getFirstMedia('eyepiece')
                    ->delete();
                }

                // Update the picture
                Eyepiece::find($eyepiece->id)
                    ->addMedia($request->picture->path())
                    ->usingFileName($eyepiece->id . '.png')
                    ->toMediaCollection('eyepiece');
            }

            laraflash(_i('Eyepiece %s updated', $eyepiece->name))->warning();
        }

        return redirect(route('eyepiece.index'));
    }

    /**
     * Returns the image of the eyepiece.
     *
     * @param Eyepiece $eyepiece The eyepiece
     *
     * @return MediaObject the image of the eyepiece
     */
    public function getImage(Eyepiece $eyepiece)
    {
        if (!$eyepiece->hasMedia('eyepiece')) {
            $eyepiece->addMediaFromUrl(asset('images/eyepiece.jpg'))
                ->usingFileName($eyepiece->id . '.png')
                ->toMediaCollection('eyepiece');
        }

        return $eyepiece->getFirstMedia('eyepiece');
    }

    /**
     * Remove the image of the eyepiece.
     *
     * @param int $id The id of the eyepiece
     *
     * @return None
     */
    public function deleteImage($id)
    {
        $this->authorize('update', Eyepiece::find($id));

        Eyepiece::find($id)
            ->getFirstMedia('eyepiece')
            ->delete();

        return '{}';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Eyepiece $eyepiece The eyepiece to remove
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Eyepiece $eyepiece)
    {
        $this->authorize('update', $eyepiece);

        if ($eyepiece->observations > 0) {
            laraflash(
                _i(
                    'Eyepiece %s has observations. Impossible to delete.',
                    $eyepiece->name
                )
            )->info();
        } else {
            if (Eyepiece::find($eyepiece->id)->hasMedia('eyepiece')) {
                Eyepiece::find($eyepiece->id)
                    ->getFirstMedia('eyepiece')
                    ->delete();
            }
            $eyepiece->delete();

            laraflash(_i('Eyepiece %s deleted', $eyepiece->name))->info();
        }

        return redirect()->back();
    }
}
