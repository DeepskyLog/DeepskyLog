<?php

namespace App\Livewire;

use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Services\ActiveObservingListService;
use Livewire\Component;

class ObservingListToggle extends Component
{
    public string $objectName;
    public bool $showToggle = true;
    public bool $showNote = false;
    public ?ObservingList $activeList = null;
    public bool $inList = false;
    public ?int $itemId = null;
    public ?string $itemDescription = null;

    public function mount(string $objectName, bool $showToggle = true, bool $showNote = false): void
    {
        $this->objectName = $objectName;
        $this->showToggle = $showToggle;
        $this->showNote = $showNote;
        $this->refresh();
    }

    /**
     * Reload active list state from DB.
     */
    protected function refresh(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        /** @var ActiveObservingListService $svc */
        $svc = app(ActiveObservingListService::class);
        $this->activeList = $svc->getActiveList($user);

        if ($this->activeList) {
            $item = ObservingListItem::where('observing_list_id', $this->activeList->id)
                ->where('object_name', $this->objectName)
                ->first();
            $this->inList = (bool) $item;
            $this->itemId = $item?->id;
            $this->itemDescription = $item?->item_description;
        } else {
            $this->inList = false;
            $this->itemId = null;
            $this->itemDescription = null;
        }
    }

    /**
     * Normalized plain-text version of the active-list note for this object.
     */
    public function getNormalizedItemDescriptionProperty(): string
    {
        if (empty($this->itemDescription)) {
            return '';
        }

        $text = html_entity_decode($this->itemDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text) ?? $text;
        $text = strip_tags($text);

        return trim($text);
    }

    /**
     * Toggle the object in/out of the active observing list.
     */
    public function toggle(): void
    {
        $user = auth()->user();
        if (!$user || !$this->activeList) {
            return;
        }

        // Re-authorize that user can still add/remove items on this list
        if (!$user->can('addItem', $this->activeList)) {
            session()->flash('error', __('You cannot modify this list.'));
            return;
        }

        if ($this->inList && $this->itemId) {
            ObservingListItem::where('id', $this->itemId)
                ->where('observing_list_id', $this->activeList->id)
                ->delete();
        } else {
            ObservingListItem::firstOrCreate(
                [
                    'observing_list_id' => $this->activeList->id,
                    'object_name' => $this->objectName,
                ],
                [
                    'source_mode' => 'manual',
                    'added_by_user_id' => $user->id,
                ]
            );
        }

        $this->refresh();
    }

    public function render()
    {
        return view('livewire.observing-list-toggle');
    }
}
