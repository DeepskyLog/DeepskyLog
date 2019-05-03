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
     * @param int $id The user id to show.
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
     * @param int $id The user id to show.
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
     * @param int $id The user id to edit.
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
            $request, [
                'name'=>'required|max:120',
                'email'=>'required|email|unique:users,email,'.$id,
                'type'=>'required'
            ]
        );
        // Retrieve the name, email and password fields
        $input = $request->only(['name', 'email', 'type']);

        $user->type = $request['type'];

        $user->fill($input)->save();

        flash()->success(_i('User %s successfully edited.', $user->name));

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id The user id of the user to delete.
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
     * Returns the image of the observer
     *
     * @return MediaObject The image of the observer.
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
}
