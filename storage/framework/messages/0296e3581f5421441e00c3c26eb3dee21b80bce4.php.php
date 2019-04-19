<?php
/**
 * Lens Controller.
 *
 * PHP Version 7
 *
 * @category Lenses
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Lens;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Lens Controller.
 *
 * @category Lenses
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LensController extends Controller
{
    /**
     * Only make sure the lens pages can be seen if the user is authenticated
     * and verified.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified'])->except(['show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lenses = auth()->user()->lenses()->get();

        return $this->_indexView("user");
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin()
    {
        if (auth()->user()->isAdmin()) {
            //$lenses = Lens::all();

            return $this->_indexView("admin");
        } else {
            abort(401);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Array  $lenses An array with all lenses
     * @param String $user   user for a normal user, admin for an admin
     *
     * @return \Illuminate\Http\Response
     */
    private function _indexView($user)
    {
        return view('layout.lens.view')->with('user', $user);
    }

    /**
     * Returns all lenses (in JSON format) for server side processing of the tables.
     */
    public function all(Request $request)
    {
        // TODO: observer should be changed to observations.
        $columns = array(
            0 => 'name',
            1 => 'factor',
            2 => 'active',
            4 => 'observations',
        );

        $totalData = Lens::count();

        $totalFiltered = $totalData;


        // TODO: order by observations
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $lenses = Lens::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');

            // TODO: Search for observations
            $lenses = Lens::where('name', 'LIKE', "%{$search}%")
                ->orWhere('factor', 'LIKE', "%{$search}%")
                ->orWhere('observations', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = Lens::where('name', 'LIKE', "%{$search}%")
                ->orWhere('factor', 'LIKE', "%{$search}%")
                ->orWhere('observations', 'LIKE', "%{$search}%")
                ->count();
        }

        $data = array();
        if (!empty($lenses)) {
            foreach ($lenses as $lens) {
                //$show = route('posts.show',$post->id);
                //$edit =  route('posts.edit',$post->id);

                $nestedData['name'] = $lens->name;
                $nestedData['factor'] = round($lens->factor, 2);
                $nestedData['active'] = $lens->active;
                $nestedData['delete'] = "Delete";

                $observations = $lens->observations . ' '
                    . _n('observation', 'observations', $lens->observations);

                if ($lens->observations == 0) {
                    $observations = _i("No observations");
                }

                $nestedData['observations'] = $observations;

        //         //$nestedData['options'] = "&emsp;<a href='{$show}' title='SHOW' ><span class='glyphicon glyphicon-list'></span></a>
        //         //                  &emsp;<a href='{$edit}' title='EDIT' ><span class='glyphicon glyphicon-edit'></span></a>";
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        echo json_encode($json_data);
    }

    /**
     * Display a listing of the lenses in JSON format.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexJson()
    {
        return response(Lens::all()->jsonSerialize(), Response::HTTP_OK);
    }

    /**
     * Display a listing of the lenses in JSON format. Only return the
     * unique names.
     *
     * @return \Illuminate\Http\Response
     */
    public function uniqueIndexJson()
    {
        $lenses = Lens::all()->unique('name')->values();

        return response($lenses->jsonSerialize(), Response::HTTP_OK);
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
        $request['observer_id'] = auth()->id();

        $validated = request()->validate(
            [
                'observer_id' => 'required',
                'name' => ['required', 'min:6'],
                'factor' => ['required', 'numeric', 'min:0', 'max:10'],
            ]
        );

        Lens::create($validated);

        flash()->success(_i('Lens "%s" created', $request->name));

        // View the page with all lenses for the user
        return redirect('/lens');
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
        return view('layout.lens.show', ['lens' => $lens]);
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

        $request['observer_id'] = $lens->observer_id;

        // If the factor is set, the name should also be set in the form.
        if ($request->has('factor')) {
            $validated = request()->validate(
                [
                    'observer_id' => 'required',
                    'name' => ['required', 'min:6'],
                    'factor' => ['required', 'numeric', 'min:0', 'max:10'],
                ]
            );

            $lens->update(['factor' => $request->get('factor')]);
            $lens->update(['name' => $request->get('name')]);

            flash()->warning(_i('Lens "%s" updated', $lens->name));
        }

        return redirect('/lens');
    }

    /**
     * Toggle the active flag of the lens using Json.
     *
     * @param int $id The id of the lens.
     *
     * @return \Illuminate\Http\Response
     */
    public function toggleActivateJson($id)
    {
        $lens = Lens::findOrFail($id);

        $this->authorize('update', $lens);

        if ($lens->active) {
            $lens->inactive();
        } else {
            $lens->active();
        }
        return response($lens->jsonSerialize(), Response::HTTP_OK);
    }

    /**
     * Delete the lens using Json.
     *
     * @param int $id The id of the lens.
     */
    public function deleteJson($id)
    {
        $lens = Lens::findOrFail($id);

        $this->authorize('update', $lens);

        $this->destroy($lens);

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

        $lens->delete();

        return redirect('/lens');
    }
}
