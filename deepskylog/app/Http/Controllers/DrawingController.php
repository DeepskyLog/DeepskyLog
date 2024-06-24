<?php

namespace App\Http\Controllers;

use App\Models\CometObservationsOld;
use App\Models\ObservationsOld;
use App\Models\User;

class DrawingController extends Controller
{
    public function show(string $slug)
    {
        $user = User::where('slug', $slug)->firstOrFail();
        $sketches = ObservationsOld::where('observerid', $user->username)
            ->where('hasDrawing', 1)->orderBy('date', 'desc')->paginate(20);
        $cometSketches = CometObservationsOld::where('observerid', $user->username)->where('hasDrawing', 1)->get();

        return view('drawings.show', ['user' => $user, 'sketches' => $sketches]);
    }
}
