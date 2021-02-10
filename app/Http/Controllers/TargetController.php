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
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Search page for the targets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('layout.target.search');
    }

    /**
     * Search page for the targets.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        if ($request['quickpick']) {
            $targetQuery = \App\Models\TargetName::where('altname', 'like', $request['quickpick']);

            $targetsToShow = $targetQuery->get();

            $translated_target = \App\Models\Target::where(
                'target_name->' . LaravelGettext::getLocaleLanguage(),
                'like',
                $request->quickpick
            )->first();
            if ($translated_target) {
                $toAdd = \App\Models\TargetName::where('target_id', $translated_target->id)->first();
                $targetsToShow->push($toAdd);
            }
        } else {
            // Build the query
            $targetQuery = \App\Models\TargetName::query();
            if ($request->number) {
                if (Str::contains($request->number, '%')) {
                    $targetQuery->where('catindex', 'like', $request->number);
                } else {
                    $targetQuery->where('catindex', $request->number);
                }
            }
            if ($request->catalog) {
                $targetQuery->where('catalog', $request->catalog);
            }

            $targetsToShow = $targetQuery->get();

            if ($request->number) {
                // Also search for translated strings
                $translated_target = \App\Models\Target::where(
                    'target_name->' . LaravelGettext::getLocaleLanguage(),
                    'like',
                    $request->number
                )->first();
                if ($translated_target) {
                    $toAdd = \App\Models\TargetName::where('target_id', $translated_target->id)->first();
                    $targetsToShow->push($toAdd);
                }
            }
        }

        if (count($targetsToShow) == 1) {
            // If there is only one target as a result of the query, show this target
            $target_id = $targetsToShow->first()->target_id;

            $target = \App\Models\Target::where('id', $target_id)->first();

            return view(
                'layout.target.show',
                compact('target', $target)
            );
        } elseif (count($targetsToShow) > 1) {
            // If there is more than one target, show a list with the targets.
            $allTargets     = \App\Models\Target::whereIn('targets.id', $targetsToShow->pluck('target_id'));
            $targetsToShow  = $allTargets->get();

            // TODO: Very slow!  Not because of the time it takes to calculate everything for the table.
            return view(
                'layout.target.view',
                compact('targetsToShow', $targetsToShow)
            );
        } else {
            // Show the search page if no target was found
            laraflash(_i('The requested target does not exist.'))->warning();

            return redirect(route('target.search'));
        }

        // TODO: We can use the same request for the quick pick
        // TODO: Adapt the search page to be able to add new search criteria (using a + in livewire).  Make it also possible to remove one of the criteria (using a -)
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
     * @param string $slug The slug of the target to show
     *
     * @return \Illuminate\Http\Response The reponse
     */
    public function show(string $slug)
    {
        $targetname = \App\Models\TargetName::where('slug', $slug)
            ->first();

        if ($targetname != null) {
            $target = \App\Models\Target::where('id', $targetname->target_id)->first();

            if ($target != null) {
                return view(
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
                $slug
            )->first();

            if ($target != null) {
                return view(
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
