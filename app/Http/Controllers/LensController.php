<?php

namespace App\Http\Controllers;

use App\Lens;
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
     * @param Lens $len The lens to fill out in the fields
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Lens $len)
    {
        return view('layout.lenses.create', ['lens' => $len, 'update' => false]);
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

        Lens::create($validated);

        // View the page with all lenses for the user
        return view('layout.lenses.view');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Lens  $len The lens to show
     * @return \Illuminate\Http\Response
     */
    public function show(Lens $len)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Lens $len The lens to edit
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Lens $len)
    {
        return view('layout.lenses.create', ['lens' => $len, 'update' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request The request with all information
     * @param Lens    $len     The id of the lens to adapt
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Lens $len)
    {
        if ($request->has('active')) {
            $len->active();
        } else {
            $len->inactive();
        }

        if ($request->has('factor')) {
            $validated = request()->validate(
                [
                    'observer_id' => 'required',
                    'name' => ['required', 'min:5'],
                    'factor' => 'required'
                ]
            );

            $len->update(['factor' => $request->get('factor')]);
            $len->update(['name' => $request->get('name')]);
        }

        return view('layout.lenses.view');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Lens $len The lens to remove
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lens $len)
    {
        $len->delete();

        return view('layout.lenses.view');
    }
}
