<?php

namespace App\Http\Controllers;

use App\Models\CometObservationsOld;
use App\Models\User;

class CometDrawingController extends Controller
{
    public function index()
    {
        $sketches = CometObservationsOld::where('hasDrawing', 1)->orderBy('id', 'desc')->paginate(20);

        return view('cometdrawings.show', ['user' => '', 'sketches' => $sketches]);
    }

    public function show(string $slug)
    {
        $user = User::where('slug', $slug)->firstOrFail();
        $sketches = CometObservationsOld::where('observerid', $user->username)->where('hasDrawing', 1)
            ->orderBy('date', 'desc')->paginate(20);

        return view('cometdrawings.show', ['user' => $user, 'sketches' => $sketches]);
    }
}
