<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Http\Controllers\Livewire\TeamController;
use Laravel\Jetstream\Jetstream;

class DeepskyLogTeamController extends TeamController
{
    /**
     * Show the team management screen.
     *
     * @param  string  $teamSlug
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $teamSlug)
    {
        $team = Jetstream::newTeamModel()->where('slug', $teamSlug)->firstOrFail();

        if (Gate::denies('view', $team)) {
            abort(403);
        }

        return view('teams.show', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }
}
