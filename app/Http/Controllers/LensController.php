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

use App\DataTables\LensDataTable;
use App\Lens;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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
     * @param LensDataTable $dataTable The lens datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function index(LensDataTable $dataTable)
    {
        return $this->_indexView($dataTable, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @param LensDataTable $dataTable The lens datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin(LensDataTable $dataTable)
    {
        if (auth()->user()->isAdmin()) {
            //$lenses = Lens::all();

            return $this->_indexView($dataTable, 'admin');
        } else {
            abort(401);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param LensDataTable $dataTable The lens datatable
     * @param string        $user      user for a normal user, admin for an admin
     *
     * @return \Illuminate\Http\Response
     */
    private function _indexView($dataTable, $user)
    {
        return $dataTable->with('user', $user)->render('layout.lens.view');
    }

    /**
     * Display a listing of the resource in JSON format.
     *
     * @param int $id The id of the lens to return
     *
     * @return \Illuminate\Http\Response
     */
    public function getLensJson(int $id)
    {
        $lens = Lens::findOrFail($id);

        return response($lens->jsonSerialize(), Response::HTTP_OK);
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
     * @param \Illuminate\Http\Request $request The request with all information
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['user_id'] = auth()->id();

        $validated = $this->validateInput($request);

        $lens = Lens::create($validated);

        if ($request->picture != null) {
            // Add the picture
            Lens::find($lens->id)
                ->addMedia($request->picture->path())
                ->usingFileName($lens->id.'.png')
                ->toMediaCollection('lens');
        }

        laraflash(_i('Lens %s created', $request->name))->success();

        // View the page with all lenses for the user
        return redirect('/lens');
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
                'name' => ['required', 'min:6'],
                'factor' => ['required', 'numeric', 'min:0', 'max:10'],
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Lens $lens The lens to show
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
     * Update the specified resource in storage.
     *
     * @param Request $request The request with all information
     * @param Lens    $lens    The lens to adapt
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Lens $lens)
    {
        $this->authorize('update', $lens);

        $request['user_id'] = $lens->user_id;

        // If the factor is set, the name should also be set in the form.
        if ($request->has('factor')) {
            $this->validateInput($request);

            $lens->update(['factor' => $request->get('factor')]);
            $lens->update(['name' => $request->get('name')]);

            if ($request->picture != null) {
                if (Lens::find($lens->id)->getFirstMedia('lens') != null
                ) {
                    // First remove the current image
                    Lens::find($lens->id)
                    ->getFirstMedia('lens')
                    ->delete();
                }

                // Update the picture
                Lens::find($lens->id)
                    ->addMedia($request->picture->path())
                    ->usingFileName($lens->id.'.png')
                    ->toMediaCollection('lens');
            }

            laraflash(_i('Lens %s updated', $lens->name))->warning();
        } else {
            // This is only reached when clicking the active checkbox in the
            // lens overview.
            if ($request->has('active')) {
                $lens->active();
                laraflash(_i('Lens %s is active', $lens->name))->warning();
            } else {
                $lens->inactive();
                laraflash(_i('Lens %s is not longer active', $lens->name))->warning();
            }
        }

        return redirect('/lens');
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
        if (! $lens->hasMedia('lens')) {
            $lens->addMediaFromUrl(asset('images/lens.jpg'))
                ->usingFileName($lens->id.'.png')
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
            $data = DB::table('lens')
                ->groupBy('name')
                ->select('id', 'name')
                ->where('name', 'LIKE', "%$search%")
                ->limit(20)
                ->get();
        }

        return response()->json($data);
    }
}
