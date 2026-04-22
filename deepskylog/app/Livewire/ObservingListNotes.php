<?php

namespace App\Livewire;

use App\Models\ObservingListItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class ObservingListNotes extends Component
{
    public int $listId;
    public string $listSlug = '';
    public bool $isOwner = false;

    /** @var array<int, string> */
    public array $visibleObjectNames = [];
    public bool $syncedWithTable = false;

    public function mount(int $listId, string $listSlug = '', bool $isOwner = false): void
    {
        $this->listId = $listId;
        $this->listSlug = $listSlug;
        $this->isOwner = $isOwner;
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
