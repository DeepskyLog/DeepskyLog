<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use Auth;

//Importing laravel-permission models
use Spatie\Permission\Models\Permission;

//Enables us to output flash messaging
use Session;

class UserController extends Controller {

    public function __construct()
    {
        // isAdmin middleware lets only users with a
        // specific permission to access these resources
        $this->middleware(['auth', 'verified', 'isAdmin']);
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
}
