<?php

namespace App\Http\Controllers;

use App\Models\ObservationsOld;
use App\Models\CometObservationsOld;
use App\Models\User;
use Illuminate\Http\Request;

class ObservationsController extends Controller
{
    /**
     * Show global list of observations (both deepsky and comet) paginated.
     */
    public function index()
    {
        // Default observations page shows deepsky observations (mirrors /drawings behavior)
        $deepsky = ObservationsOld::orderBy('id', 'desc')->paginate(20, ['*'], 'deepsky');

        return view('observations.show', [
            'user' => '',
            'deepsky' => $deepsky,
            'comet' => collect(),
            'mode' => 'deepsky',
        ]);
    }

    /**
     * Show observations for a specific observer (both deepsky and comet).
     */
    public function show(string $slug)
    {
        $user = User::where('slug', $slug)->firstOrFail();

        $deepsky = ObservationsOld::where('observerid', $user->username)->orderBy('date', 'desc')->paginate(20, ['*'], 'deepsky');

        return view('observations.show', [
            'user' => $user,
            'deepsky' => $deepsky,
            'comet' => collect(),
            'mode' => 'deepsky',
        ]);
    }

    /**
     * Show comet-only observations (global)
     */
    public function cometIndex()
    {
        $comet = CometObservationsOld::orderBy('id', 'desc')->paginate(20, ['*'], 'comet');

        return view('observations.show', [
            'user' => '',
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
        ]);
    }

    /**
     * Show comet-only observations for a specific observer
     */
    public function cometShow(string $slug)
    {
        $user = User::where('slug', $slug)->firstOrFail();

        $comet = CometObservationsOld::where('observerid', $user->username)->orderBy('date', 'desc')->paginate(20, ['*'], 'comet');

        return view('observations.show', [
            'user' => $user,
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
        ]);
    }
}
