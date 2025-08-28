<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;

class LocationController extends Controller
{
    public function index()
    {
        return view('location.index');
    }

    public function create()
    {
        return view('location.create', ['update' => false]);
    }

    public function show_from_user(string $user_id)
    {
        return Location::where('observer', $user_id)->get();
    }

    public function show(string $user_slug, string $location_slug)
    {
        $user_id = User::where('slug', $user_slug)->first()->id;
        $location = Location::where('slug', $location_slug)->where('user_id', $user_id)->first();

        // Check if there is an image for this location
        if ($location->picture != null) {
            $image = '/storage/'.asset($location->picture);
        } else {
            $image = '/images/location.png';
        }

        return view(
            'location.show',
            ['location' => $location, 'image' => $image]
        );
    }

    public function edit(string $user_slug, string $location_slug)
    {
        $user_id = User::where('slug', $user_slug)->first()->id;
        $location = Location::where('slug', $location_slug)->where('user_id', $user_id)->first();

        return view(
            'location.create',
            ['location' => $location, 'update' => true]
        );
    }
}
