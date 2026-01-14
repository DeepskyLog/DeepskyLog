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
        // If no initial constellation provided, request an authoritative
        // ephemerides recompute from the `object-ephemerides` component so
        // it can emit `objectEphemeridesUpdated` with a computed constellation.
        try {
            if (empty($this->constellation) && ! empty($this->objectId)) {
                // Request a server-side ephemerides recompute by emitting the
                // normalized payload event that `ObjectEphemerides` listens to.
                $this->emit('ephemerisPayloadUpdated', ['objectId' => $this->objectId]);
            }
        } catch (\Throwable $_) {
            // ignore
        }

        // Defensive server-side recompute: instantiate the ObjectEphemerides
        // Livewire class and call recalculate() so we can obtain an
        // authoritative constellation immediately on the server.
        try {
            if (empty($this->constellation) && ! empty($this->objectId) && class_exists(\App\Http\Livewire\ObjectEphemerides::class)) {
                $lw = new \App\Http\Livewire\ObjectEphemerides();
                $lw->objectId = $this->objectId;
                $lw->suppressEphemerides = false;
                try {
                    $lw->recalculate(['objectId' => $this->objectId]);
                } catch (\Throwable $_) {
                    // ignore recalc failures
                }
                if (!empty($lw->ephemerides) && is_array($lw->ephemerides) && !empty($lw->ephemerides['constellation'])) {
                    $this->constellation = $lw->ephemerides['constellation'];
                }
            }
        } catch (\Throwable $_) {
            // ignore
        }
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
                if (array_key_exists('constellation', $arr)) {
                    $incoming = $arr['constellation'];
                    // Defensive: if an incoming payload would clear the constellation
                    // (null/empty) but we already have a non-empty value established
                    // from the server-rendered initial payload, prefer the existing
                    // value to avoid briefly showing 'Unknown' on the client.
                    if (($incoming === null || $incoming === '') && !empty($this->constellation)) {
                        return;
                    }
                    $this->constellation = $incoming;
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
