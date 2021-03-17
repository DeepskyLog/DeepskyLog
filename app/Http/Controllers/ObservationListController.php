<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObservationList;

class ObservationListController extends Controller
{
    /**
     * Make sure the observationList pages can only be seen if the user is authenticated
     * and verified.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified'])->except(['show', 'index']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->_indexView('user');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin()
    {
        if (auth()->user()->isAdmin()) {
            return $this->_indexView('admin');
        } else {
            abort(401);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param string              $user      user for a normal user, admin for an admin
     *
     * @return \Illuminate\Http\Response
     */
    private function _indexView($user)
    {
        return view('layout.observationList.view');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ObservationList  $observationList
     * @return \Illuminate\Http\Response
     */
    public function show(ObservationList $observationList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ObservationList  $observationList
     * @return \Illuminate\Http\Response
     */
    public function edit(ObservationList $observationList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ObservationList  $observationList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ObservationList $observationList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ObservationList  $observationList
     * @return \Illuminate\Http\Response
     */
    public function destroy(ObservationList $observationList)
    {
        //
    }
}
