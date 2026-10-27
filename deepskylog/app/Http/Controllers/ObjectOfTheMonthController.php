<?php

namespace App\Http\Controllers;

use App\Models\ObjectOfTheMonth;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\ObjectsOfTheMonth\ObjectsInText;
use App\Services\Observations\ObservationCardData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * The objects of the month of Astroforum: /objects-of-the-month and its
 * pages with everyone's observations of the objects in the months, and
 * /admin/objects-of-the-month, where the administrators and database
 * experts confirm the objects astroforum:objects-of-the-month proposed.
 */
class ObjectOfTheMonthController extends Controller
{
    public const PER_PAGE = 12;

    public const OBSERVATIONS_PER_PAGE = 25;

    public const DRAWINGS = 12;

    public function index(): View
    {
        $items = ObjectOfTheMonth::published()->orderByDesc('starts_on')->orderByDesc('thread_id')->paginate(self::PER_PAGE);

        return view('objects-of-the-month.index', ['items' => $items]);
    }

    public function show(ObjectOfTheMonth $objectOfTheMonth): View
    {
        abort_unless($objectOfTheMonth->isPublished() || Gate::allows('manage_news', User::class), 404);

        $observations = $objectOfTheMonth->observationsQuery()->paginate(self::OBSERVATIONS_PER_PAGE);
        $drawings = $objectOfTheMonth->observationsQuery()->where('observations.has_drawing', true)->limit(self::DRAWINGS)->get();
        ObservationCardData::instance()->preload(collect($observations->items())->concat($drawings));

        return view('objects-of-the-month.show', [
            'item' => $objectOfTheMonth,
            'counts' => $objectOfTheMonth->counts(),
            'observations' => $observations,
            'drawings' => $drawings,
        ]);
    }

    public function storeList(Request $request, ObjectOfTheMonth $objectOfTheMonth): RedirectResponse
    {
        abort_unless($objectOfTheMonth->isPublished(), 404);
        $this->authorize('create', ObservingList::class);

        $name = __('Object of the month :period', ['period' => $objectOfTheMonth->periodLabel()]);
        $list = ObservingList::createWithObjects(
            $request->user(),
            $name,
            $objectOfTheMonth->listNames(),
            $objectOfTheMonth->subtitle()."\n".$objectOfTheMonth->url,
        );

        return redirect()->route('observing-list.show', $list)
            ->with('success', __('Observing list created with :count object(s).', ['count' => $list->items()->count()]));
    }

    public function admin(): View
    {
        $items = ObjectOfTheMonth::with('confirmer:id,name')->orderByDesc('starts_on')->orderByDesc('thread_id')->paginate(25);

        return view('admin.objects-of-the-month.index', ['items' => $items]);
    }

    public function edit(ObjectOfTheMonth $objectOfTheMonth): View
    {
        $confirmed = $objectOfTheMonth->confirmed_at !== null;

        // The confirmed objects that were not proposed (added by name) are offered as well
        $objects = collect($objectOfTheMonth->candidates ?? []);
        foreach ($objectOfTheMonth->objects ?? [] as $object) {
            if (! $objects->contains(fn (array $candidate) => ObjectOfTheMonth::key($candidate) === ObjectOfTheMonth::key($object))) {
                $objects->push($object);
            }
        }

        return view('admin.objects-of-the-month.form', [
            'item' => $objectOfTheMonth,
            'objects' => $objects->values(),
            'selected' => $confirmed
                ? collect($objectOfTheMonth->objects ?? [])->map(fn (array $object) => ObjectOfTheMonth::key($object))->all()
                : $objectOfTheMonth->proposedKeys(),
        ]);
    }

    public function update(Request $request, ObjectOfTheMonth $objectOfTheMonth, ObjectsInText $finder): RedirectResponse
    {
        $validated = $request->validate([
            'objects' => ['nullable', 'array'],
            'objects.*' => ['string'],
            'extra' => ['nullable', 'string', 'max:2000'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'hidden' => ['nullable', 'boolean'],
        ]);

        $offered = collect($objectOfTheMonth->candidates ?? [])->concat($objectOfTheMonth->objects ?? [])
            ->keyBy(fn (array $object) => ObjectOfTheMonth::key($object));
        $objects = collect($validated['objects'] ?? [])
            ->filter(fn (string $key) => $offered->has($key))
            ->map(fn (string $key) => array_diff_key($offered[$key], ['mentions' => true, 'in_title' => true]));

        $names = collect(preg_split('/[\r\n,;]+/', (string) ($validated['extra'] ?? '')))->map(fn ($name) => trim((string) $name))->filter()->unique()->values()->all();
        $unknown = [];
        foreach ($finder->resolve($names) as $name => $object) {
            if ($object === null) {
                $unknown[] = $name;
            } else {
                $objects->push($object);
            }
        }
        if ($unknown !== []) {
            return back()->withInput()->withErrors(['extra' => __('DeepskyLog does not know: :names', ['names' => implode(', ', $unknown)])]);
        }

        $objects = $objects->unique(fn (array $object) => ObjectOfTheMonth::key($object))->values();
        $objectOfTheMonth->fill([
            'objects' => $objects->all(),
            'starts_on' => $validated['starts_on'],
            'ends_on' => $validated['ends_on'],
            'hidden' => (bool) ($validated['hidden'] ?? false),
        ]);
        if ($objects->isEmpty()) {
            $objectOfTheMonth->confirmed_at = null;
            $objectOfTheMonth->confirmed_by = null;
        } elseif ($objectOfTheMonth->confirmed_at === null || $objectOfTheMonth->isDirty('objects')) {
            $objectOfTheMonth->confirmed_at = now();
            $objectOfTheMonth->confirmed_by = $request->user()->id;
        }
        $objectOfTheMonth->save();

        return redirect()->route('admin.objects-of-the-month')
            ->with('success', $objects->isEmpty()
                ? __('No objects chosen: the object of the month is not shown.')
                : __('The objects of the month are saved.'));
    }
}
