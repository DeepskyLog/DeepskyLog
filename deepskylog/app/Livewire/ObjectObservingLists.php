<?php

namespace App\Livewire;

use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Services\ActiveObservingListService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * The observing lists that contain an object, on the object page: the
 * observer's own lists (also the private ones) and the most popular public
 * lists of other observers.
 */
class ObjectObservingLists extends Component
{
    /**
     * The number of own and of public lists shown; the others are found on
     * the observer's list page and the discover page.
     */
    public const MAX_OWN = 10;

    public const MAX_PUBLIC = 10;

    public string $objectName;

    public function mount(string $objectName): void
    {
        $this->objectName = $objectName;
    }

    /**
     * Adding the object to or removing it from the active list changes the
     * lists shown here.
     */
    #[On('observing-list-item-toggled')]
    public function refreshLists(): void
    {
        // Rendering again reloads the lists.
    }

    /**
     * The lists that hold the object under the canonical name the items are
     * stored as.
     */
    protected function containing(): Builder
    {
        return ObservingList::query()
            ->whereIn('id', ObservingListItem::select('observing_list_id')->where('object_name', $this->objectName))
            ->withCount('items');
    }

    /**
     * @return array{own: Collection, ownTotal: int, public: Collection, publicTotal: int}
     */
    protected function lists(): array
    {
        $user = auth()->user();

        $own = collect();
        $ownTotal = 0;
        if ($user) {
            $ownQuery = $this->containing()->where('owner_user_id', $user->id);
            $ownTotal = (clone $ownQuery)->count();
            // The active list first, then the newest.
            $activeId = (int) app(ActiveObservingListService::class)->getActiveList($user)?->id;
            $own = $ownTotal === 0 ? $own : $ownQuery
                ->orderByRaw('id = ? desc', [$activeId])
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->limit(self::MAX_OWN)
                ->get();
        }

        $public = $this->containing()
            ->where('public', true)
            ->when($user, fn ($q) => $q->where('owner_user_id', '<>', $user->id));
        $publicTotal = (clone $public)->count();

        // The order of the popular lists on the discover page.
        $public = $publicTotal === 0 ? collect() : $public->with('owner:id,name,slug')
            ->orderBy('likes_count', 'desc')
            ->orderBy('comments_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(self::MAX_PUBLIC)
            ->get();

        return ['own' => $own, 'ownTotal' => $ownTotal, 'public' => $public, 'publicTotal' => $publicTotal];
    }

    public function render()
    {
        return view('livewire.object-observing-lists', $this->lists());
    }
}
