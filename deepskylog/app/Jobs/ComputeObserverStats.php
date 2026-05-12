<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Observation;
use App\Models\User;

use Illuminate\Support\Arr;

class ComputeObserverStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function handle()
    {
        $cacheKey = 'observer_stats:' . $this->userId;
        $inProgressKey = 'observer_stats_in_progress:' . $this->userId;

        try {
            $user = User::find($this->userId);
            if (!$user) {
                Cache::forget($inProgressKey);
                return;
            }

            // Double-star counts (include legacy + unified)
            $doubleTotalObservations = (int) DB::table('observations')
                ->where('target_table', 'objects')
                ->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->count();

            $doubleYourObservations = (int) DB::table('observations')
                ->where('target_table', 'objects')
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->count();

            $doubleTotalObservationsLastYear = (int) DB::table('observations')
                ->where('target_table', 'objects')
                ->where('observed_on', '>=', now()->subYear())
                ->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->count();

            $doubleYourObservationsLastYear = (int) DB::table('observations')
                ->where('target_table', 'objects')
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->count();

            $doubleTotalDrawings = (int) DB::table('observations')
                ->where('target_table', 'objects')
                ->where('has_drawing', 1)
                ->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->count();

            $doubleYourDrawings = (int) DB::table('observations')
                ->where('target_table', 'objects')
                ->where('has_drawing', 1)
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->count();

            $doubleTotalDrawingsLastYear = (int) DB::table('observations')
                ->where('target_table', 'objects')
                ->where('has_drawing', 1)
                ->where('observed_on', '>=', now()->subYear())
                ->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->count();

            $doubleYourDrawingsLastYear = (int) DB::table('observations')
                ->where('target_table', 'objects')
                ->where('has_drawing', 1)
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->count();

            $doubleTotalUnique = (int) DB::table('observations')
                ->where('target_table', 'objects')
                ->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->distinct()->count('target_name');

            $doubleYourUnique = (int) DB::table('observations')
                ->where('target_table', 'objects')
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->distinct()->count('target_name');

            // Compute deepsky metrics (include both unified and legacy then subtract double-stars)
            $unifiedObjectsCountForUser = (int) Observation::where('target_table', 'objects')
                ->whereNull('legacy_source')
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $unifiedObjectsCount = (int) Observation::where('target_table', 'objects')->whereNull('legacy_source')->count();

            $legacyDeepskyYour = (int) Observation::where('legacy_source', 'deepsky')
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $legacyDeepskyTotal = (int) Observation::where('legacy_source', 'deepsky')->count();

            $deepskyYourObservations = max(0, ($unifiedObjectsCountForUser + $legacyDeepskyYour) - $doubleYourObservations);
            $deepskyTotalObservations = max(0, ($unifiedObjectsCount + $legacyDeepskyTotal) - $doubleTotalObservations);

            $unifiedYourObsLastYear = (int) Observation::where('target_table', 'objects')
                ->whereNull('legacy_source')
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $legacyYourObsLastYear = (int) Observation::where('legacy_source', 'deepsky')
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $deepskyYourObservationsLastYear = max(0, $unifiedYourObsLastYear + $legacyYourObsLastYear - $doubleYourObservationsLastYear);

            $unifiedTotalObsLastYear = (int) Observation::where('target_table', 'objects')->whereNull('legacy_source')->where('observed_on', '>=', now()->subYear())->count();
            $legacyTotalObsLastYear = (int) Observation::where('legacy_source', 'deepsky')->where('observed_on', '>=', now()->subYear())->count();

            $deepskyTotalObservationsLastYear = max(0, $unifiedTotalObsLastYear + $legacyTotalObsLastYear - $doubleTotalObservationsLastYear);

            $unifiedUserDeepskyDraws = (int) Observation::where('target_table', 'objects')->whereNull('legacy_source')->where('has_drawing', 1)->where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            })->count();

            $legacyUserDeepskyDraws = (int) Observation::where('legacy_source', 'deepsky')->where('has_drawing', 1)->where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            })->count();

            $deepskyYourDrawings = max(0, ($unifiedUserDeepskyDraws + $legacyUserDeepskyDraws) - $doubleYourDrawings);
            $deepskyTotalDrawings = max(0, ((int) Observation::where('target_table', 'objects')->whereNull('legacy_source')->where('has_drawing', 1)->count()) + (int) Observation::where('legacy_source', 'deepsky')->where('has_drawing', 1)->count() - $doubleTotalDrawings);

            $unifiedUserDeepskyDrawsLastYear = (int) Observation::where('target_table', 'objects')->whereNull('legacy_source')->where('has_drawing', 1)->where('observed_on', '>=', now()->subYear())->where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            })->count();

            $legacyUserDeepskyDrawsLastYear = (int) Observation::where('legacy_source', 'deepsky')->where('has_drawing', 1)->where('observed_on', '>=', now()->subYear())->where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            })->count();

            $deepskyYourDrawingsLastYear = max(0, ($unifiedUserDeepskyDrawsLastYear + $legacyUserDeepskyDrawsLastYear) - $doubleYourDrawingsLastYear);
            $deepskyTotalDrawingsLastYear = max(0, ((int) Observation::where('target_table', 'objects')->whereNull('legacy_source')->where('has_drawing', 1)->where('observed_on', '>=', now()->subYear())->count()) + (int) Observation::where('legacy_source', 'deepsky')->where('has_drawing', 1)->where('observed_on', '>=', now()->subYear())->count() - $doubleTotalDrawingsLastYear);

            $unifiedYourUnique = (int) Observation::whereNull('legacy_source')->where('target_table', 'objects')->where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            })->distinct()->count('target_name');

            $legacyYourUnique = (int) Observation::where('legacy_source', 'deepsky')->where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            })->distinct()->count('target_name');

            $deepskyYourUnique = max(0, $unifiedYourUnique + $legacyYourUnique - $doubleYourUnique);
            $unifiedTotalUnique = (int) Observation::whereNull('legacy_source')->where('target_table', 'objects')->distinct()->count('target_name');
            $legacyTotalUnique = (int) Observation::where('legacy_source', 'deepsky')->distinct()->count('target_name');
            $deepskyTotalUnique = max(0, $unifiedTotalUnique + $legacyTotalUnique - $doubleTotalUnique);

            // Unified (modern) totals across all target tables
            $unifiedYourObservations = (int) Observation::whereNull('legacy_source')
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $unifiedTotalObservations = (int) Observation::whereNull('legacy_source')->count();

            $unifiedYourObservationsLastYear = (int) Observation::whereNull('legacy_source')
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $unifiedTotalObservationsLastYear = (int) Observation::whereNull('legacy_source')
                ->where('observed_on', '>=', now()->subYear())
                ->count();

            $unifiedYourDrawings = (int) Observation::whereNull('legacy_source')
                ->where('has_drawing', 1)
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $unifiedTotalDrawings = (int) Observation::whereNull('legacy_source')->where('has_drawing', 1)->count();

            $unifiedYourDrawingsLastYear = (int) Observation::whereNull('legacy_source')
                ->where('has_drawing', 1)
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $unifiedTotalDrawingsLastYear = (int) Observation::whereNull('legacy_source')
                ->where('has_drawing', 1)
                ->where('observed_on', '>=', now()->subYear())
                ->count();

            $unifiedYourUnique = (int) Observation::whereNull('legacy_source')
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->distinct()->count('target_name');

            $unifiedTotalUnique = (int) Observation::whereNull('legacy_source')->distinct()->count('target_name');

            // Global totals (all observations)
            $allYourObservations = (int) Observation::where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            })->count();

            $allTotalObservations = (int) Observation::count();

            $allYourObservationsLastYear = (int) Observation::where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            })->where('observed_on', '>=', now()->subYear())->count();

            $allTotalObservationsLastYear = (int) Observation::where('observed_on', '>=', now()->subYear())->count();

            $allYourDrawings = (int) Observation::where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            })->where('has_drawing', 1)->count();

            $allTotalDrawings = (int) Observation::where('has_drawing', 1)->count();

            $allYourDrawingsLastYear = (int) Observation::where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            })->where('has_drawing', 1)->where('observed_on', '>=', now()->subYear())->count();

            $allTotalDrawingsLastYear = (int) Observation::where('has_drawing', 1)->where('observed_on', '>=', now()->subYear())->count();

            $allYourUnique = (int) Observation::where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            })->distinct()->count('target_name');

            $allTotalUnique = (int) Observation::distinct()->count('target_name');

            // Comet totals
            $cometYourObservations = (int) Observation::where('legacy_source', 'comet')
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $cometTotalObservations = (int) Observation::where('legacy_source', 'comet')->count();

            $cometYourObservationsLastYear = (int) Observation::where('legacy_source', 'comet')
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $cometTotalObservationsLastYear = (int) Observation::where('legacy_source', 'comet')->where('observed_on', '>=', now()->subYear())->count();

            $cometYourDrawings = (int) Observation::where('legacy_source', 'comet')
                ->where('has_drawing', 1)
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $cometTotalDrawings = (int) Observation::where('legacy_source', 'comet')->where('has_drawing', 1)->count();

            $cometYourDrawingsLastYear = (int) Observation::where('legacy_source', 'comet')
                ->where('has_drawing', 1)
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->count();

            $cometTotalDrawingsLastYear = (int) Observation::where('legacy_source', 'comet')->where('has_drawing', 1)->where('observed_on', '>=', now()->subYear())->count();

            $cometYourUnique = (int) Observation::where('legacy_source', 'comet')
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                })->distinct()->count('target_name');

            $cometTotalUnique = (int) Observation::where('legacy_source', 'comet')->distinct()->count('target_name');

            // Planets / Sun / Moons (full computation)
            $planetsYourObservations = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $planetsTotalObservations = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->count();

            $planetsYourObservationsLastYear = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $planetsTotalObservationsLastYear = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->where('observed_on', '>=', now()->subYear())
                ->count();

            $planetsYourDrawings = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->where('has_drawing', 1)
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $planetsTotalDrawings = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->where('has_drawing', 1)
                ->count();

            $planetsYourDrawingsLastYear = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->where('has_drawing', 1)
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $planetsTotalDrawingsLastYear = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->where('has_drawing', 1)
                ->where('observed_on', '>=', now()->subYear())
                ->count();

            $planetsYourUnique = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->distinct()->count('target_name');

            $planetsTotalUnique = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->distinct()->count('target_name');

            // Sun
            $sunYourObservations = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $sunTotalObservations = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->count();

            $sunYourObservationsLastYear = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $sunTotalObservationsLastYear = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->where('observed_on', '>=', now()->subYear())
                ->count();

            $sunYourDrawings = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->where('has_drawing', 1)
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $sunTotalDrawings = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->where('has_drawing', 1)
                ->count();

            $sunYourDrawingsLastYear = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->where('has_drawing', 1)
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $sunTotalDrawingsLastYear = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->where('has_drawing', 1)
                ->where('observed_on', '>=', now()->subYear())
                ->count();

            $sunYourUnique = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->distinct()->count('target_name');

            $sunTotalUnique = DB::table('observations')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->distinct()->count('target_name');

            // Moon
            $moonTargetTables = ['moons', 'lunar_features'];

            $moonYourObservations = DB::table('observations')
                ->whereIn('target_table', $moonTargetTables)
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $moonTotalObservations = DB::table('observations')
                ->whereIn('target_table', $moonTargetTables)
                ->count();

            $moonYourObservationsLastYear = DB::table('observations')
                ->whereIn('target_table', $moonTargetTables)
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $moonTotalObservationsLastYear = DB::table('observations')
                ->whereIn('target_table', $moonTargetTables)
                ->where('observed_on', '>=', now()->subYear())
                ->count();

            $moonYourDrawings = DB::table('observations')
                ->whereIn('target_table', $moonTargetTables)
                ->where('has_drawing', 1)
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $moonTotalDrawings = DB::table('observations')
                ->whereIn('target_table', $moonTargetTables)
                ->where('has_drawing', 1)
                ->count();

            $moonYourDrawingsLastYear = DB::table('observations')
                ->whereIn('target_table', $moonTargetTables)
                ->where('has_drawing', 1)
                ->where('observed_on', '>=', now()->subYear())
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->count();

            $moonTotalDrawingsLastYear = DB::table('observations')
                ->whereIn('target_table', $moonTargetTables)
                ->where('has_drawing', 1)
                ->where('observed_on', '>=', now()->subYear())
                ->count();

            $moonYourUnique = DB::table('observations')
                ->whereIn('target_table', $moonTargetTables)
                ->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)
                        ->orWhere('observer_username', $user->username);
                })->distinct()->count('target_name');

            $moonTotalUnique = DB::table('observations')
                ->whereIn('target_table', $moonTargetTables)
                ->distinct()->count('target_name');

            $stats = [
                'doubleTotalObservations' => $doubleTotalObservations,
                'doubleYourObservations' => $doubleYourObservations,
                'doubleTotalObservationsLastYear' => $doubleTotalObservationsLastYear,
                'doubleYourObservationsLastYear' => $doubleYourObservationsLastYear,
                'doubleTotalDrawings' => $doubleTotalDrawings,
                'doubleYourDrawings' => $doubleYourDrawings,
                'doubleTotalDrawingsLastYear' => $doubleTotalDrawingsLastYear,
                'doubleYourDrawingsLastYear' => $doubleYourDrawingsLastYear,
                'doubleTotalUnique' => $doubleTotalUnique,
                'doubleYourUnique' => $doubleYourUnique,

                'deepskyYourObservations' => $deepskyYourObservations,
                'deepskyTotalObservations' => $deepskyTotalObservations,
                'deepskyYourObservationsLastYear' => $deepskyYourObservationsLastYear,
                'deepskyTotalObservationsLastYear' => $deepskyTotalObservationsLastYear,
                'deepskyYourDrawings' => $deepskyYourDrawings,
                'deepskyTotalDrawings' => $deepskyTotalDrawings,
                'deepskyYourDrawingsLastYear' => $deepskyYourDrawingsLastYear,
                'deepskyTotalDrawingsLastYear' => $deepskyTotalDrawingsLastYear,
                'deepskyYourUnique' => $deepskyYourUnique,
                'deepskyTotalUnique' => $deepskyTotalUnique,

                'planetsYourObservations' => $planetsYourObservations,
                'planetsTotalObservations' => $planetsTotalObservations,
                'planetsYourObservationsLastYear' => $planetsYourObservationsLastYear,
                'planetsTotalObservationsLastYear' => $planetsTotalObservationsLastYear,
                'planetsYourDrawings' => $planetsYourDrawings,
                'planetsTotalDrawings' => $planetsTotalDrawings,
                'planetsYourDrawingsLastYear' => $planetsYourDrawingsLastYear,
                'planetsTotalDrawingsLastYear' => $planetsTotalDrawingsLastYear,
                'planetsYourUnique' => $planetsYourUnique,
                'planetsTotalUnique' => $planetsTotalUnique,

                'sunYourObservations' => $sunYourObservations,
                'sunTotalObservations' => $sunTotalObservations,
                'sunYourObservationsLastYear' => $sunYourObservationsLastYear,
                'sunTotalObservationsLastYear' => $sunTotalObservationsLastYear,
                'sunYourDrawings' => $sunYourDrawings,
                'sunTotalDrawings' => $sunTotalDrawings,
                'sunYourDrawingsLastYear' => $sunYourDrawingsLastYear,
                'sunTotalDrawingsLastYear' => $sunTotalDrawingsLastYear,
                'sunYourUnique' => $sunYourUnique,
                'sunTotalUnique' => $sunTotalUnique,

                'moonYourObservations' => $moonYourObservations,
                'moonTotalObservations' => $moonTotalObservations,
                'moonYourObservationsLastYear' => $moonYourObservationsLastYear,
                'moonTotalObservationsLastYear' => $moonTotalObservationsLastYear,
                'moonYourDrawings' => $moonYourDrawings,
                'moonTotalDrawings' => $moonTotalDrawings,
                'moonYourDrawingsLastYear' => $moonYourDrawingsLastYear,
                'moonTotalDrawingsLastYear' => $moonTotalDrawingsLastYear,
                'moonYourUnique' => $moonYourUnique,
                'moonTotalUnique' => $moonTotalUnique,
                // Unified totals
                'unifiedYourObservations' => $unifiedYourObservations,
                'unifiedTotalObservations' => $unifiedTotalObservations,
                'unifiedYourObservationsLastYear' => $unifiedYourObservationsLastYear,
                'unifiedTotalObservationsLastYear' => $unifiedTotalObservationsLastYear,
                'unifiedYourDrawings' => $unifiedYourDrawings,
                'unifiedTotalDrawings' => $unifiedTotalDrawings,
                'unifiedYourDrawingsLastYear' => $unifiedYourDrawingsLastYear,
                'unifiedTotalDrawingsLastYear' => $unifiedTotalDrawingsLastYear,
                'unifiedYourUnique' => $unifiedYourUnique,
                'unifiedTotalUnique' => $unifiedTotalUnique,

                // Global totals (all observations)
                'allYourObservations' => $allYourObservations,
                'allTotalObservations' => $allTotalObservations,
                'allYourObservationsLastYear' => $allYourObservationsLastYear,
                'allTotalObservationsLastYear' => $allTotalObservationsLastYear,
                'allYourDrawings' => $allYourDrawings,
                'allTotalDrawings' => $allTotalDrawings,
                'allYourDrawingsLastYear' => $allYourDrawingsLastYear,
                'allTotalDrawingsLastYear' => $allTotalDrawingsLastYear,
                'allYourUnique' => $allYourUnique,
                'allTotalUnique' => $allTotalUnique,

                // Comet totals
                'cometYourObservations' => $cometYourObservations,
                'cometTotalObservations' => $cometTotalObservations,
                'cometYourObservationsLastYear' => $cometYourObservationsLastYear,
                'cometTotalObservationsLastYear' => $cometTotalObservationsLastYear,
                'cometYourDrawings' => $cometYourDrawings,
                'cometTotalDrawings' => $cometTotalDrawings,
                'cometYourDrawingsLastYear' => $cometYourDrawingsLastYear,
                'cometTotalDrawingsLastYear' => $cometTotalDrawingsLastYear,
                'cometYourUnique' => $cometYourUnique,
                'cometTotalUnique' => $cometTotalUnique,
            ];

            // Precompute per-month series (user-specific) for charts to avoid runtime queries
            $months = range(1, 12);
            $zeroMonths = array_fill(0, 12, 0);

            // Helper to map grouped results into a months array
            $mapToMonths = function ($grouped) use ($zeroMonths) {
                $arr = $zeroMonths;
                foreach ($grouped as $r) {
                    $m = (int) $r->month;
                    if ($m >= 1 && $m <= 12) {
                        $arr[$m - 1] = (int) $r->cnt;
                    }
                }
                return $arr;
            };

            // User-scoped grouped queries
            $observerWhere = function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            };

            $unifiedDeepskyGrouped = DB::table('observations')
                ->selectRaw('MONTH(observed_on) as month, count(*) as cnt')
                ->where('target_table', 'objects')
                ->whereNull('legacy_source')
                ->where($observerWhere)
                ->groupByRaw('MONTH(observed_on)')
                ->get();

            $legacyDeepskyGrouped = DB::table('observations')
                ->selectRaw('MONTH(observed_on) as month, count(*) as cnt')
                ->where('legacy_source', 'deepsky')
                ->where($observerWhere)
                ->groupByRaw('MONTH(observed_on)')
                ->get();

            $doubleGrouped = DB::table('observations')
                ->selectRaw('MONTH(observed_on) as month, count(*) as cnt')
                ->where('target_table', 'objects')
                ->where($observerWhere)
                ->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->groupByRaw('MONTH(observed_on)')
                ->get();

            $cometGrouped = DB::table('observations')
                ->selectRaw('MONTH(observed_on) as month, count(*) as cnt')
                ->where('legacy_source', 'comet')
                ->where($observerWhere)
                ->groupByRaw('MONTH(observed_on)')
                ->get();

            $planetsGrouped = DB::table('observations')
                ->selectRaw('MONTH(observed_on) as month, count(*) as cnt')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->where($observerWhere)
                ->groupByRaw('MONTH(observed_on)')
                ->get();

            $sunGrouped = DB::table('observations')
                ->selectRaw('MONTH(observed_on) as month, count(*) as cnt')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->where($observerWhere)
                ->groupByRaw('MONTH(observed_on)')
                ->get();

            $moonGrouped = DB::table('observations')
                ->selectRaw('MONTH(observed_on) as month, count(*) as cnt')
                ->whereIn('target_table', ['moons', 'lunar_features'])
                ->where($observerWhere)
                ->groupByRaw('MONTH(observed_on)')
                ->get();

            $unifiedMonths = $mapToMonths($unifiedDeepskyGrouped);
            $legacyMonths = $mapToMonths($legacyDeepskyGrouped);
            $doubleMonths = $mapToMonths($doubleGrouped);
            $cometMonths = $mapToMonths($cometGrouped);
            $planetsMonths = $mapToMonths($planetsGrouped);
            $sunMonths = $mapToMonths($sunGrouped);
            $moonMonths = $mapToMonths($moonGrouped);

            $deepskyMonths = $unifiedMonths;
            for ($i = 0; $i < 12; $i++) {
                $deepskyMonths[$i] += $legacyMonths[$i];
                $deepskyMonths[$i] = max(0, $deepskyMonths[$i] - $doubleMonths[$i]);
            }

            // Per-year series
            $firstYear = DB::table('observations')
                ->where($observerWhere)
                ->min(DB::raw('YEAR(observed_on)'));

            if (is_null($firstYear)) {
                $firstYear = intval(date('Y'));
            }
            $lastYear = intval(date('Y'));
            $years = range($firstYear, $lastYear);

            $mapToYears = function ($grouped, $firstYear, $lastYear) {
                $len = $lastYear - $firstYear + 1;
                $arr = array_fill(0, $len, 0);
                foreach ($grouped as $r) {
                    $y = (int) $r->year;
                    if ($y >= $firstYear && $y <= $lastYear) {
                        $arr[$y - $firstYear] = (int) $r->cnt;
                    }
                }
                return $arr;
            };

            $unifiedDeepskyYearGrouped = DB::table('observations')
                ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
                ->where('target_table', 'objects')
                ->whereNull('legacy_source')
                ->where($observerWhere)
                ->groupByRaw('YEAR(observed_on)')
                ->get();

            $legacyDeepskyYearGrouped = DB::table('observations')
                ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
                ->where('legacy_source', 'deepsky')
                ->where($observerWhere)
                ->groupByRaw('YEAR(observed_on)')
                ->get();

            $doubleYearGrouped = DB::table('observations')
                ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
                ->where('target_table', 'objects')
                ->where($observerWhere)
                ->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->groupByRaw('YEAR(observed_on)')
                ->get();

            $cometYearGrouped = DB::table('observations')
                ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
                ->where('legacy_source', 'comet')
                ->where($observerWhere)
                ->groupByRaw('YEAR(observed_on)')
                ->get();

            $planetsYearGrouped = DB::table('observations')
                ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) != ?', ['sun'])
                ->where($observerWhere)
                ->groupByRaw('YEAR(observed_on)')
                ->get();

            $sunYearGrouped = DB::table('observations')
                ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
                ->where('target_table', 'planets')
                ->whereRaw('LOWER(target_key) = ?', ['sun'])
                ->where($observerWhere)
                ->groupByRaw('YEAR(observed_on)')
                ->get();

            $moonYearGrouped = DB::table('observations')
                ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
                ->whereIn('target_table', ['moons', 'lunar_features'])
                ->where($observerWhere)
                ->groupByRaw('YEAR(observed_on)')
                ->get();

            $unifiedYears = $mapToYears($unifiedDeepskyYearGrouped, $firstYear, $lastYear);
            $legacyYears = $mapToYears($legacyDeepskyYearGrouped, $firstYear, $lastYear);
            $doubleYears = $mapToYears($doubleYearGrouped, $firstYear, $lastYear);
            $cometYears = $mapToYears($cometYearGrouped, $firstYear, $lastYear);
            $planetsYears = $mapToYears($planetsYearGrouped, $firstYear, $lastYear);
            $sunYears = $mapToYears($sunYearGrouped, $firstYear, $lastYear);
            $moonYears = $mapToYears($moonYearGrouped, $firstYear, $lastYear);

            $deepskyYears = $unifiedYears;
            for ($i = 0; $i < count($deepskyYears); $i++) {
                $deepskyYears[$i] += $legacyYears[$i];
                $deepskyYears[$i] = max(0, $deepskyYears[$i] - $doubleYears[$i]);
            }

            $stats['perMonth'] = [
                'deepsky' => $deepskyMonths,
                'comets' => $cometMonths,
                'double' => $doubleMonths,
                'planets' => $planetsMonths,
                'sun' => $sunMonths,
                'moon' => $moonMonths,
            ];

            $stats['perYear'] = [
                'x_axis' => $years,
                'deepsky' => $deepskyYears,
                'comets' => $cometYears,
                'double' => $doubleYears,
                'planets' => $planetsYears,
                'sun' => $sunYears,
                'moon' => $moonYears,
            ];

            // Compute whether user has any liked observations (deepsky or comet) — use a JOIN to avoid loading IDs into PHP
            $hasPopularObservations = DB::table('observation_likes as ol')
                ->join('observations as o', function ($join) {
                    $join->on('o.id', '=', 'ol.observation_id')
                        ->on('ol.observation_type', '=', 'o.legacy_source');
                })
                ->where(function ($q) use ($user) {
                    $q->where('o.observer_user_id', $user->id)
                        ->orWhere('o.observer_username', $user->username);
                })
                ->whereIn('ol.observation_type', ['deepsky', 'comet'])
                ->exists();

            $stats['hasPopularObservations'] = $hasPopularObservations;

            // Cache for 6 hours
            Cache::put($cacheKey, $stats, now()->addHours(6));
        } catch (\Throwable $e) {
            // Ensure we don't leave the in-progress flag set
        }

        Cache::forget($inProgressKey);
    }
}
