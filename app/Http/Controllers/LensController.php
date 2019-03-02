<?php

namespace App\Http\Controllers;

use App\Lenses;
use Illuminate\Http\Request;

class LensController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('layout.lenses.view');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Lenses $lense The lens to fill out in the fields
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Lenses $lense)
    {
        return view('layout.lenses.create', ['lens' => $lense, 'update' => false]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request The request with all information
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = request()->validate(
            [
                'observer_id' => 'required',
                'name' => ['required', 'min:5'],
                'factor' => 'required'
            ]
        );

        Lenses::create($validated);

        // View the page with all lenses for the user
        return view('layout.lenses.view');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Lenses $lense The lens to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Lenses $lense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Lenses $lense The lens to edit
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Lenses $lense)
    {
        return view('layout.lenses.create', ['lens' => $lense, 'update' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request The request with all information
     * @param Lenses  $lense   The lens to adapt
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Lenses $lense)
    {
        if ($request->has('active')) {
            $lense->active();
        } else {
            $lense->inactive();
        }

        if ($request->has('factor')) {
            $validated = request()->validate(
                [
                    'observer_id' => 'required',
                    'name' => ['required', 'min:5'],
                    'factor' => 'required'
                ]
            );

            $lense->update(['factor' => $request->get('factor')]);
            $lense->update(['name' => $request->get('name')]);
        }

        return view('layout.lenses.view');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Lenses $lense The lens to remove
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lenses $lense)
    {
        $lense->delete();

        return view('layout.lenses.view');
    }
}
