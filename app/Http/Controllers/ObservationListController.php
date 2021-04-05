<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ObservationList;
use Illuminate\Support\Facades\Auth;

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
     * @param ObservationList $list The observation list to fill out in the fields
     *
     * @return \Illuminate\Http\Response
     */
    public function create(ObservationList $list)
    {
        return view(
            'layout.observationList.create',
            ['observationList' => $list, 'update' => false]
        );
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
    public function show(String $slug)
    {
        $list  = \App\Models\ObservationList::where('slug', $slug)->first();

        if ($list->user_id != Auth::user()->id) {
            if ($list->discoverable == 0) {
                abort(401, _i('The requested observation list is not available.'));
            }
        }

        $user = $list->user;

        $media = $user->getFirstMedia('observer');

        return view(
            'layout.observationList.show',
            ['observationList' => $list, 'media' => $media]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param String $list The slug of the observation list to edit
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(String $slug)
    {
        $list  = \App\Models\ObservationList::where('slug', $slug)->first();

        return view(
            'layout.observationList.create',
            ['observationList' => $list, 'update' => true]
        );
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
