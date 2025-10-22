<?php
namespace App\Http\Livewire;

use Livewire\Component;

class AladinSelects extends Component
{
    public $instrument;
    public $eyepiece;
    public $lens;
    public $instrumentSet;
    public $objectId;

    public function mount($instrument = null, $eyepiece = null, $lens = null, $instrumentSet = null, $objectId = null)
    {
        $this->instrument = $instrument;
        $this->eyepiece = $eyepiece;
        $this->lens = $lens;
        $this->instrumentSet = $instrumentSet;
        $this->objectId = (is_string($objectId) && trim($objectId) === '') ? null : $objectId;
        // Emit initial values to the front-end so the page can sync immediately
        // No server-side browser event here; Livewire will render the component with
        // the public properties and the page JS reads the hidden inputs / rendered DOM.
    }

    public function updated($name, $value)
    {
        // When a select changes, notify the frontend and attempt a direct server-side
        // emit to the preview component so it can recalculate immediately. This
        // mirrors the behaviour of the client-side listeners and avoids requiring
        // the user to press "Force recalculation".
        try {
            // Normalize empty-string selections (from client selects) to explicit null
            $payload = [
                'instrument' => (is_string($this->instrument) && trim($this->instrument) === '') ? null : ($this->instrument ?? null),
                'eyepiece' => (is_string($this->eyepiece) && trim($this->eyepiece) === '') ? null : ($this->eyepiece ?? null),
                'lens' => (is_string($this->lens) && trim($this->lens) === '') ? null : ($this->lens ?? null),
                'objectId' => (is_string($this->objectId) && trim($this->objectId) === '') ? null : $this->objectId,
            ];

            // Emit a Livewire browser event the frontend JS listens for so hidden
            // inputs get synced and client-side logic runs.
            $this->dispatchBrowserEvent('aladin-selects-changed', $payload);

            // Also dispatch a Livewire event targeted at the preview component so it
            // performs the recalculation server-side immediately if it's mounted.
            // In Livewire v3 use dispatch(...)->to('component-name') instead of emitTo.
            try { $this->dispatch('aladinUpdated', payload: $payload)->to('aladin-preview-info'); } catch (\Throwable $_) {}
        } catch (\Throwable $_) {
            // swallow to avoid breaking select updates
        }
    }

    public function render()
    {
        return view('livewire.aladin-selects');
    }
}
