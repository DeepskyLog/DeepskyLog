<?php

namespace App\Http\Controllers;

use App\Models\InstrumentSet;
use App\Models\User;

class InstrumentSetController extends Controller
{
    public function index()
    {
        return view('instrumentset.index');
    }

    public function create()
    {
        return view('instrumentset.create', ['update' => false]);
    }

    public function show(string $user_slug, string $set_slug)
    {
        $user_id = User::where('slug', $user_slug)->first()->id;
        $set = InstrumentSet::where('slug', $set_slug)->where('user_id', $user_id)->first();

        return view('instrumentset.show', ['set' => $set]);
    }

    public function edit(string $user_slug, string $set_slug)
    {
        $user_id = User::where('slug', $user_slug)->first()->id;
        $set = InstrumentSet::where('slug', $set_slug)->where('user_id', $user_id)->first();

        return view('instrumentset.create', ['set' => $set, 'update' => true]);
    }

    public function indexAdmin()
    {
        return view('instrumentset-admin.index');
    }
}
