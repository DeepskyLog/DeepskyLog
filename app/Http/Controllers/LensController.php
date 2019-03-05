<?php

namespace App\Http\Controllers;

use App\Lenses;
use Illuminate\Http\Request;

class LensController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['index', 'create']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // TODO: Test index
        // TODO: Test creating a new lens (only authenticated!)
        // TODO: ...
        $lenses = Lenses::where(‘observer_id’, auth()->id())->get();

        return view('layout.lenses.view', compact($lenses));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Lenses $lens The lens to fill out in the fields
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Lenses $lens)
    {
        return view('layout.lenses.create', ['lens' => $lens, 'update' => false]);
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

        $validated['observer_id'] = auth()->id();

        Lenses::create($validated);

        // View the page with all lenses for the user
        return view('layout.lenses.view');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Lenses $lens The lens to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Lenses $lens)
    {
        abort_unless(auth()->user()->owns($lens), 403);

        // TO WRITE
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Lenses $lens The lens to edit
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Lenses $lens)
    {
        return view('layout.lenses.create', ['lens' => $lens, 'update' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request The request with all information
     * @param Lenses  $lens    The lens to adapt
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Lenses $lens)
    {
        if ($request->has('active')) {
            $lens->active();
        } else {
            $lens->inactive();
        }

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
        }

        return view('layout.lenses.view');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Lenses $lens The lens to remove
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lenses $lens)
    {
        $lens->delete();

        return view('layout.lenses.view');
    }
}
