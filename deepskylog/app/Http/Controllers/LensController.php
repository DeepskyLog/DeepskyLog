<?php

namespace App\Http\Controllers;

use App\Models\Lens;
use App\Models\LensMake;
use App\Models\User;
use Illuminate\Http\Request;

class LensController extends Controller
{
    public function index()
    {
        return view('lens.index');
    }

    public function create()
    {
        return view('lens.create', ['update' => false]);
    }

    public function show_from_user(string $user_id)
    {
        return Lens::where('observer', $user_id)->get();
    }

    public function show(string $user_slug, string $lens_slug)
    {
        $user_id = User::where('slug', $user_slug)->first()->id;
        $lens = Lens::where('slug', $lens_slug)->where('user_id', $user_id)->first();

        // Check if there is an image for this lens
        if ($lens->picture != null) {
            $image = '/storage/'.asset($lens->picture);
        } else {
            $image = '/images/lens.png';
        }

        return view(
            'lens.show',
            ['lens' => $lens, 'image' => $image]
        );
    }

    public function edit(string $user_slug, string $lens_slug)
    {
        $user_id = User::where('slug', $user_slug)->first()->id;
        $lens = Lens::where('slug', $lens_slug)->where('user_id', $user_id)->first();

        return view(
            'lens.create',
            ['lens' => $lens, 'update' => true]
        );
    }

    public function indexAdmin()
    {
        return view('lens-admin.index');
    }

    public function editMake(LensMake $make)
    {
        return view('lens-admin.edit-make', ['make' => $make]);
    }

    public function storeMake(Request $request)
    {
        LensMake::where('id', $request->id)->update(['name' => $request->lens_make]);

        return redirect()->route('lens.indexAdmin');
    }

    public function destroyMake(Request $request)
    {
        Lens::where('make_id', $request->id)->update(['make_id' => $request->new_make]);
        LensMake::where('id', $request->id)->delete();

        return redirect()->route('lens.indexAdmin');
    }
}
