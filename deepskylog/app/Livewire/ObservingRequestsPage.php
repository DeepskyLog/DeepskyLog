<?php

namespace App\Livewire;

use App\Models\ObservingList;
use App\Models\ObservingRequest;
use App\Services\ObservingRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Observing requests (/observing-requests): the open requests the
 * observer's standard telescope can probably answer (`for-me`, the default),
 * all open requests (`all`), and the observer's own with their answers
 * (`mine`). The requests for the telescope can be made an observing list.
 */
class ObservingRequestsPage extends Component
{
    public const VIEWS = ['for-me', 'all', 'mine'];

    #[Url]
    public string $view = 'for-me';

    public function mount(): void
    {
        if (! in_array($this->view, self::VIEWS, true)) {
            $this->view = 'for-me';
        }
    }

    public function closeRequest(int $id): void
    {
        $request = ObservingRequest::findOrFail($id);
        abort_unless($request->canBeClosedBy(Auth::user()), 403);

        $request->close();
    }

    public function createList()
    {
        $names = ObservingRequests::forViewer(Auth::user())->pluck('object_name')->unique()->values()->all();
        if ($names === []) {
            return null;
        }
        $list = ObservingList::createWithObjects(Auth::user(), __('Observing requests'), $names, __('Objects other observers asked to observe, that my telescope can probably show.'));

        return redirect()->route('observing-list.show', $list);
    }

    public function render()
    {
        $user = Auth::user();
        $requests = match ($this->view) {
            'all' => ObservingRequest::open()->with('user:id,name,slug,username')->withCount('answers')->orderByDesc('created_at')->get(),
            'mine' => ObservingRequest::where('user_id', $user->id)
                ->with(['answers' => fn ($q) => $q->limit(50)])
                ->withCount('answers')
                ->orderByRaw('closed_at is not null')
                ->orderByDesc('created_at')
                ->get(),
            default => ObservingRequests::forViewer($user),
        };

        $slugs = DB::table('objects')
            ->whereIn('name', $requests->pluck('object_name')->unique()->all())
            ->pluck('slug', 'name');

        // of the answers: whether the object was seen, and the aperture
        $answerIds = $this->view === 'mine' ? $requests->flatMap->answers->pluck('id')->all() : [];
        $answers = $answerIds === [] ? collect() : DB::table('observations as o')
            ->leftJoin('deepsky_observation_details as d', 'd.observation_id', '=', 'o.id')
            ->leftJoin('instruments as i', 'i.id', '=', 'o.instrument_id')
            ->leftJoin('users as u', 'u.username', '=', 'o.observer_username')
            ->whereIn('o.id', $answerIds)
            ->get(['o.id', 'd.visibility', 'i.aperture_mm', 'u.name', 'u.slug'])
            ->keyBy('id');

        return view('livewire.observing-requests-page', [
            'requests' => $requests,
            'answerDetails' => $answers,
            'slugs' => $slugs,
            'user' => $user,
            'hasEquipment' => $user->stdtelescope && $user->stdlocation,
        ]);
    }
}
