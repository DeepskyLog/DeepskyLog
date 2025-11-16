<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ObjectConstellation extends Component
{
    public $constellation = null;
    public $objectId = null;

    protected $listeners = [
        'setConstellation' => 'setConstellation',
        'objectConstellationUpdated' => 'setConstellationFromPayload',
        // Also accept the broader ephemerides update event in case other components emit it
        'objectEphemeridesUpdated' => 'setConstellationFromPayload',
    ];

    public function mount($initialConstellation = null, $objectId = null)
    {
        $this->constellation = $initialConstellation ?? null;
        $this->objectId = $objectId ?? null;
    }

    public function setConstellation($value)
    {
        try {
            $this->constellation = $value;
        } catch (\Throwable $_) {
            // ignore
        }
    }

    public function setConstellationFromPayload($payload)
    {
        try {
            if (is_array($payload) || is_object($payload)) {
                $arr = (array) $payload;
                // If objectId provided, ensure it matches this instance (best-effort)
                if (isset($arr['objectId']) && $arr['objectId'] !== null && $this->objectId !== null) {
                    if ((string)$arr['objectId'] !== (string)$this->objectId) return;
                }
                if (isset($arr['constellation'])) {
                    $this->constellation = $arr['constellation'];
                }
            }
        } catch (\Throwable $_) {
            // ignore
        }
    }

    public function render()
    {
        return view('livewire.object-constellation');
    }
}
