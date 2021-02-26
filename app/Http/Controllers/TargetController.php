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
            $targetQuery   = \App\Models\TargetName::where('altname', 'like', $request['quickpick']);
            $targetsToShow = $targetQuery->get();
            $allTargets    = \App\Models\Target::whereIn('targets.id', $targetsToShow->pluck('target_id'));

            $translated_target = \App\Models\Target::where(
                'target_name->' . LaravelGettext::getLocaleLanguage(),
                'like',
                $request->quickpick
            )->first();
            if ($translated_target) {
                $allTargets->orWhere('id', $translated_target->id);
            }
        } else {
            $requestArray = array_keys($request->toArray());

            // Check for all catalog entries in the request
            $resultCatalogs = array_filter($requestArray, function ($value) {
                return strpos($value, 'catalog') !== false;
            });
            // Check for all catalog entries in the request
            $resultNumbers = array_filter($requestArray, function ($value) {
                return strpos($value, 'numbers') !== false;
            });
            // Build the query
            $targetQuery = \App\Models\TargetName::query();
            if (count($resultCatalogs) > 0 || count($resultNumbers) > 0) {
                if (count($resultNumbers) > 0) {
                    if (count($resultNumbers) == 1) {
                        if (Str::contains($request->number, '%')) {
                            $targetQuery->where('catindex', 'like', $request->number1);
                        } else {
                            $targetQuery->where('catindex', $request->number1);
                        }
                    } elseif (count($resultNumbers) > 1) {
                        $targetQuery = $targetQuery->where(function ($query) use ($request, $resultNumbers) {
                            if (Str::contains($request->number1, '%')) {
                                $query->where('catindex', 'like', $request->number1);
                            } else {
                                $query->where('catindex', $request->number1);
                            }

                            foreach ($resultNumbers as $number) {
                                if ($number != array_values($resultNumbers)[0]) {
                                    $query->orWhere('constellation', $request[$number]);
                                }
                            }
                        });
                    }
                }
                if (count($resultCatalogs) > 0) {
                    if (count($resultCatalogs) == 1) {
                        $targetQuery->where('catalog', $request->catalog1);
                    } elseif (count($resultCatalogs) > 1) {
                        $targetQuery = $targetQuery->where(function ($query) use ($request, $resultCatalogs) {
                            $query->where('catalog', $request[array_values($resultCatalogs)[0]]);
                            foreach ($resultCatalogs as $res) {
                                if ($res != array_values($resultCatalogs)[0]) {
                                    $query->orWhere('catalog', $request[$res]);
                                }
                            }
                        });
                    }
                }

                $targetsToShow = $targetQuery->get();
                $allTargets    = \App\Models\Target::whereIn('targets.id', $targetsToShow->pluck('target_id'));

                if (count($resultNumbers) > 0) {
                    // Also search for translated strings
                    if (count($resultNumbers) == 1) {
                        $translated_target = \App\Models\Target::where(
                            'target_name->' . LaravelGettext::getLocaleLanguage(),
                            'like',
                            $request->number1
                        )->first();
                        if ($translated_target) {
                            $allTargets->orWhere('id', $translated_target->id);
                        }
                    } elseif (count($resultNumbers) > 1) {
                        $allTargets = $allTargets->where(function ($query) use ($request, $resultNumbers) {
                            $query->where('target_name->' . LaravelGettext::getLocaleLanguage(), 'like', $request[array_values($resultNumbers)[0]]);
                            foreach ($resultNumbers as $num) {
                                if ($num != array_values($resultNumbers)[0]) {
                                    $query->orWhere('target_name->' . LaravelGettext::getLocaleLanguage(), 'like', $request[$num]);
                                }
                            }
                        });
                    }
                }
            } else {
                $allTargets = \App\Models\Target::query();
            }

            // Check for all constellation entries in the request
            $results = array_filter($requestArray, function ($value) {
                return strpos($value, 'constellation') !== false;
            });

            if (count($results) == 1) {
                $allTargets = $allTargets->where('constellation', $request->constellation1);
            } elseif (count($results) > 1) {
                $allTargets = $allTargets->where(function ($query) use ($request, $results) {
                    $query->where('constellation', $request[array_values($results)[0]]);
                    foreach ($results as $con) {
                        if ($con != array_values($results)[0]) {
                            $query->orWhere('constellation', $request[$con]);
                        }
                    }
                });
            }

            // Check for all type entries in the request
            $results = array_filter($requestArray, function ($value) {
                return strpos($value, 'type') !== false;
            });

            if (count($results) == 1) {
                $allTargets = $allTargets->where('target_type', $request->type1);
            } elseif (count($results) > 1) {
                $allTargets = $allTargets->where(function ($query) use ($request, $results) {
                    $query->where('target_type', $request[array_values($results)[0]]);
                    foreach ($results as $typ) {
                        if ($typ != array_values($results)[0]) {
                            $query->orWhere('target_type', $request[$typ]);
                        }
                    }
                });
            }
        }

        $targetsToShow = $allTargets->get();

        if (count($targetsToShow) == 1) {
            // If there is only one target as a result of the query, show this target
            $target = $targetsToShow->first();

            return view(
                'layout.target.show',
                compact('target', $target)
            );
        } elseif (count($targetsToShow) > 1) {
            // If there is more than one target, show a list with the targets.
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
