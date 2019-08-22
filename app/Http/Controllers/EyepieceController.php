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
        $request['observer_id'] = auth()->id();

        $validated = request()->validate(
            [
                'observer_id' => 'required',
                'name' => 'required|min:6',
                'focalLength' => 'required|numeric|gte:1|lte:99',
                'apparentFOV' => 'required|numeric|gte:20|lte:150',
                'maxFocalLength' => 'numeric|gte:1|lte:99',
            ]
        );

        Eyepiece::create($validated);

        laraflash(_i('Eyepiece %s created', $request->name))->success();

        // View the page with all eyepieces for the user
        return redirect('/eyepiece');
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

        $request['observer_id'] = $eyepiece->observer_id;

        // If the factor is set, the name should also be set in the form.
        if ($request->has('focalLength')) {
            request()->validate(
                [
                    'observer_id' => 'required',
                    'name' => 'required|min:6',
                    'focalLength' => 'required|numeric|gte:1|lte:99',
                    'apparentFOV' => 'required|numeric|gte:20|lte:150',
                    'maxFocalLength' => 'numeric|gte:1|lte:99',
                ]
            );

            $eyepiece->update(['focalLength' => $request->get('focalLength')]);
            $eyepiece->update(['name' => $request->get('name')]);
            $eyepiece->update(['apparentFOV' => $request->get('apparentFOV')]);
            $eyepiece->update(['maxFocalLength' => $request->get('maxFocalLength')]);

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
