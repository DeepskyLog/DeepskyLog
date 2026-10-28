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
    public ?string $observationDescription = null;
    public ?int $observationId = null;
    public ?ObservingList $activeList = null;
    public bool $ownsActiveList = false;
    public bool $inList = false;
    public ?int $itemId = null;
    public ?string $itemDescription = null;
    public bool $noteReplaced = false;

    public function mount(
        string $objectName,
        bool $showToggle = true,
        bool $showNote = false,
        ?string $observationDescription = null,
        ?int $observationId = null,
    ): void {
        $this->objectName = $objectName;
        $this->showToggle = $showToggle;
        $this->showNote = $showNote;
        $this->observationDescription = $observationDescription;
        $this->observationId = $observationId;
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
        $this->ownsActiveList = (bool) ($this->activeList && (int) $this->activeList->owner_user_id === (int) $user->id);

        if ($this->activeList && $this->ownsActiveList) {
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
     * Build a plain-text note from the observation description.
     */
    protected function getSanitizedObservationNote(): ?string
    {
        if (empty($this->observationDescription)) {
            return null;
        }

        $text = html_entity_decode($this->observationDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text) ?? $text;
        $text = strip_tags($text);
        $text = preg_replace("/\r\n?|\n/", "\n", $text) ?? $text;
        $text = trim($text);

        return $text !== '' ? $text : null;
    }

    /**
     * Toggle the object in/out of the active observing list.
     */
    public function toggle(): void
    {
        $user = auth()->user();
        if (!$user || !$this->activeList || !$this->ownsActiveList) {
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
            $sanitizedNote = $this->getSanitizedObservationNote();

            ObservingListItem::firstOrCreate(
                [
                    'observing_list_id' => $this->activeList->id,
                    'object_name' => $this->objectName,
                ],
                [
                    'item_description' => $sanitizedNote,
                    'source_mode' => $this->observationId ? 'from_observation' : 'manual',
                    'source_observation_id' => $this->observationId,
                    'added_by_user_id' => $user->id,
                ]
            );
        }

        $this->noteReplaced = false;
        $this->refresh();

        // The lists on the object page that contain it.
        $this->dispatch('observing-list-item-toggled');
    }

    /**
     * Replace the current list note for this object with the observation description.
     */
    public function replaceNote(): void
    {
        $user = auth()->user();
        if (!$user || !$this->activeList || !$this->ownsActiveList || !$this->inList || !$this->itemId) {
            return;
        }

        if (!$user->can('addItem', $this->activeList)) {
            return;
        }

        $sanitizedNote = $this->getSanitizedObservationNote();

        ObservingListItem::where('id', $this->itemId)
            ->where('observing_list_id', $this->activeList->id)
            ->update([
                'item_description' => $sanitizedNote,
                'source_mode' => 'from_observation',
                'source_observation_id' => $this->observationId,
            ]);

        $this->noteReplaced = true;
        $this->refresh();
    }

    public function render()
    {
        return view('livewire.observing-list-toggle');
    }
}
