<?php

namespace App\Http\Controllers;

use App\Models\Eyepiece;
use App\Models\EyepiecesOld;
use App\Models\User;

class EyepieceController extends Controller
{
    public function index()
    {
        return view('eyepiece.index');
    }

    public function create()
    {
        return view('eyepiece.create', ['update' => false]);
    }

    public function show_from_user(string $user_id)
    {
        return EyepiecesOld::where('observer', $user_id)->get();
    }

    public function show(string $user_slug, string $eyepiece_slug)
    {
        $user_id = User::where('slug', $user_slug)->first()->id;
        $eyepiece = Eyepiece::where('slug', $eyepiece_slug)->where('user_id', $user_id)->first();

        // Check if there is an image for this eyepiece
        if ($eyepiece->picture != null) {
            $image = '/storage/'.asset($eyepiece->picture);
        } else {
            $image = '/images/eyepiece.png';
        }

        return view(
            'eyepiece.show',
            ['eyepiece' => $eyepiece, 'image' => $image]
        );
    }

    public function edit(string $user_slug, string $eyepiece_slug)
    {
        $user_id = User::where('slug', $user_slug)->first()->id;
        $eyepiece = Eyepiece::where('slug', $eyepiece_slug)->where('user_id', $user_id)->first();

        return view(
            'eyepiece.create',
            ['eyepiece' => $eyepiece, 'update' => true]
        );
    }
}
