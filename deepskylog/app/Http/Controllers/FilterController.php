<?php

namespace App\Http\Controllers;

use App\Models\Filter;
use App\Models\FilterMake;
use App\Models\User;
use Illuminate\Http\Request;

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
        $user_id = User::where('slug', $user_slug)->first()->id;
        $filter = Filter::where('slug', $filter_slug)->where('user_id', $user_id)->first();

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
        $user_id = User::where('slug', $user_slug)->first()->id;
        $filter = Filter::where('slug', $filter_slug)->where('user_id', $user_id)->first();

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
        FilterMake::where('id', $request->id)->update(['name' => $request->filter_make]);

        return redirect()->route('filter.indexAdmin');
    }

    public function destroyMake(Request $request)
    {
        Filter::where('make_id', $request->id)->update(['make_id' => $request->new_make]);
        FilterMake::where('id', $request->id)->delete();

        return redirect()->route('filter.indexAdmin');
    }
}
