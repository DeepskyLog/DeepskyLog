<?php

namespace App\Livewire;

use App\Models\ObservingList;
use App\Models\ObservingListItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class ObservingListNotes extends Component
{
    #[Locked]
    public int $listId;

    #[Locked]
    public string $listSlug = '';

    #[Locked]
    public bool $isOwner = false;

    /** @var array<int, string> */
    public array $visibleObjectNames = [];
    public bool $syncedWithTable = false;

    public function mount(int $listId): void
    {
        $list = ObservingList::findOrFail($listId);

        // Guests have no user for the policy: they may see public lists only.
        abort_unless(
            Auth::check() ? Gate::allows('view', $list) : (bool) $list->public,
            403
        );

        $this->listId = $list->id;
        $this->listSlug = (string) $list->slug;
        $this->isOwner = Auth::id() !== null && Auth::id() === $list->owner_user_id;
    }

    #[On('observing-list-table-visible-objects-updated')]
    public function syncVisibleObjects(int $listId, array $objectNames = []): void
    {
        if ($listId !== $this->listId) {
            return;
        }

        $this->syncedWithTable = true;
        $this->visibleObjectNames = collect($objectNames)
            ->map(fn($name) => trim((string) $name))
            ->filter(fn($name) => $name !== '')
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.observing-list-notes', [
            'notes' => $this->resolveVisibleNotes(),
            'syncedWithTable' => $this->syncedWithTable,
        ]);
    }

    private function resolveVisibleNotes(): Collection
    {
        if (!$this->syncedWithTable || empty($this->visibleObjectNames)) {
            return collect();
        }

        $query = ObservingListItem::query()
            ->where('observing_list_id', $this->listId)
            ->whereIn('object_name', $this->visibleObjectNames);

        if (!$this->isOwner) {
            $query->whereNotNull('item_description')
                ->where('item_description', '<>', '');
        }

        $itemsByName = $query->get()->keyBy(function (ObservingListItem $item) {
            return mb_strtolower(trim((string) $item->object_name));
        });

        return collect($this->visibleObjectNames)
            ->map(function (string $name) use ($itemsByName) {
                return $itemsByName->get(mb_strtolower($name));
            })
            ->filter()
            ->values();
    }
}
