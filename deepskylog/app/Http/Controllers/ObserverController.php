<?php

namespace App\Http\Controllers;

use App\Charts\ObservationsPerYearChart;
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
    public function show(string $slug, ObservationsPerYearChart $chart)
    {
        $user = User::where('slug', $slug)->firstOrFail();

        return view('observers.show', [
            'user' => $user,
            'chart' => $chart->build($user),
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
}
