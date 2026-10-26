<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\ObservationCardData;
use App\Services\ObserverYear;

/**
 * The year in review of an observer: public, only for finished years in which
 * they have observations.
 */
class ObserverYearController extends Controller
{
    public function show(string $observer, int $year)
    {
        $user  = User::where('slug', $observer)->firstOrFail();
        $years = ObserverYear::years($user);
        if (! ObserverYear::isShown($year) || ! $years->contains($year)) {
            abort(404);
        }

        $review  = new ObserverYear($user, $year);
        $summary = $review->summary();

        $drawings = Observation::whereIn('id', $summary['drawing_ids'])->get()
            ->sortBy(fn (Observation $observation) => array_search($observation->id, $summary['drawing_ids'], true))
            ->values();
        ObservationCardData::instance()->preloadDetailUrls($drawings);

        return view('observers.year', [
            'user'     => $user,
            'year'     => $year,
            'years'    => $years,
            'summary'  => $summary,
            'drawings' => $drawings,
            'goals'    => $review->goals(auth()->user()),
        ]);
    }
}
