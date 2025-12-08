<?php

namespace App\Http\Controllers;

use App\Models\CometObservationsOld;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CometDrawingController extends Controller
{
    /**
     * Fetches and displays a list of comet sketches.
     *
     * This method retrieves all comet observations that have a sketch associated with them, ordered by their ID in descending order.
     * The results are paginated, with 20 results per page.
     * The method then returns a view that displays these sketches.
     *
     * @return \Illuminate\View\View The view that displays the sketches.
     */
    public function index()
    {
        $query = CometObservationsOld::where('hasDrawing', 1)->orderBy('id', 'desc');
        // Optional object filter via query param or path segment handled in routes
        try {
            if (request()->route('observer') && request()->route('object')) {
                // handled by showObserverObject when present
            }
            if ($obj = request()->query('object')) {
                // resolve slug to object id
                $objSlug = (string) $obj;
                $co = null;
                try {
                    $co = \App\Models\CometObject::where('slug', $objSlug)->orWhere('name', $objSlug)->first();
                } catch (\Throwable $_) {
                }
                if (! $co) {
                    try {
                        $row = DB::connection('mysqlOld')->table('cometobjects')->where('slug', $objSlug)->orWhere('name', $objSlug)->first();
                        if ($row) $query->where('objectid', $row->id);
                    } catch (\Throwable $_) {
                    }
                } else {
                    $query->where('objectid', $co->id);
                }
            }
        } catch (\Throwable $_) {
        }

        $sketches = $query->paginate(20);

        return view('cometdrawings.show', ['user' => '', 'sketches' => $sketches]);
    }

    /**
     * Show comet drawings for a specific observer filtered by object.
     * URL: /cometdrawings/{observer}/{object}
     */
    public function showObserverObject(string $observerSlug, string $objectSlug)
    {
        $user = User::where('slug', $observerSlug)->firstOrFail();

        // Resolve object slug to id
        $objectId = null;
        try {
            $co = \App\Models\CometObject::where('slug', $objectSlug)->orWhere('name', $objectSlug)->first();
            if ($co) $objectId = $co->id;
        } catch (\Throwable $_) {
        }
        if (! $objectId) {
            try {
                $row = DB::connection('mysqlOld')->table('cometobjects')->where('slug', $objectSlug)->orWhere('name', $objectSlug)->first();
                if ($row) $objectId = $row->id;
            } catch (\Throwable $_) {
            }
        }

        $query = CometObservationsOld::where('observerid', $user->username)->where('hasDrawing', 1)->orderBy('date', 'desc');
        if ($objectId) $query->where('objectid', $objectId);

        $sketches = $query->paginate(20);

        return view('cometdrawings.show', ['user' => $user, 'sketches' => $sketches]);
    }

    /**
     * Fetches and displays a list of comet sketches for a specific user.
     *
     * This method retrieves the user with the given slug. It then retrieves all comet observations made by this user that have a sketch associated with them,
     * ordered by the date of the observation in descending order. The results are paginated, with 20 results per page.
     * The method then returns a view that displays these sketches.
     *
     * @param  string  $slug  The slug of the user for whom to fetch the sketches.
     * @return \Illuminate\View\View The view that displays the sketches.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no user with the given slug exists.
     */
    public function show(string $slug)
    {
        // First try to resolve slug as a user (existing behaviour)
        $user = User::where('slug', $slug)->first();
        if ($user) {
            $sketches = CometObservationsOld::where('observerid', $user->username)->where('hasDrawing', 1)
                ->orderBy('date', 'desc')->paginate(20);

            return view('cometdrawings.show', ['user' => $user, 'sketches' => $sketches]);
        }

        // Not a user: try to resolve slug to an object and show object-scoped drawings
        $objectSlug = $slug;
        $objectId = null;
        try {
            $co = \App\Models\CometObject::where('slug', $objectSlug)->orWhere('name', $objectSlug)->first();
            if ($co) $objectId = $co->id;
        } catch (\Throwable $_) {
        }
        if (! $objectId) {
            try {
                $row = DB::connection('mysqlOld')->table('cometobjects')->where('slug', $objectSlug)->orWhere('name', $objectSlug)->first();
                if ($row) $objectId = $row->id;
            } catch (\Throwable $_) {
            }
        }

        $query = CometObservationsOld::where('hasDrawing', 1)->orderBy('id', 'desc');
        if ($objectId) $query->where('objectid', $objectId);
        $sketches = $query->paginate(20);

        $fakeUser = (object) ['name' => $objectSlug, 'slug' => $objectSlug, 'username' => null];
        return view('cometdrawings.show', ['user' => $fakeUser, 'sketches' => $sketches]);
    }
}
