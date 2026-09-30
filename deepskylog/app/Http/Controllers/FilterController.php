<?php

namespace App\Http\Controllers;

use App\Models\Filter;
use App\Models\FilterMake;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FilterController extends Controller
{
    public function index()
    {
        return view('filter.index');
    }

    public function create()
    {
        return view('filter.create', ['update' => false]);
    }

    public function show_from_user(string $user_id)
    {
        return Filter::where('observer', $user_id)->get();
    }

    public function show(string $user_slug, string $filter_slug)
    {
        $user_id = User::where('slug', $user_slug)->firstOrFail()->id;
        $filter = Filter::where('slug', $filter_slug)->where('user_id', $user_id)->firstOrFail();
        abort_unless($filter->canBeEditedBy(Auth::user()), 403);

        // Check if there is an image for this filter
        if ($filter->picture != null) {
            $image = '/storage/'.asset($filter->picture);
        } else {
            $image = '/images/filter.png';
        }

        return view(
            'filter.show',
            ['filter' => $filter, 'image' => $image]
        );
    }

    public function edit(string $user_slug, string $filter_slug)
    {
        $user_id = User::where('slug', $user_slug)->firstOrFail()->id;
        $filter = Filter::where('slug', $filter_slug)->where('user_id', $user_id)->firstOrFail();

        return view(
            'filter.create',
            ['filter' => $filter, 'update' => true]
        );
    }

    public function indexAdmin()
    {
        return view('filter-admin.index');
    }

    public function editMake(FilterMake $make)
    {
        return view('filter-admin.edit-make', ['make' => $make]);
    }

    public function storeMake(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|not_in:1|exists:filter_makes,id',
            'filter_make' => ['required', 'string', 'max:255', Rule::unique('filter_makes', 'name')->ignore($request->id)],
        ]);

        FilterMake::whereKey($data['id'])->update(['name' => trim($data['filter_make'])]);

        return redirect()->route('filter.indexAdmin');
    }

    public function destroyMake(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|not_in:1|exists:filter_makes,id',
            'new_make' => 'required|integer|different:id|exists:filter_makes,id',
        ]);

        DB::transaction(function () use ($data) {
            Filter::where('make_id', $data['id'])->update(['make_id' => $data['new_make']]);
            FilterMake::whereKey($data['id'])->delete();
        });

        return redirect()->route('filter.indexAdmin');
    }
}
