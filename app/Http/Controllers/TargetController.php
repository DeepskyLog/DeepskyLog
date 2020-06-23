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
            $target = \App\Target::where('id', $targetname->target_id)->first();

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
        // \App\TargetName::with('target')->where('catalog', 'M')->get('target_id', 'target_name')->sortBy('altname', SORT_NATURAL);
        // TODO: Use livewire for this.
        return \App\TargetName::where('catalog', $catalogname)->get()
            ->sortBy('altname', SORT_NATURAL)->toJson();
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
                count((targets.constellation)) as count FROM targets
                JOIN target_names ON targets.id = target_names.target_id
                JOIN constellations ON targets.constellation = constellations.id
                WHERE catalog="' . $catalogname . '" GROUP BY targets.constellation'
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
                'SELECT target_types.type, count((targets.target_type)) as count
                    FROM targets
                    JOIN target_names ON targets.id = target_names.target_id
                    JOIN target_types ON targets.target_type = target_types.id
                    WHERE catalog="'
                    . $catalogname . '" GROUP BY targets.target_type'
            )
        );

        foreach ($types as $type) {
            $type->type = _i($type->type);
        }

        return $types;
    }
}
