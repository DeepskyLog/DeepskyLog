<?php

namespace App\Http\Controllers;

class PopularObservationController extends Controller
{
    /**
     * The list itself (with its search) is the PopularObservationsTable
     * Livewire component.
     */
    public function index()
    {
        return view('observations.popular');
    }
}
