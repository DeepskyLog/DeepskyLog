<?php
/**
 * Lens Controller.
 *
 * PHP Version 7
 *
 * @category Lenses
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Models\Lens;
use Illuminate\Http\Request;
use App\Http\Requests\LensRequest;

/**
 * Lens Controller.
 *
 * @category Lenses
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LensController extends Controller
{
    /**
     * Make sure the lens pages can only be seen if the user is authenticated
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
     * @param string        $user      user for a normal user, admin for an admin
     *
     * @return \Illuminate\Http\Response
     */
    private function _indexView($user)
    {
        return view('layout.lens.view');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Lens $lens The lens to fill out in the fields
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Lens $lens)
    {
        return view('layout.lens.create', ['lens' => $lens, 'update' => false]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\LensRequest $request The request with all information
     *
     * @return \Illuminate\Http\Response
     */
    public function store(LensRequest $request)
    {
        $validated            = $request->validated();
        $validated['user_id'] = auth()->id();

        $lens = Lens::create($validated);

        if ($request->picture != null) {
            // Add the picture
            Lens::find($lens->id)
                ->addMedia($request->picture->path())
                ->usingFileName($lens->id . '.png')
                ->toMediaCollection('lens');
        }

        laraflash(_i('Lens %s created', $request->name))->success();

        // View the page with all lenses for the user
        return redirect(route('lens.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Lens $lens The lens to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Lens $lens)
    {
        $media = $this->getImage($lens);

        return view('layout.lens.show', ['lens' => $lens, 'media' => $media]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Lens $lens The lens to edit
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Lens $lens)
    {
        $this->authorize('update', $lens);

        return view('layout.lens.create', ['lens' => $lens, 'update' => true]);
    }

    /**
     * Returns the image of the lens.
     *
     * @param Lens $lens The lens
     *
     * @return MediaObject the image of the lens
     */
    public function getImage(Lens $lens)
    {
        if (!$lens->hasMedia('lens')) {
            $lens->addMediaFromUrl(asset('images/lens.jpg'))
                ->usingFileName($lens->id . '.png')
                ->toMediaCollection('lens');
        }

        return $lens->getFirstMedia('lens');
    }

    /**
     * Remove the image of the lens.
     *
     * @param int $id The id of the lens
     *
     * @return None
     */
    public function deleteImage($id)
    {
        $this->authorize('update', Lens::find($id));

        Lens::find($id)
            ->getFirstMedia('lens')
            ->delete();

        return '{}';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Lens $lens The lens to remove
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lens $lens)
    {
        $this->authorize('update', $lens);

        if ($lens->observations > 0) {
            laraflash(_i('Lens %s has observations. Impossible to delete.', $lens->name))->info();
        } else {
            if (Lens::find($lens->id)->hasMedia('lens')) {
                Lens::find($lens->id)
                    ->getFirstMedia('lens')
                    ->delete();
            }
            $lens->delete();

            laraflash(_i('Lens %s deleted', $lens->name))->info();
        }

        return redirect()->back();
    }

    public function getlensesAjax(int $id = 0)
    {
        return response()->json(Lens::getLensOptionsChoices($id));
    }
}
