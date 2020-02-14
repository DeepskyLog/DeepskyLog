<?php
/**
 * Eyepiece Controller.
 *
 * PHP Version 7
 *
 * @category Eyepieces
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Eyepiece;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\DataTables\EyepieceDataTable;
use Illuminate\Support\Facades\DB;

/**
 * Eyepiece Controller.
 *
 * @category Eyepieces
 * @package  DeepskyLog
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
        $this->middleware(['auth', 'verified'])->except(['show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param EyepieceDataTable $dataTable The eyepiece datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function index(EyepieceDataTable $dataTable)
    {
        return $this->_indexView($dataTable, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @param EyepieceDataTable $dataTable The eyepiece datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin(EyepieceDataTable $dataTable)
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
     * @param EyepieceDataTable $dataTable The eyepiece datatable
     * @param String            $user      user for a normal user, admin for an admin
     *
     * @return \Illuminate\Http\Response
     */
    private function _indexView($dataTable, $user)
    {
        return $dataTable->with('user', $user)->render('layout.eyepiece.view');
    }

    /**
     * Display a listing of the resource in JSON format.
     *
     * @param int $id The id of the eyepiece to return
     *
     * @return \Illuminate\Http\Response
     */
    public function getEyepieceJson(int $id)
    {
        $eyepiece = Eyepiece::findOrFail($id);

        return response($eyepiece->jsonSerialize(), Response::HTTP_OK);
    }

    /**
     * Display a listing of the types of eyepieces of a given brand in JSON format.
     *
     * @param string $brand The brand of the eyepiece
     *
     * @return \Illuminate\Http\Response
     */
    public function getEyepieceTypeJson(string $brand)
    {
        $types = \App\EyepieceType::where('brand', $brand)
            ->pluck('type')->sort()->values();

        return response($types->jsonSerialize(), Response::HTTP_OK);
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
     * @param \Illuminate\Http\Request $request The request with all information
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['user_id'] = auth()->id();

        $validated = $this->validateInput($request);

        // Check if brand is already in the database.
        if (\App\EyepieceBrand::where(
            'brand', $request->get('brand')
        )->get()->isEmpty()
        ) {
            // Add the new brand to the database
            \App\EyepieceBrand::create(
                [
                    'brand' => $request->get('brand')
                ]
            );
        }

        // Check if brand is already in the database.
        if (\App\EyepieceType::where(
            'type', $request->get('type')
        )->where('brand', $request->get('brand'))->get()->isEmpty()
        ) {
            // Add the new brand to the database
            \App\EyepieceType::create(
                [
                    'type' => $request->get('type'),
                    'brand' => $request->get('brand')
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
        return redirect('/eyepiece');
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
                'brand' => 'required',
                'type' => 'required',
                'focalLength' => 'required|numeric|gte:1|lte:99',
                'apparentFOV' => 'required|numeric|gte:20|lte:150',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Eyepiece $eyepiece The eyepiece to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Eyepiece $eyepiece)
    {
        return view('layout.eyepiece.show', ['eyepiece' => $eyepiece]);
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
     * @param Request  $request  The request with all information
     * @param Eyepiece $eyepiece The eyepiece to adapt
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Eyepiece $eyepiece)
    {
        $this->authorize('update', $eyepiece);

        $request['user_id'] = $eyepiece->user_id;

        // If the factor is set, the name should also be set in the form.
        if ($request->has('focalLength')) {
            $this->validateInput($request);

            // Check if brand is already in the database.
            if (\App\EyepieceBrand::where(
                'brand', $request->get('brand')
            )->get()->isEmpty()
            ) {
                // Add the new brand to the database
                \App\EyepieceBrand::create(
                    [
                        'brand' => $request->get('brand')
                    ]
                );
            }

            // Check if brand is already in the database.
            if (\App\EyepieceType::where(
                'type', $request->get('type')
            )->where('brand', $request->get('brand'))->get()->isEmpty()
            ) {
                // Add the new brand to the database
                \App\EyepieceType::create(
                    [
                        'type' => $request->get('type'),
                        'brand' => $request->get('brand')
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
        } else {
            // This is only reached when clicking the active checkbox in the
            // eyepiece overview.
            if ($request->has('active')) {
                $eyepiece->active();
                laraflash(_i('Eyepiece %s is active', $eyepiece->name))->warning();
            } else {
                $eyepiece->inactive();
                laraflash(
                    _i('Eyepiece %s is not longer active', $eyepiece->name)
                )->warning();
            }
        }

        return redirect('/eyepiece');
    }

    /**
     * Returns the image of the eyepiece.
     *
     * @param int $id The id of the eyepiece
     *
     * @return MediaObject the image of the eyepiece
     */
    public function getImage($id)
    {
        if (Eyepiece::find($id)->hasMedia('eyepiece')) {
            return Eyepiece::find($id)
                ->getFirstMedia('eyepiece');
        } else {
            Eyepiece::find($id)
                ->addMediaFromUrl(asset('images/eyepiece.jpg'))
                ->usingFileName($id . '.png')
                ->toMediaCollection('eyepiece');

            return Eyepiece::find($id)
                ->getFirstMedia('eyepiece');
        }
    }

    /**
     * Remove the image of the eyepiece
     *
     * @param integer $id The id of the eyepiece
     *
     * @return None
     */
    public function deleteImage($id)
    {
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
            $data = DB::table('eyepieces')
                ->groupBy('name')
                ->select('id', 'name')
                ->where('name', 'LIKE', "%$search%")
                ->limit(20)
                ->get();
        }

        return response()->json($data);
    }
}
