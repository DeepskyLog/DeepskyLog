<?php
/**
 * Target Controller.
 *
 * PHP Version 7
 *
 * @category Targets
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Models\Target;
use Illuminate\Http\Request;
use App\DataTables\TargetDataTable;
use deepskylog\LaravelGettext\Facades\LaravelGettext;

/**
 * Target Controller.
 *
 * @category Targets
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class TargetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified target.
     *
     * @param string $name The name of the target to show
     *
     * @return \Illuminate\Http\Response The reponse
     */
    public function show(string $name, TargetDataTable $dataTable)
    {
        $targetname = \App\Models\TargetName::where('altname', $name)
            ->first();

        if ($targetname != null) {
            $target = \App\Models\Target::where('id', $targetname->target_id)->first();

            if ($target != null) {
                return $dataTable->with('target', $target)->render(
                    'layout.target.show',
                    compact('target', $target)
                );
            } else {
                abort(403, _i('The requested target does not exist.'));
            }
        } else {
            // Check all the translations
            $target = \App\Models\Target::where(
                'target_name->' . LaravelGettext::getLocaleLanguage(),
                $name
            )->first();

            if ($target != null) {
                return $dataTable->with('target', $target)->render(
                    'layout.target.show',
                    compact('target', $target)
                );
            } else {
                abort(403, _i('The requested target does not exist.'));
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Target         $target
     * @return \Illuminate\Http\Response
     */
    public function edit(Target $target)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Target         $target
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Target $target)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Model\Target $target
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Target $target)
    {
        //
    }

    /**
     * Shows the catalogs page.
     *
     * @return View The catalogs view
     */
    public function catalogs()
    {
        return view(
            'layout.target.catalogs'
        );
    }

    /**
     * Ajax request for the quick search selection.
     *
     * @param Request $request The request
     *
     * @return string the JSON response
     */
    public function dataAjax(Request $request)
    {
        dd('test');
        $search = trim($request->q);

        if ($search === '') {
            return \Response::json([]);
        }

        $data = [];

        if ($request->has('q')) {
            $data = DB::table('instruments')
                ->groupBy('name')
                ->select('id', 'name')
                ->where('name', 'LIKE', "%$search%")
                ->limit(20)
                ->get();
        }

        return response()->json($data);
    }
}
