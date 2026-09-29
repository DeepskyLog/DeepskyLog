<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\User;

class DrawingController extends Controller
{
    /**
     * Fetches and displays a list of sketches.
     *
     * This method retrieves all observations that have a sketch associated with them, ordered by their ID in descending order.
     * The results are paginated, with 20 results per page.
     * The method then returns a view that displays these sketches.
     *
     * @return \Illuminate\View\View The view that displays the sketches.
     */
    public function index()
    {
        $obsType = request()->query('obs_type', 'all');
        $allowedTypes = ['all', 'deepsky', 'comet', 'solarsystem'];
        if (!in_array($obsType, $allowedTypes)) {
            $obsType = 'all';
        }

        $query = Observation::where('has_drawing', 1);
        if ($obsType === 'all') {
            $query->whereIn('target_type', ['deepsky', 'comet', 'planet', 'solarsystem', 'lunar']);
        } elseif ($obsType === 'solarsystem') {
            $query->whereIn('target_type', ['planet', 'solarsystem', 'lunar']);
        } else {
            $query->where('target_type', $obsType);
        }

        $sketches = $query->orderBy('id', 'desc')->deferredPaginate(20)->appends(request()->query());

        // Preload users to avoid N+1 queries in the view
        $observerIds = $sketches->pluck('observer_username')->unique()->all();
        $sketchUsers = User::whereIn('username', $observerIds)->get()->keyBy('username');

        return view('drawings.show', ['user' => '', 'sketches' => $sketches, 'sketchUsers' => $sketchUsers, 'obsType' => $obsType]);
    }

    /**
     * Fetches and displays a list of sketches for a specific user.
     *
     * This method retrieves the user with the given slug. It then retrieves all observations made by this user that have a sketch associated with them,
     * ordered by the date of the observation in descending order. The results are paginated, with 20 results per page.
     * The method then returns a view that displays these sketches.
     *
     * @param  string  $slug  The slug of the user for whom to fetch the sketches.
     * @return \Illuminate\View\View The view that displays the sketches.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no user with the given slug exists.
     */
    public function show(string $slug)
    {
        $user = User::where('slug', $slug)->firstOrFail();

        $obsType = request()->query('obs_type', 'all');
        $allowedTypes = ['all', 'deepsky', 'comet', 'solarsystem'];
        if (!in_array($obsType, $allowedTypes)) {
            $obsType = 'all';
        }

        $query = Observation::where('observer_username', $user->username)->where('has_drawing', 1);
        if ($obsType === 'all') {
            $query->whereIn('target_type', ['deepsky', 'comet', 'planet', 'solarsystem', 'lunar']);
        } elseif ($obsType === 'solarsystem') {
            $query->whereIn('target_type', ['planet', 'solarsystem', 'lunar']);
        } else {
            $query->where('target_type', $obsType);
        }

        $sketches = $query->orderBy('observed_on', 'desc')->paginate(20)->appends(request()->query());

        // Preload users to avoid N+1 queries in the view
        $observerIds = $sketches->pluck('observer_username')->unique()->all();
        $sketchUsers = User::whereIn('username', $observerIds)->get()->keyBy('username');

        return view('drawings.show', ['user' => $user, 'sketches' => $sketches, 'sketchUsers' => $sketchUsers, 'obsType' => $obsType]);
    }
}
