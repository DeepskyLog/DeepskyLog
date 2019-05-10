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
use Auth;
//Importing laravel-permission models
use Spatie\Permission\Models\Permission;
//Enables us to output flash messaging
use Session;

// For the datatables
use App\DataTables\UserDataTable;

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
        $this->middleware(['auth', 'verified', 'isAdmin']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param LensDataTable $dataTable The user datatable
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
        return redirect('users');
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
        $this->validate(
            $request,
            [
                'email' => 'required|unique|min:2',
                'name' => 'required|max:120',
                'email' => 'required|email|unique:users,email,' . $id,
                'type' => 'required'
            ]
        );
        // Retrieve the name, email and password fields
        $input = $request->only(['username', 'name', 'email', 'type']);

        $user->type = $request['type'];

        $user->fill($input)->save();

        flash()->success(_i('User %s successfully edited.', $user->name));

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

        flash()->warning(_i('User %s successfully deleted.', $user->name));

        $user->delete();

        return redirect()->route('users.index');
    }

    /**
     * Upload the image for the observer.
     *
     * @param Request $request The request from filePond
     *
     * @return None
     */
    public function upload(Request $request)
    {
        User::find(auth()->user()->id)
            ->addMediaFromRequest('filepond')
            ->usingFileName(auth()->user()->id . '.png')
            ->toMediaCollection('observer');
    }

    /**
     * Delete the image for the observer.
     *
     * @return None
     */
    public function delete()
    {
        User::find(auth()->user()->id)
            ->getFirstMedia('observer')
            ->delete();
    }

    /**
     * Returns the image of the observer.
     *
     * @return MediaObject the image of the observer
     */
    public function getImage()
    {
        if (User::find(auth()->user()->id)->hasMedia('observer')) {
            return User::find(auth()->user()->id)
                ->getFirstMedia('observer');
        } else {
            User::find(auth()->user()->id)
            ->addMediaFromUrl(asset('img/profile.png'))
            ->usingFileName(auth()->user()->id . '.png')
            ->toMediaCollection('observer');

            return User::find(auth()->user()->id)
                ->getFirstMedia('observer');
        }
    }

    /**
     * Patch the settings for the observer.
     *
     * @param Request $request The request object with all information.
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

        // Update the copyright
        if ($request->has('standardAtlasCode')) {
            $user->update(
                ['standardAtlasCode' => $request->get('standardAtlasCode')]
            );
        }

        // Update the copyright
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

        return redirect('/user/settings/' . $id);
    }
}
