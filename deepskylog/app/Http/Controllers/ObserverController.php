<?php

namespace App\Http\Controllers;

use App\Charts\CountriesChart;
use App\Charts\ObjectTypesChart;
use App\Charts\ObservationsPerMonthChart;
use App\Charts\ObservationsPerYearChart;
use App\Models\ObservationsOld;
use App\Models\User;
use Illuminate\Http\Request;

class ObserverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug, ObservationsPerYearChart $chart, ObservationsPerMonthChart $chart2,
        ObjectTypesChart $chart3, CountriesChart $chart4)
    {
        $user = User::where('slug', $slug)->firstOrFail();

        $observationsOld = $user->observations();
        $totalObservations = ObservationsOld::getTotalObservations();

        // Get the observations from the last year
        $lastYear = date('Ymd') - 10000;

        $observationsLastYear = ObservationsOld::where('date', '>=', $lastYear)->where('observerid', $user->username)->count();
        $totalObservationsLastYear = ObservationsOld::where('date', '>=', $lastYear)->count();

        $totalNumberOfDrawings = ObservationsOld::where('hasDrawing', 1)->count();
        $totalUniqueObjects = ObservationsOld::getUniqueObjectsObserved();

        return view('observers.show', [
            'user' => $user,
            'observations' => $observationsOld,
            'totalObservations' => $totalObservations,
            'observationsLastYear' => $observationsLastYear,
            'totalObservationsLastYear' => $totalObservationsLastYear,
            'totalNumberOfDrawings' => $totalNumberOfDrawings,
            'totalUniqueObjects' => $totalUniqueObjects,
            'observationsPerYearChart' => $chart->build($user),
            'observationsPerMonthChart' => $chart2->build($user),
            'objectTypesChart' => $chart3->build($user),
            'countriesChart' => $chart4->build($user),
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function admin()
    {
        return view('observers.admin');
    }
}
