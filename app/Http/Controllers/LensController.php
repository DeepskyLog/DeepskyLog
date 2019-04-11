<?php
/**
 * Lens Controller.
 *
 * PHP Version 7
 *
 * @category Lenses
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use App\Lens;
use Illuminate\Http\Request;

/**
 * Lens Controller.
 *
 * @category Lenses
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class LensController extends Controller
{
    /**
     * Only make sure the lens pages can be seen if the user is authenticated
     * and verified.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified'])->except(['show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lenses = auth()->user()->lenses()->get();

        return view('layout.lens.view')->with('lenses', $lenses);
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
        $request['observer_id'] = auth()->id();

        $validated = request()->validate(
            [
                'observer_id' => 'required',
                'name' => ['required', 'min:6'],
                'factor' => ['required', 'numeric', 'min:0', 'max:10'],
            ]
        );

        Lens::create($validated);

        flash()->success(_i('Lens "%s" created', $request->name));

        // View the page with all lenses for the user
        return redirect('/lens');
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
        return view('layout.lens.show', ['lens' => $lens]);
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
        $this->authorize('update', $lens);

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
        $this->authorize('update', $lens);

        $request['observer_id'] = $lens->observer_id;

        // If the factor is set, the name should also be set in the form.
        if ($request->has('factor')) {
            $validated = request()->validate(
                [
                    'observer_id' => 'required',
                    'name' => ['required', 'min:6'],
                    'factor' => ['required', 'numeric', 'min:0', 'max:10'],
                    ]
            );

            $lens->update(['factor' => $request->get('factor')]);
            $lens->update(['name' => $request->get('name')]);

            flash()->warning(_i('Lens "%s" updated', $lens->name));
        } else {
            // This is only reached when clicking the active checkbox in the
            // lens overview.
            if ($request->has('active')) {
                $lens->active();
                flash()->warning(_i('Lens "%s" is active', $lens->name));
            } else {
                $lens->inactive();
                flash()->warning(_i('Lens "%s" is not longer active', $lens->name));
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
        $this->authorize('update', $lens);

        $lens->delete();

        flash()->error(_i('Lens "%s" deleted', $lens->name));

        return redirect('/lens');
    }
}
