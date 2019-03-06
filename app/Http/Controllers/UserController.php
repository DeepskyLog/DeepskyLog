<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use Auth;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

//Enables us to output flash messaging
use Session;

class UserController extends Controller {

    public function __construct()
    {
        // isAdmin middleware lets only users with a
        // specific permission permission to access these resources
        $this->middleware(['auth', 'isAdmin']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Get all users and pass it to the view
        $users = User::all();
        return view('users.index')->with('users', $users);
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
        //Get all roles
        $roles = Role::get();

        //pass user and roles data to view
        return view('users.edit', compact('user', 'roles'));
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
        //Get role specified by id
        $user = User::findOrFail($id);

        //Validate name, email and password fields
        $this->validate(
            $request, [
                'name'=>'required|max:120',
                'email'=>'required|email|unique:users,email,'.$id
            ]
        );
        // Retrieve the name, email and password fields
        $input = $request->only(['name', 'email', 'password']);
        // Retreive all roles
        $roles = $request['roles'];
        $user->fill($input)->save();

        if (isset($roles)) {
            //If one or more roles are selected associate user to roles
            $user->roles()->sync($roles);
        } else {
            //If no role is selected remove exisiting role associated to a user
            $user->roles()->detach();
        }
        return redirect()->route('users.index')->with(
            'flash_message',
            _i('User successfully edited.')
        );
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
        $user->delete();

        // TODO: Add the name of the deleted user!
        return redirect()->route('users.index')->with(
            'flash_message',
            _i('User successfully deleted.')
        );
    }
}
