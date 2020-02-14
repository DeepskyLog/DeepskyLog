<?php

/**
 * User Controller.
 *
 * PHP Version 7
 *
 * @category Authentication
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\DataTables\UserDataTable;
use Carbon\Carbon;

/**
 * User Controller.
 *
 * PHP Version 7
 *
 * @category Authentication
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class UserController extends Controller
{
    /**
     * Make sure the user pages can be seen if the user is authenticated,
     * administrator and verified.
     */
    public function __construct()
    {
        // isAdmin middleware lets only users with a
        // specific permission to access these resources
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param UserDataTable $dataTable The user datatable
     *
     * @return \Illuminate\Http\Response
     */
    public function index(UserDataTable $dataTable)
    {
        return $dataTable->render('users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id the user id to show
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        $obsPerYear = $this->chartObservationsPerYear($user);
        $obsPerMonth = $this->chartObservationsPerMonth($user);

        return view(
            'users.view',
            ['user' => $user, 'observationsPerYear' => $obsPerYear,
                'observationsPerMonth' => $obsPerMonth]
        );
    }

    /**
     * Makes the chart with the observations per year.
     *
     * @param User $user The User object
     *
     * @return Chart The chart to display
     */
    protected function chartObservationsPerYear($user)
    {
        return \Chart::title(
            [
                'text' => _i('Number of observations per year: ') . $user->name,
            ]
        )->chart(
            [
                // pie , columnt etc
                'type' => 'line',
                // render the chart into your div with id
                'renderTo' => 'observationsPerYear',
                'zoomType' => 'x',
            ]
        )->subtitle(
            [
                'text' => _i('Source: ') . 'https://www.deepskylog.org/',
            ]
        )->xaxis(
            [
                'categories' => [
                    '2009',
                    '2010',
                    '2011',
                    '2012',
                    '2013',
                    '2014',
                    '2015',
                    '2016',
                    '2017',
                    '2018',
                    '2019',
                ],
                'labels' => [
                    'rotation' => 0,
                    'align' => 'top',
                    //'formatter' => 'startJs:function(){return this.value}:endJs',
                    // use 'startJs:yourjavasscripthere:endJs'
                ],
            ]
        )->yaxis(
            [
                'title' => ['text' => _i('Observations')],
            ]
        )->legend(
            [
                'layout' => 'vertikal',
                'align' => 'right',
                'verticalAlign' => 'middle',
            ]
        )->series(
            [
                [
                    'name' => _i('Total'),
                    'data' => [124, 439, 525, 571, 696, 0, 100, 324, 129, 77, 12],
                ],
                [
                    'name' => _i('Deepsky'),
                    'data' => [120, 400, 423, 333, 500, 0, 77, 11, 12, 7, 4],
                ],
                [
                    'name' => _i('Comets'),
                    'data' => [23, 10, 23, 33, 50, 0, 7, 15, 66, 23, 1],
                ],
                [
                    'name' => _i('Double stars'),
                    'data' => [12, 3, 9, 22, 30, 0, 12, 18, 77, 18, 3],
                ],
                [
                    'name' => _i('Planets'),
                    'data' => [12, 3, 9, 22, 30, 0, 12, 18, 77, 18, 3],
                ],
                [
                    'name' => _i('Sun'),
                    'data' => [12, 3, 9, 22, 30, 0, 12, 18, 77, 18, 3],
                ],
                [
                    'name' => _i('Moon'),
                    'data' => [12, 3, 9, 22, 30, 0, 12, 18, 77, 18, 3],
                ],
            ]
        )->display();
    }

    /**
     * Makes the chart with the observations per month.
     *
     * @param User $user The User object
     *
     * @return Chart The chart to display
     */
    protected function chartObservationsPerMonth($user)
    {
        return \Chart::title(
            [
                'text' => _i('Number of observations per month: ') . $user->name,
            ]
        )->chart(
            [
                // pie , columnt etc
                'type' => 'column',
                // render the chart into your div with id
                'renderTo' => 'observationsPerMonth',
            ]
        )->plotOptions(
            [
                'column' => ['stacking' => 'normal'],
            ]
        )->subtitle(
            [
                'text' => _i('Source: ') . 'https://www.deepskylog.org/',
            ]
        )->xaxis(
            [
                // Add months of the year (short version)
                'categories' => [
                    Carbon::parse('2018-01-20')->isoFormat('MMM'),
                    Carbon::parse('2018-02-20')->isoFormat('MMM'),
                    Carbon::parse('2018-03-20')->isoFormat('MMM'),
                    Carbon::parse('2018-04-20')->isoFormat('MMM'),
                    Carbon::parse('2018-05-20')->isoFormat('MMM'),
                    Carbon::parse('2018-06-20')->isoFormat('MMM'),
                    Carbon::parse('2018-07-20')->isoFormat('MMM'),
                    Carbon::parse('2018-08-20')->isoFormat('MMM'),
                    Carbon::parse('2018-09-20')->isoFormat('MMM'),
                    Carbon::parse('2018-10-20')->isoFormat('MMM'),
                    Carbon::parse('2018-11-20')->isoFormat('MMM'),
                    Carbon::parse('2018-12-20')->isoFormat('MMM'),
                ],
                'labels' => [
                    'rotation' => 0,
                    'align' => 'center',
                    //'formatter' => 'startJs:function(){return this.value}:endJs',
                    // use 'startJs:yourjavasscripthere:endJs'
                ],
            ]
        )->yaxis(
            [
                'title' => ['text' => _i('Observations')],
            ]
        )->legend(
            [
                'layout' => 'vertikal',
                'align' => 'right',
                'verticalAlign' => 'middle',
            ]
        )->series(
            [
                [
                    'name' => _i('Deepsky'),
                    'data' => [120, 400, 423, 333, 500, 0, 77, 11, 12, 7, 4, 6],
                ],
                [
                    'name' => _i('Comets'),
                    'data' => [23, 10, 23, 33, 50, 0, 7, 15, 66, 23, 1, 7],
                ],
                [
                    'name' => _i('Double stars'),
                    'data' => [12, 3, 9, 22, 30, 0, 12, 18, 77, 18, 3, 8],
                ],
                [
                    'name' => _i('Planets'),
                    'data' => [12, 3, 9, 22, 30, 0, 12, 18, 77, 18, 3, 9],
                ],
                [
                    'name' => _i('Sun'),
                    'data' => [12, 3, 9, 22, 30, 0, 12, 18, 77, 18, 3, 10],
                ],
                [
                    'name' => _i('Moon'),
                    'data' => [12, 3, 9, 22, 30, 0, 12, 18, 77, 18, 3, 11],
                ],
            ]
        )->display();
    }

    /**
     * Display the settings page for the observer.
     *
     * @param int $id the user id to show
     *
     * @return \Illuminate\Http\Response
     */
    public function settings($id)
    {
        if (auth()->user()->id == $id) {
            $user = auth()->user();

            return view('users.settings', ['user' => $user]);
        } else {
            abort(401);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id the user id to edit
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //Get user with specified id
        $user = User::findOrFail($id);
        //pass user data to view
        return view('users.edit', compact('user'));
    }

    /**
     * Validate the values of the form.
     *
     * @param \Illuminate\Http\Request $request The request with all information
     *
     * @return \Illuminate\Http\Request The validated request
     */
    public function validateInput(Request $request)
    {
        return $request->validate(
            [
                'email' => 'required|unique|min:2',
                'name' => 'required|max:120',
                'email' => 'required|email|unique:users,email,' . $id,
                'type' => 'required',
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request The request
     * @param int                      $id      The id of the user to update
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Get user specified by id
        $user = User::findOrFail($id);

        //Validate name, email and password fields
        $this->validateInput($request);

        // Retrieve the name, email and password fields
        $input = $request->only(['username', 'name', 'email', 'type']);

        $user->type = $request['type'];

        $user->fill($input)->save();

        laraflash(_i('User %s successfully edited.', $user->name))->success();

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id the user id of the user to delete
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Find a user with a given id and delete
        $user = User::findOrFail($id);

        laraflash(_i('User %s successfully deleted.', $user->name))->warning();

        $user->delete();

        return redirect()->route('users.index');
    }

    /**
     * Returns the image of the observer.
     *
     * @param int $id The id of the observer
     *
     * @return MediaObject the image of the observer
     */
    public function getImage($id)
    {
        if (User::find($id)->hasMedia('observer')) {
            return User::find($id)
                ->getFirstMedia('observer');
        } else {
            User::find($id)
                ->addMediaFromUrl(asset('img/profile.png'))
                ->usingFileName($id . '.png')
                ->toMediaCollection('observer');

            return User::find($id)
                ->getFirstMedia('observer');
        }
    }

    /**
     * Remove the image of the observer
     *
     * @param integer $id The id of the observer
     *
     * @return None
     */
    public function deleteImage($id)
    {
        User::find($id)
            ->getFirstMedia('observer')
            ->delete();

        return '{}';
    }

    /**
     * Returns the image of the observer.
     *
     * @return MediaObject the image of the observer
     */
    public function getAuthenticatedUserImage()
    {
        $id = auth()->user()->id;
        if (User::find($id)->hasMedia('observer')) {
            return User::find($id)
                ->getFirstMedia('observer');
        } else {
            User::find($id)
                ->addMediaFromUrl(asset('img/profile.png'))
                ->usingFileName($id . '.png')
                ->toMediaCollection('observer');

            return User::find($id)
                ->getFirstMedia('observer');
        }
    }

    /**
     * Patch the settings for the observer.
     *
     * @param Request $request The request object with all information
     * @param int     $id      The id of the observer
     *
     * @return None
     */
    public function patchSettings(Request $request, $id)
    {
        // The authenticated user
        $user = auth()->user();

        // Update the email
        if ($request->has('email')) {
            $user->update(['email' => $request->get('email')]);
        }

        // Update the name
        if ($request->has('name')) {
            $user->update(['name' => $request->get('name')]);
        }

        if ($request->has('username')) {
            if ($request->has('sendMail')) {
                $user->update(['sendMail' => 1]);
            } else {
                $user->update(['sendMail' => '0']);
            }
        }

        // Update the fstOffset
        if ($request->has('fstOffset')) {
            $user->update(['fstOffset' => $request->get('fstOffset')]);
        }

        // Update the copyright
        if ($request->has('copyright')) {
            $user->update(['copyright' => $request->get('copyright')]);
        }

        // Update the standard instrument
        if ($request->has('stdinstrument')) {
            $user->update(
                ['stdtelescope' => $request->get('stdinstrument')]
            );
        }

        // Update the standard instrument
        if ($request->has('stdlocation')) {
            $user->update(
                ['stdlocation' => $request->get('stdlocation')]
            );
        }

        // Update the standard atlas
        if ($request->has('standardAtlasCode')) {
            $user->update(
                ['standardAtlasCode' => $request->get('standardAtlasCode')]
            );
        }

        // Update imperial / metric
        if ($request->has('showInches')) {
            $user->update(
                ['showInches' => $request->get('showInches')]
            );
        }

        // Update the overviewFoV
        if ($request->has('overviewFoV')) {
            $user->update(
                ['overviewFoV' => $request->get('overviewFoV')]
            );
        }

        // Update the lookupFoV
        if ($request->has('lookupFoV')) {
            $user->update(
                ['lookupFoV' => $request->get('lookupFoV')]
            );
        }

        // Update the detailFoV
        if ($request->has('detailFoV')) {
            $user->update(
                ['detailFoV' => $request->get('detailFoV')]
            );
        }

        // Update the overviewdsos
        if ($request->has('overviewdsos')) {
            $user->update(
                ['overviewdsos' => $request->get('overviewdsos')]
            );
        }

        // Update the lookupdsos
        if ($request->has('lookupdsos')) {
            $user->update(
                ['lookupdsos' => $request->get('lookupdsos')]
            );
        }

        // Update the detaildsos
        if ($request->has('detaildsos')) {
            $user->update(
                ['detaildsos' => $request->get('detaildsos')]
            );
        }

        // Update the overviewstars
        if ($request->has('overviewstars')) {
            $user->update(
                ['overviewstars' => $request->get('overviewstars')]
            );
        }

        // Update the lookupstars
        if ($request->has('lookupstars')) {
            $user->update(
                ['lookupstars' => $request->get('lookupstars')]
            );
        }

        // Update the detailstars
        if ($request->has('detailstars')) {
            $user->update(
                ['detailstars' => $request->get('detailstars')]
            );
        }

        // Update the photosize1
        if ($request->has('photosize1')) {
            $user->update(
                ['photosize1' => $request->get('photosize1')]
            );
        }

        // Update the photosize2
        if ($request->has('photosize2')) {
            $user->update(
                ['photosize2' => $request->get('photosize2')]
            );
        }

        // Update the atlaspagefont
        if ($request->has('atlaspagefont')) {
            $user->update(
                ['atlaspagefont' => $request->get('atlaspagefont')]
            );
        }

        // Update the country of residence
        if ($request->has('country')) {
            $user->update(
                ['country' => $request->get('country')]
            );
        }

        // Update the language for the user interface
        if ($request->has('language')) {
            $user->update(
                ['language' => $request->get('language')]
            );
        }

        // Update the language for the observations
        if ($request->has('observationlanguage')) {
            $user->update(
                ['observationlanguage' => $request->get('observationlanguage')]
            );
        }

        // Update the image
        if ($request->picture != null) {
            if (User::find($user->id)->getFirstMedia('observer') != null
            ) {
                // First remove the current image
                User::find($user->id)
                ->getFirstMedia('observer')
                ->delete();
            }

            // Update the picture
            User::find($user->id)
                ->addMedia($request->picture->path())
                ->usingFileName($user->id . '.png')
                ->toMediaCollection('observer');
        }

        return redirect()->back();
    }
}
