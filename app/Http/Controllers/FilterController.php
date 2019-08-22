<?php
/**
 * Filter Controller.
 *
 * PHP Version 7
 *
 * @category Filters
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Filter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\DataTables\FilterDataTable;
use Illuminate\Support\Facades\DB;

/**
 * Filter Controller.
 *
 * @category Filters
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class FilterController extends Controller
{
    /**
     * Make sure the filter pages can only be seen if the user is authenticated
     * and verified.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified'])->except(['show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterDataTable $dataTable The filter datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function index(FilterDataTable $dataTable)
    {
        return $this->_indexView($dataTable, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterDataTable $dataTable The filter datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin(FilterDataTable $dataTable)
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
     * @param FilterDataTable $dataTable The filter datatable
     * @param String          $user      user for a normal user, admin for an admin
     *
     * @return \Illuminate\Http\Response
     */
    private function _indexView($dataTable, $user)
    {
        return $dataTable->with('user', $user)->render('layout.filter.view');
    }

    /**
     * Display a listing of the resource in JSON format.
     *
     * @param int $id The id of the filter to return
     *
     * @return \Illuminate\Http\Response
     */
    public function getFilterJson(int $id)
    {
        $filter = Filter::findOrFail($id);

        return response($filter->jsonSerialize(), Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Filter $filter The filter to fill out in the fields
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Filter $filter)
    {
        return view(
            'layout.filter.create', ['filter' => $filter, 'update' => false]
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
                'name' => ['required', 'min:6'],
                'type' => ['required'],
                'color' => [], 'wratten' => ['max:5'],
                'schott' => []
            ]
        );

        Filter::create($validated);

        laraflash(_i('Filter %s created', $request->name))->success();

        // View the page with all filters for the user
        return redirect('/filter');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Filter $filter The filter to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Filter $filter)
    {
        return view('layout.filter.show', ['filter' => $filter]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Filter $filter The filter to edit
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Filter $filter)
    {
        $this->authorize('update', $filter);

        return view('layout.filter.create', ['filter' => $filter, 'update' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request The request with all information
     * @param Filter  $filter  The filter to adapt
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Filter $filter)
    {
        $this->authorize('update', $filter);

        $request['observer_id'] = $filter->observer_id;

        // If the factor is set, the name should also be set in the form.
        if ($request->has('type')) {
            request()->validate(
                [
                    'observer_id' => 'required',
                    'name' => ['required', 'min:6'],
                    'type' => ['required'],
                    'color', 'wratten', 'schott'
                ]
            );

            $filter->update(['type' => $request->get('type')]);
            $filter->update(['name' => $request->get('name')]);
            $filter->update(['color' => $request->get('color')]);
            $filter->update(['wratten' => $request->get('wratten')]);
            $filter->update(['schott' => $request->get('schott')]);

            laraflash(_i('Filter %s updated', $filter->name))->warning();
        } else {
            // This is only reached when clicking the active checkbox in the
            // filter overview.
            if ($request->has('active')) {
                $filter->active();
                laraflash(_i('Filter %s is active', $filter->name))->warning();
            } else {
                $filter->inactive();
                laraflash(
                    _i('Filter %s is not longer active', $filter->name)
                )->warning();
            }
        }

        return redirect('/filter');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Filter $filter The filter to remove
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Filter $filter)
    {
        $this->authorize('update', $filter);

        if ($filter->observations > 0) {
            laraflash(
                _i('Filter %s has observations. Impossible to delete.', $filter->name)
            )->info();
        } else {
            $filter->delete();

            laraflash(_i('Filter %s deleted', $filter->name))->info();
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
            $data = DB::table('filters')
                ->groupBy('name')
                ->select('id', 'name')
                ->where('name', 'LIKE', "%$search%")
                ->limit(20)
                ->get();
        }

        return response()->json($data);
    }
}
