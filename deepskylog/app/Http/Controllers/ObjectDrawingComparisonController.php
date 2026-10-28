<?php

namespace App\Http\Controllers;

use App\Models\DeepskyObject;
use App\Models\Observation;
use App\Services\Observations\DrawingComparison;
use App\Services\Observations\ObservationCardData;
use Illuminate\Http\Request;

/**
 * The drawings of a deep-sky object side by side, per aperture class
 * (App\Services\Observations\DrawingComparison).
 */
class ObjectDrawingComparisonController extends Controller
{
    public function show(Request $request, string $slug, DrawingComparison $comparison)
    {
        $name = Observation::canonicalDeepskyName($slug);
        $object = $name !== null ? DeepskyObject::where('name', $name)->first() : null;
        abort_unless($object, 404);

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

        return view('object.drawings-compare', [
            'object' => $object,
            'classes' => $classes,
            'left' => $left,
            'right' => $right,
            'inches' => (bool) ($request->user()?->showInches ?? false),
            'backUrl' => route('object.show', ['slug' => $object->slug]),
        ]);
    }
}
