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
        return view('layout.lens.view');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Lens $lens The lens to fill out in the fields
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Lens $lens)
    {
        return view('layout.lens.create', ['lens' => $lens, 'update' => false]);
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
        return view('layout.lens.view');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Lens $lens The lens to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Lens $lens)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Lens $lens The lens to edit
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Lens $lens)
    {
        return view('layout.lens.create', ['lens' => $lens, 'update' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request The request with all information
     * @param Lens    $lens    The lens to adapt
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Lens $lens)
    {
        // If the factor is set, the name should also be set in the form.
        if ($request->has('factor')) {
            $validated = request()->validate(
                [
                    'observer_id' => 'required',
                    'name' => ['required', 'min:5'],
                    'factor' => 'required'
                ]
            );

            $lens->update(['factor' => $request->get('factor')]);
            $lens->update(['name' => $request->get('name')]);
        } else {
            // This is only reached when clicking the active checkbox in the lens overview.
            if ($request->has('active')) {
                $lens->active();
            } else {
                $lens->inactive();
            }
        }

        return redirect('/lens');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Lens $lens The lens to remove
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lens $lens)
    {
        $lens->delete();

        return view('layout.lens.view');
    }
}
