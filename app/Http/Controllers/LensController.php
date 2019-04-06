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
        $this->middleware(['auth', 'verified', 'clearance'])->except(['show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // SEEDER
        // TODO: UserFactory: Take country out of a list with possibilities
        // TODO: UserFactory: Take copyright out of a list with possibilities (in Faker?)
        // TODO: Write LensFactory

        // TESTS
        // TODO: Why do the test don't use the UserFactory and fail because country is not set?
        // TODO: Tests for a lens with a too short name, negative factor, ...
        // TODO: Test lens when not logged in
        // TODO: Test when the user is logged in, but not verified
        // TODO: Check if DeepskyLog can send a mail to the user to verify.
        // TODO: Extra tests for the user class.

        // LENSES
        // TODO: Export lenses should also have the correct name
        // TODO: Fix edit lens
        // TODO: Write view one lens
        // TODO: Add flash_messages when lens is updated (see store)
        // TODO: Show all lenses (as administrator)

        // AUTHENTICATION
        // TODO: We need: guest, verified and admin -> Do we need spatie/laravel-permissions for that?
        // TODO: Write user settings page and table for the DeepskyLog information
        // TODO: Update admin page for the users, add extra information,
        //        move operations in two different colums, use icons for operations
        // TODO: Use authentication on the pages and in the layout.
        // TODO: Write script to convert old observers table of DeepskyLog to laravel
        // TODO: Write script to convert old lenses table of DeepskyLog to laravel
        // TODO: Page to change observer settings
        // TODO: Page to view observer
        // TODO: Clean up source code

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
        $validated = request()->validate(
            [
                'observer_id' => 'required',
                'name' => ['required', 'min:5'],
                'factor' => 'required',
            ]
        );

        $validated['observer_id'] = auth()->id();

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
        return view();
        // TO WRITE
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
        // TO WRITE
        $this->authorize('edit', $lens);

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
                    'factor' => 'required',
                ]
            );

            $lens->update(['factor' => $request->get('factor')]);
            $lens->update(['name' => $request->get('name')]);
        } else {
            // This is only reached when clicking the active checkbox in the
            // lens overview.
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

        flash()->warning(_i('Lens "%s" deleted', $lens->name));

        return redirect('/lens');
    }
}
