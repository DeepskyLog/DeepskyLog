<?php

namespace App\Http\Controllers;

use App\Models\Eyepiece;
use App\Models\EyepieceMake;
use App\Models\EyepiecesOld;
use App\Models\EyepieceType;
use App\Models\User;
use Illuminate\Http\Request;

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

    public function indexAdmin()
    {
        return view('eyepiece-admin.index');
    }

    public function editMake(EyepieceMake $make)
    {
        return view('eyepiece-admin.edit-make', ['make' => $make]);
    }

    public function storeMake(Request $request)
    {
        EyepieceMake::where('id', $request->id)->update(['name' => $request->eyepiece_make]);

        return redirect()->route('eyepiece.indexAdmin');
    }

    public function destroyMake(Request $request)
    {
        Eyepiece::where('make_id', $request->id)->update(['type_id' => 1]);
        Eyepiece::where('make_id', $request->id)->update(['make_id' => $request->new_make]);
        EyepieceType::where('eyepiece_makes_id', $request->id)->delete();
        EyepieceMake::where('id', $request->id)->delete();

        return redirect()->route('eyepiece.indexAdmin');
    }

    public function indexTypeAdmin()
    {
        return view('eyepiece-admin.index-type');
    }

    public function editType(EyepieceType $type)
    {
        return view('eyepiece-admin.edit-type', ['type' => $type]);
    }

    public function storeType(Request $request)
    {
        EyepieceType::where('id', $request->id)->update(['name' => $request->eyepiece_type]);

        return redirect()->route('eyepiece.index-typeAdmin');
    }

    public function destroyType(Request $request)
    {
        Eyepiece::where('type_id', $request->id)->update(['type_id' => 1]);
        EyepieceType::where('id', $request->id)->delete();

        return redirect()->route('eyepiece.index-typeAdmin');
    }
}
