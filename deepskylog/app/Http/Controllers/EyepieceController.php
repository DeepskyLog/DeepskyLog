<?php

namespace App\Http\Controllers;

use App\Models\Eyepiece;
use App\Models\EyepieceMake;
use App\Models\EyepieceType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        return Eyepiece::where('observer', $user_id)->get();
    }

    public function show(string $user_slug, string $eyepiece_slug)
    {
        $user_id = User::where('slug', $user_slug)->firstOrFail()->id;
        $eyepiece = Eyepiece::where('slug', $eyepiece_slug)->where('user_id', $user_id)->firstOrFail();
        abort_unless($eyepiece->canBeEditedBy(Auth::user()), 403);

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
        $user_id = User::where('slug', $user_slug)->firstOrFail()->id;
        $eyepiece = Eyepiece::where('slug', $eyepiece_slug)->where('user_id', $user_id)->firstOrFail();

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
        $data = $request->validate([
            'id' => 'required|integer|not_in:1|exists:eyepiece_makes,id',
            'eyepiece_make' => ['required', 'string', 'max:255', Rule::unique('eyepiece_makes', 'name')->ignore($request->id)],
        ]);

        EyepieceMake::whereKey($data['id'])->update(['name' => trim($data['eyepiece_make'])]);

        return redirect()->route('eyepiece.indexAdmin');
    }

    public function destroyMake(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|not_in:1|exists:eyepiece_makes,id',
            'new_make' => 'required|integer|different:id|exists:eyepiece_makes,id',
        ]);

        DB::transaction(function () use ($data) {
            // Move the types of the deleted make to the new make, merging
            // them with a type of the same name the new make already has.
            foreach (EyepieceType::where('eyepiece_makes_id', $data['id'])->get() as $type) {
                $existing = EyepieceType::where('eyepiece_makes_id', $data['new_make'])
                    ->where('name', $type->name)->first();
                if ($existing) {
                    Eyepiece::where('type_id', $type->id)->update(['type_id' => $existing->id]);
                    $type->delete();
                } else {
                    $type->update(['eyepiece_makes_id' => $data['new_make']]);
                }
            }
            Eyepiece::where('make_id', $data['id'])->update(['make_id' => $data['new_make']]);
            EyepieceMake::whereKey($data['id'])->delete();
        });

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
