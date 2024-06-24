<?php

namespace App\Http\Controllers;

use App\Models\ObservationsOld;
use App\Models\User;

class DrawingController extends Controller
{
    public function index()
    {
        $sketches = ObservationsOld::where('hasDrawing', 1)->orderBy('id', 'desc')->paginate(20);

        return view('drawings.show', ['user' => '', 'sketches' => $sketches]);
    }

    public function show(string $slug)
    {
        $user = User::where('slug', $slug)->firstOrFail();
        $sketches = ObservationsOld::where('observerid', $user->username)
            ->where('hasDrawing', 1)->orderBy('date', 'desc')->paginate(20);

        return view('drawings.show', ['user' => $user, 'sketches' => $sketches]);
    }
}
