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
                return strpos($value, 'number') !== false;
            });
            // Build the query
            $targetQuery = \App\Models\TargetName::query();

            if (count($resultCatalogs) > 0 || count($resultNumbers) > 0) {
                $cnt = 0;
                foreach ($resultCatalogs as $cat) {
                    $index  = str_replace('catalog', '', $cat);
                    $number = 'number' . $index;
                    $not    = 'notName' . $index;

                    if ($request[$not]) {
                        if ($request[$number]) {
                            $translated_target = \App\Models\Target::where(
                                'target_name->' . LaravelGettext::getLocaleLanguage(),
                                'not like',
                                ucwords($request[$number])
                            )->first();
                            if ($translated_target) {
                                $targetQuery->where('target_id', $translated_target->id);
                            } else {
                                $targetQuery->where('altname', 'not like', $request[$cat] . ' ' . $request[$number]);
                            }
                        } else {
                            $targetQuery->where('altname', 'not like', $request[$cat] . ' %');
                        }
                    } else {
                        if ($cnt == 0) {
                            if ($request[$number]) {
                                $translated_target = \App\Models\Target::where(
                                    'target_name->' . LaravelGettext::getLocaleLanguage(),
                                    'like',
                                    ucwords($request[$number])
                                )->first();
                                if ($translated_target) {
                                    $targetQuery->where('target_id', $translated_target->id);
                                } else {
                                    $targetQuery->where('altname', 'like', $request[$cat] . ' ' . $request[$number]);
                                }
                            } else {
                                $targetQuery->where('altname', 'like', $request[$cat] . ' %');
                            }
                            $cnt++;
                        } else {
                            if ($request[$number]) {
                                $targetQuery->orwhere('altname', 'like', $request[$cat] . ' ' . $request[$number]);

                                $translated_target = \App\Models\Target::where(
                                    'target_name->' . LaravelGettext::getLocaleLanguage(),
                                    'like',
                                    ucwords($request[$number])
                                )->first();
                                if ($translated_target) {
                                    $targetQuery->orwhere('target_id', $translated_target->id);
                                }
                            } else {
                                $targetQuery->orwhere('altname', 'like', $request[$cat] . ' %');
                            }
                        }
                    }
                }

                $targetsToShow = $targetQuery->get();
                $allTargets    = \App\Models\Target::whereIn('targets.id', $targetsToShow->pluck('target_id'));
            } else {
                $allTargets = \App\Models\Target::query();
            }

            // Check for all constellation entries in the request
            $results = array_filter($requestArray, function ($value) {
                return strpos($value, 'constellation') !== false;
            });

            if (count($results) == 1) {
                if ($request->notConstellation1) {
                    $allTargets = $allTargets->where('constellation', '!=', $request->constellation1);
                } else {
                    $allTargets = $allTargets->where('constellation', $request->constellation1);
                }
            } elseif (count($results) > 1) {
                $notResults = array_filter($requestArray, function ($value) {
                    return strpos($value, 'notConstellation') !== false;
                });
                $allTargets = $allTargets->where(function ($query) use ($request, $results, $notResults) {
                    if ($request[array_values($notResults)[0]]) {
                        $query->where('constellation', '!=', $request[array_values($results)[0]]);
                    } else {
                        $query->where('constellation', $request[array_values($results)[0]]);
                    }
                    $cnt = 0;
                    foreach ($results as $con) {
                        if ($con != array_values($results)[0]) {
                            if ($request[array_values($notResults)[$cnt]]) {
                                $query->where('constellation', '!=', $request[$con]);
                            } else {
                                $query->orWhere('constellation', $request[$con]);
                            }
                        }
                        $cnt++;
                    }
                });
            }

            // Check for all type entries in the request
            $results = array_filter($requestArray, function ($value) {
                return strpos($value, 'type') !== false;
            });

            if (count($results) == 1) {
                if ($request->notType1) {
                    $allTargets = $allTargets->where('target_type', '!=', $request->type1);
                } else {
                    $allTargets = $allTargets->where('target_type', $request->type1);
                }
            } elseif (count($results) > 1) {
                $notResults = array_filter($requestArray, function ($value) {
                    return strpos($value, 'notType') !== false;
                });
                $allTargets = $allTargets->where(function ($query) use ($request, $results, $notResults) {
                    if ($request[array_values($notResults)[0]]) {
                        $query->where('target_type', '!=', $request[array_values($results)[0]]);
                    } else {
                        $query->where('target_type', $request[array_values($results)[0]]);
                    }
                    $cnt = 0;
                    foreach ($results as $typ) {
                        if ($typ != array_values($results)[0]) {
                            if ($request[array_values($notResults)[$cnt]]) {
                                $query->where('target_type', '!=', $request[$typ]);
                            } else {
                                $query->orWhere('target_type', $request[$typ]);
                            }
                        }
                        $cnt++;
                    }
                });
            }

            // Check for all atlas entries in the request
            $results = array_filter($requestArray, function ($value) {
                return strpos($value, 'atlasPage') !== false;
            });

            if (count($results) == 1) {
                if ($request->notAtlas1) {
                    $allTargets = $allTargets->where($request->atlas1, '!=', $request->atlasPage1);
                } else {
                    $allTargets = $allTargets->where($request->atlas1, $request->atlasPage1);
                }
            } elseif (count($results) > 1) {
                $notResults = array_filter($requestArray, function ($value) {
                    return strpos($value, 'notAtlas') !== false;
                });
                $allTargets = $allTargets->where(function ($query) use ($request, $results, $notResults) {
                    if ($request[array_values($notResults)[0]]) {
                        $query->where($request->atlas1, '!=', $request[array_values($results)[0]]);
                    } else {
                        $query->where($request->atlas1, $request[array_values($results)[0]]);
                    }
                    $cnt = 0;
                    foreach ($results as $atl) {
                        $index  = str_replace('atlasPage', '', $atl);
                        $atlas  = 'atlas' . $index;
                        if ($atl != array_values($results)[0]) {
                            if ($request[array_values($notResults)[$cnt]]) {
                                $query->where($request[$atlas], '!=', $request[$atl]);
                            } else {
                                $query->orWhere($request[$atlas], $request[$atl]);
                            }
                        }
                        $cnt++;
                    }
                });
            }
            // Check for all declination entries in the request
            $results = array_filter($requestArray, function ($value) {
                return strpos($value, 'declinationDegrees') !== false;
            });

            if (count($results) == 1) {
                $decl = $request->declinationDegrees1 + $request->declinationMinutes1 / 60 + $request->declinationSeconds1 / 3600.0;
                if ($request->compDeclination1) {
                    $allTargets = $allTargets->where('decl', '<', $decl);
                } else {
                    $allTargets = $allTargets->where('decl', '>', $decl);
                }
            } elseif (count($results) > 1) {
                $compResults = array_filter($requestArray, function ($value) {
                    return strpos($value, 'compDeclination') !== false;
                });
                $minResults = array_filter($requestArray, function ($value) {
                    return strpos($value, 'declinationMinutes') !== false;
                });
                $secResults = array_filter($requestArray, function ($value) {
                    return strpos($value, 'declinationSeconds') !== false;
                });
                $allTargets = $allTargets->where(function ($query) use ($request, $results, $compResults, $minResults, $secResults) {
                    $decl = $request[array_values($results)[0]] + $request[array_values($minResults)[0]] / 60 + $request[array_values($secResults)[0]] / 3600.0;
                    if ($request[array_values($compResults)[0]]) {
                        $query->where('decl', '<', $decl);
                    } else {
                        $query->where('decl', '>', $decl);
                    }
                    $cnt = 0;
                    foreach ($results as $dec) {
                        $decl = $request[array_values($results)[$cnt]] + $request[array_values($minResults)[$cnt]] / 60 + $request[array_values($secResults)[$cnt]] / 3600.0;
                        if ($dec != array_values($results)[0]) {
                            if ($request[array_values($compResults)[$cnt]]) {
                                $query->where('decl', '<', $decl);
                            } else {
                                $query->where('decl', '>', $decl);
                            }
                        }
                        $cnt++;
                    }
                });
            }
            // Check for all ra entries in the request
            $results = array_filter($requestArray, function ($value) {
                return strpos($value, 'raHours') !== false;
            });

            if (count($results) == 1) {
                $ra = $request->raHours1 + $request->raMinutes1 / 60 + $request->raSeconds1 / 3600.0;
                if ($request->compRa1) {
                    $allTargets = $allTargets->where('ra', '<', $ra);
                } else {
                    $allTargets = $allTargets->where('ra', '>', $ra);
                }
            } elseif (count($results) > 1) {
                $compResults = array_filter($requestArray, function ($value) {
                    return strpos($value, 'compRa') !== false;
                });
                $minResults = array_filter($requestArray, function ($value) {
                    return strpos($value, 'raMinutes') !== false;
                });
                $secResults = array_filter($requestArray, function ($value) {
                    return strpos($value, 'raSeconds') !== false;
                });
                $allTargets = $allTargets->where(function ($query) use ($request, $results, $compResults, $minResults, $secResults) {
                    $ra = $request[array_values($results)[0]] + $request[array_values($minResults)[0]] / 60 + $request[array_values($secResults)[0]] / 3600.0;
                    if ($request[array_values($compResults)[0]]) {
                        $query->where('ra', '<', $ra);
                    } else {
                        $query->where('ra', '>', $ra);
                    }
                    $cnt = 0;
                    foreach ($results as $ra) {
                        $ra = $request[array_values($results)[$cnt]] + $request[array_values($minResults)[$cnt]] / 60 + $request[array_values($secResults)[$cnt]] / 3600.0;
                        if ($ra != array_values($results)[0]) {
                            if ($request[array_values($compResults)[$cnt]]) {
                                $query->where('ra', '<', $ra);
                            } else {
                                $query->where('ra', '>', $ra);
                            }
                        }
                        $cnt++;
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
