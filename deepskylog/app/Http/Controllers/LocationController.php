<?php

namespace App\Http\Controllers;

use App\Charts\SkyQualityHistoryChart;
use App\Models\Location;
use App\Models\User;
use App\Services\LocationSkyHistory;
use App\Services\Weather\NightForecast;
use Illuminate\Support\Facades\Auth;

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
        $user_id = User::where('slug', $user_slug)->firstOrFail()->id;
        $location = Location::where('slug', $location_slug)->where('user_id', $user_id)->firstOrFail();

        // Check if there is an image for this location
        if ($location->picture != null) {
            $image = '/storage/'.asset($location->picture);
        } else {
            $image = '/images/location.png';
        }

        // The cloud forecast of the coming nights, for whoever may see the location, once Open-Meteo answered
        $nightForecast = null;
        if (! $location->hidden || Auth::id() === $location->user_id || Auth::user()?->isAdministrator()) {
            try {
                $nightForecast = NightForecast::forLocation($location, fetch: false);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // The SQM and NELM its owner recorded with the observations at the location over the years, the NELM with their correction
        $skyHistory = LocationSkyHistory::forLocation($location);
        $skyQualityChart = $skyHistory->isEmpty()
            ? null
            : (new SkyQualityHistoryChart)->build($location->name, $skyHistory, (float) ($location->user->fstOffset ?? 0));

        return view(
            'location.show',
            [
                'location' => $location,
                'image' => $image,
                'nightForecast' => $nightForecast,
                'skyHistory' => $skyHistory,
                'skyQualityChart' => $skyQualityChart,
            ]
        );
    }

    public function edit(string $user_slug, string $location_slug)
    {
        $user_id = User::where('slug', $user_slug)->firstOrFail()->id;
        $location = Location::where('slug', $location_slug)->where('user_id', $user_id)->firstOrFail();
        abort_unless($location->canBeEditedBy(Auth::user()), 403);

        return view(
            'location.create',
            ['location' => $location, 'update' => true]
        );
    }
}
