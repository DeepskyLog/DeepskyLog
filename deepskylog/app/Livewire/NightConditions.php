<?php

namespace App\Livewire;

use App\Models\Observation;
use App\Models\ObservationSession;
use App\Services\Weather\NightWeather;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The weather of the night of an observation, or of the nights of a session
 * whose observer wrote none, from Open-Meteo's historical archive
 * (NightWeather). Lazy, so a night that is not stored yet is fetched in its
 * own request and the page (response cached) never waits for it.
 */
#[Lazy]
class NightConditions extends Component
{
    #[Locked]
    public ?int $observationId = null;

    #[Locked]
    public ?int $sessionId = null;

    public function placeholder(): string
    {
        return '<div class="text-sm text-gray-500">…</div>';
    }

    public function render()
    {
        $observation = null;
        $nights      = null;

        if ($this->observationId !== null) {
            $model = Observation::find($this->observationId);
            if ($model && NightWeather::appliesToObservation($model)) {
                $observation = NightWeather::forObservation($model);
            }
        } elseif ($this->sessionId !== null) {
            $session = ObservationSession::find($this->sessionId);
            $visible = $session && ($session->active || $session->canBeEditedBy(Auth::user()));
            if ($visible && NightWeather::appliesToSession($session)) {
                $nights = NightWeather::forSession($session);
            }
        }

        return view('livewire.night-conditions', ['observation' => $observation, 'nights' => $nights]);
    }
}
