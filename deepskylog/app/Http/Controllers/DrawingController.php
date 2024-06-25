<?php

namespace App\Http\Controllers;

use App\Models\ObservationsOld;
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
        $sketches = ObservationsOld::where('hasDrawing', 1)->orderBy('id', 'desc')->paginate(20);

        return view('drawings.show', ['user' => '', 'sketches' => $sketches]);
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
        $sketches = ObservationsOld::where('observerid', $user->username)
            ->where('hasDrawing', 1)->orderBy('date', 'desc')->paginate(20);

        return view('drawings.show', ['user' => $user, 'sketches' => $sketches]);
    }
}
