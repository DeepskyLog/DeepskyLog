<?php

namespace App\Livewire;

use App\Models\ObservingGoal;
use App\Models\User;
use App\Services\ObjectSources;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;
use Livewire\Component;

/**
 * The observing goals on the observer page, with their progress. The
 * observer adds, edits and removes their own goals here; everybody else sees
 * the public ones.
 */
class ObservingGoals extends Component
{
    public int $observerId;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $source = 'catalog:m';

    /** The number of objects, empty for all objects of the source. */
    public string $target = '';

    public string $mode = 'observed';

    public bool $newOnly = false;

    public string $startsOn = '';

    public string $endsOn = '';

    public bool $public = true;

    public function mount(User $observer): void
    {
        $this->observerId = $observer->id;
    }

    private function observer(): User
    {
        return User::findOrFail($this->observerId);
    }

    private function authorizeOwner(): User
    {
        $user = Auth::user();
        abort_unless($user !== null && $user->id === $this->observerId, 403);

        return $user;
    }

    public function create(): void
    {
        $this->authorizeOwner();
        $this->resetForm();
        $this->period('year');
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $goal = ObservingGoal::where('user_id', $this->authorizeOwner()->id)->findOrFail($id);

        $this->resetValidation();
        $this->editingId = $goal->id;
        $this->name      = (string) $goal->name;
        $this->source    = $goal->source()['key'] ?? '';
        $this->target    = $goal->target_count === null ? '' : (string) $goal->target_count;
        $this->mode      = $goal->mode;
        $this->newOnly   = $goal->new_only;
        $this->startsOn  = $goal->starts_on->toDateString();
        $this->endsOn    = $goal->ends_on->toDateString();
        $this->public    = $goal->public;
        $this->showForm  = true;
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    /**
     * Fill in the period: this year, next year or the coming twelve months.
     */
    public function period(string $period): void
    {
        $today = Carbon::today();
        [$start, $end] = match ($period) {
            'next_year' => [$today->copy()->addYear()->startOfYear(), $today->copy()->addYear()->endOfYear()],
            'twelve_months' => [$today->copy(), $today->copy()->addYear()->subDay()],
            default => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
        };
        $this->startsOn = $start->toDateString();
        $this->endsOn   = $end->toDateString();
    }

    public function save(): void
    {
        $user = $this->authorizeOwner();
        $goal = $this->editingId !== null ? ObservingGoal::where('user_id', $user->id)->findOrFail($this->editingId) : null;

        $source = ObjectSources::find($this->source, $user);
        // An edited goal keeps the list of another observer that is no longer subscribed to.
        if ($source === null && $goal !== null && $goal->source()['key'] === $this->source) {
            $source = $goal->source() + ['short_label' => $goal->source_label];
        }

        $this->withValidator(function (Validator $validator) use ($user, $goal, $source) {
            $validator->after(function (Validator $validator) use ($user, $goal, $source) {
                if ($source === null) {
                    $validator->errors()->add('source', __('Choose a catalog or observing list.'));
                }
                if ($goal === null && ObservingGoal::where('user_id', $user->id)->count() >= ObservingGoal::MAX_PER_USER) {
                    $validator->errors()->add('name', __('You can have at most :count observing goals.', ['count' => ObservingGoal::MAX_PER_USER]));
                }
            });
        })->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'source' => ['required', 'string'],
            'target' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'mode' => ['required', 'in:observed,drawn'],
            'startsOn' => ['required', 'date_format:Y-m-d'],
            'endsOn' => ['required', 'date_format:Y-m-d', 'after_or_equal:startsOn', 'before_or_equal:' . Carbon::parse($this->startsOn ?: 'today')->addYears(ObservingGoal::MAX_YEARS)->toDateString()],
        ], [], [
            'startsOn' => __('Start'),
            'endsOn' => __('End'),
            'target' => __('Number of objects'),
        ]);

        $attributes = [
            'name' => trim($this->name) !== '' ? trim($this->name) : null,
            'catalog' => $source['type'] === 'catalog' ? $source['catalog'] : null,
            'observing_list_id' => $source['type'] === 'list' ? $source['list_id'] : null,
            'source_label' => (string) ($source['short_label'] ?? $source['label'] ?? ''),
            'target_count' => $this->target === '' ? null : (int) $this->target,
            'mode' => $this->mode,
            'new_only' => $this->newOnly,
            'starts_on' => $this->startsOn,
            'ends_on' => $this->endsOn,
            'public' => $this->public,
        ];

        if ($goal !== null) {
            $goal->update($attributes);
        } else {
            ObservingGoal::create(['user_id' => $user->id] + $attributes);
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        ObservingGoal::where('user_id', $this->authorizeOwner()->id)->whereKey($id)->first()?->delete();
        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    private function resetForm(): void
    {
        $this->reset(['showForm', 'editingId', 'name', 'source', 'target', 'mode', 'newOnly', 'startsOn', 'endsOn', 'public']);
        $this->resetValidation();
    }

    public function render()
    {
        $observer = $this->observer();
        $viewer   = Auth::user();
        $isOwner  = $viewer !== null && $viewer->id === $observer->id;

        $sources = collect();
        if ($isOwner && $this->showForm) {
            $sources = ObjectSources::options($viewer);
            // The list of an edited goal the observer no longer subscribed to
            $goal = $this->editingId !== null ? ObservingGoal::find($this->editingId) : null;
            if ($goal !== null && $goal->source() !== null && ! $sources->contains('key', $goal->source()['key'])) {
                $sources->push($goal->source() + ['label' => html_entity_decode($goal->source_label, ENT_QUOTES | ENT_HTML5, 'UTF-8')]);
            }
        }

        return view('livewire.observing-goals', [
            'observer' => $observer,
            'isOwner' => $isOwner,
            'goals' => ObservingGoal::shownFor($observer, $viewer),
            // The options of the searchable select, with the kind of source under each
            'sources' => $sources->map(fn (array $option) => [
                'key' => $option['key'],
                'label' => $option['label'],
                'kind' => $option['type'] === 'list' ? __('Observing list') : __('Catalog'),
            ])->values()->all(),
        ]);
    }
}
