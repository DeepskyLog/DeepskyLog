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

use App\Target;
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
        $targetname = \App\TargetName::where('altname', $name)
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
            // Check all the translations
            $target = \App\Target::where(
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
}
