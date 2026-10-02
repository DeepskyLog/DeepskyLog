<?php

namespace App\Http\Controllers;

use App\Models\Lens;
use App\Models\LensMake;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        $user_id = User::where('slug', $user_slug)->firstOrFail()->id;
        $lens = Lens::where('slug', $lens_slug)->where('user_id', $user_id)->firstOrFail();

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
        $user_id = User::where('slug', $user_slug)->firstOrFail()->id;
        $lens = Lens::where('slug', $lens_slug)->where('user_id', $user_id)->firstOrFail();
        abort_unless($lens->canBeEditedBy(Auth::user()), 403);

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
        $data = $request->validate([
            'id' => 'required|integer|not_in:1|exists:lens_makes,id',
            'lens_make' => ['required', 'string', 'max:255', Rule::unique('lens_makes', 'name')->ignore($request->id)],
        ]);

        LensMake::whereKey($data['id'])->update(['name' => trim($data['lens_make'])]);

        return redirect()->route('lens.indexAdmin');
    }

    public function destroyMake(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|not_in:1|exists:lens_makes,id',
            'new_make' => 'required|integer|different:id|exists:lens_makes,id',
        ]);

        DB::transaction(function () use ($data) {
            Lens::where('make_id', $data['id'])->update(['make_id' => $data['new_make']]);
            LensMake::whereKey($data['id'])->delete();
        });

        return redirect()->route('lens.indexAdmin');
    }
}
