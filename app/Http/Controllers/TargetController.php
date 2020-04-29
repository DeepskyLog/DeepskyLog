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

use App\DataTables\TargetDataTable;
use App\Target;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * @param string $targetname The name of the target to show
     *
     * @return \Illuminate\Http\Response The reponse
     */
    public function show(string $targetname, TargetDataTable $dataTable)
    {
        $targetname = \App\TargetName::where('altname', $targetname)
            ->first();

        if ($targetname != null) {
            $target = $targetname->target()->get()[0];
            if ($target != null) {
                return $dataTable->with('target', $target)->render(
                    'layout.target.show',
                    compact('target', $target)
                );
            } else {
                abort(403, _i('The requested target does not exist.'));
            }
        } else {
            abort(403, _i('The requested target does not exist.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Target               $target
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
     * @param  \App\Target               $target
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Target $target)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Target $target
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
     * Returns the data from one catalog in JSON format.
     *
     * @param string $catalogname The name of the catalog
     *
     * @return string The JSON with all information on the objects
     */
    public function getCatalogData($catalogname)
    {
        $targets = DB::select(
            DB::raw(
                'SELECT targets.name, targets.con, targets.type,
                    target_names.objectname, target_names.altname
                    FROM targets JOIN target_names
                    ON targets.name = target_names.objectname WHERE catalog="'
                    .$catalogname.'" ORDER BY objectname'
            )
        );

        // Natural sort the array, so that the targets are sorted by name.
        usort(
            $targets,
            function ($a, $b) {
                return strnatcmp($a->altname, $b->altname);
            }
        );

        return $targets;
    }

    /**
     * Returns the constellation data from one catalog in JSON format.
     *
     * @param string $catalogname The name of the catalog
     *
     * @return string The JSON with all constellation information on the objects
     */
    public static function getConstellationInfo($catalogname)
    {
        $cons = DB::select(
            DB::raw(
                'SELECT constellations.name as con,
                    count((targets.con)) as count FROM targets
                    JOIN target_names ON targets.name = target_names.objectname
                    JOIN constellations ON targets.con = constellations.id
                    WHERE catalog="'
                    .$catalogname.'" GROUP BY targets.con'
            )
        );
        foreach ($cons as $con) {
            $con->con = _i($con->con);
        }

        return $cons;
    }

    /**
     * Returns the type data from one catalog in JSON format.
     *
     * @param string $catalogname The name of the catalog
     *
     * @return string The JSON with all type information of the objects
     */
    public static function getTypeInfo($catalogname)
    {
        $types = DB::select(
            DB::raw(
                'SELECT target_types.type, count((targets.type)) as count
                    FROM targets
                    JOIN target_names ON targets.name = target_names.objectname
                    JOIN target_types ON targets.type = target_types.id
                    WHERE catalog="'
                    .$catalogname.'" GROUP BY targets.type'
            )
        );

        foreach ($types as $type) {
            $type->type = _i($type->type);
        }

        return $types;
    }
}
