<?php

namespace App\Http\Controllers;

use App\Models\DeepskyObject;
use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\DrawingComparison;
use App\Services\Observations\ObservationCardData;
use App\Services\Observations\ObserverDrawings;
use Illuminate\Http\Request;

/**
 * The drawings of a deep-sky object side by side, per aperture class
 * (App\Services\Observations\DrawingComparison), and those of one observer
 * oldest to newest (App\Services\Observations\ObserverDrawings).
 */
class ObjectDrawingComparisonController extends Controller
{
    public function show(Request $request, string $slug, DrawingComparison $comparison, ObserverDrawings $observerDrawings)
    {
        $object = $this->object($slug);

        $classes = $comparison->forObject($object->name);
        [$left, $right] = DrawingComparison::defaultPair($classes);
        foreach (['left', 'right'] as $side) {
            $requested = $request->query($side);
            if (is_string($requested) && isset($classes[$requested])) {
                $$side = $requested;
            }
        }

        $shown = collect([$left, $right])->filter()->unique()
            ->flatMap(fn ($key) => $classes[$key]->take(DrawingComparison::PER_CLASS));
        ObservationCardData::instance()->preload($shown);

        $observers = $observerDrawings->observersWithSeveral($object->name);
        $observerUsers = User::whereIn('username', array_keys($observers))->get(['id', 'username', 'name', 'slug'])->keyBy('username');

        return view('object.drawings-compare', [
            'observers' => $observers,
            'observerUsers' => $observerUsers,
            'object' => $object,
            'classes' => $classes,
            'left' => $left,
            'right' => $right,
            'inches' => (bool) ($request->user()?->showInches ?? false),
            'backUrl' => route('object.show', ['slug' => $object->slug]),
        ]);
    }

    /** The drawings of the object by one observer, oldest to newest. */
    public function observer(Request $request, string $slug, string $observer, ObserverDrawings $observerDrawings)
    {
        $object = $this->object($slug);
        $user = User::where('slug', $observer)->firstOrFail();

        $drawings = $observerDrawings->forObserver($object->name, $user);
        ObservationCardData::instance()->preload($drawings);

        return view('object.drawings-observer', [
            'object' => $object,
            'observer' => $user,
            'drawings' => $drawings,
            'inches' => (bool) ($request->user()?->showInches ?? false),
            'backUrl' => route('object.show', ['slug' => $object->slug]),
        ]);
    }

    private function object(string $slug): DeepskyObject
    {
        $name = Observation::canonicalDeepskyName($slug);
        $object = $name !== null ? DeepskyObject::where('name', $name)->first() : null;
        abort_unless($object, 404);

        return $object;
    }
}
