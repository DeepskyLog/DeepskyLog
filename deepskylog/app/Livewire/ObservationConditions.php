<?php

namespace App\Livewire;

use App\Models\Location;
use deepskylog\AstronomyLibrary\Magnitude;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ObservationConditions extends Component
{
    public $sqm;
    public $nelm;
    public $lastAction;
    public $bortle;
    // Internal guard to avoid triggering update handlers from programmatic assignments
    private $suppressLivewireUpdates = false;

    protected $listeners = [
        'locationSelected' => 'onLocationSelected',
    ];

    public function mount($initialSqm = null, $initialNelm = null, $initialBortle = null)
    {
        $this->sqm = $initialSqm !== null && $initialSqm !== '' ? $initialSqm : null;
        $this->nelm = $initialNelm !== null && $initialNelm !== '' ? $initialNelm : null;
        $this->bortle = $initialBortle !== null && $initialBortle !== '' ? $initialBortle : null;
        $this->lastAction = null;

        // Prefilled values (editing an observation) come without a Bortle class.
        if ($this->bortle === null) {
            try {
                if ($this->sqm !== null) {
                    $this->bortle = Magnitude::sqmToBortle((float) $this->sqm);
                } elseif ($this->nelm !== null) {
                    $this->bortle = Magnitude::nelmToBortle((float) $this->nelm);
                }
            } catch (\Throwable $_) {
                $this->bortle = null;
            }
        }
    }

    public function onLocationSelected($locationId)
    {
        if (empty($locationId)) {
            return;
        }

        $loc = Location::find($locationId);
        if (!$loc) {
            return;
        }

        $fstOffset = Auth::check() ? (Auth::user()->fstOffset ?? 0.0) : 0.0;

        // Avoid chained updatedX handlers firing while we programmatically assign values
        $this->suppressLivewireUpdates = true;
        try {
            if (!empty($loc->skyBackground) && $loc->skyBackground > 0) {
                $this->sqm = round((float) $loc->skyBackground, 2);
                try {
                    $this->nelm = round(Magnitude::sqmToNelm($this->sqm, $fstOffset), 1);
                } catch (\Throwable $_) {
                    $this->nelm = null;
                }
                try {
                    $this->bortle = Magnitude::sqmToBortle($this->sqm);
                } catch (\Throwable $_) {
                    $this->bortle = null;
                }
            } elseif (!empty($loc->limitingMagnitude) && $loc->limitingMagnitude > 0) {
                // The location stores the NELM without the fstOffset, the observation
                // takes the NELM of the observer (with the offset).
                $this->nelm = (float) $loc->getNelm($fstOffset);
                try {
                    $this->sqm = round(Magnitude::nelmToSqm($this->nelm, $fstOffset), 2);
                } catch (\Throwable $_) {
                    $this->sqm = null;
                }
                try {
                    $this->bortle = Magnitude::nelmToBortle($this->nelm);
                } catch (\Throwable $_) {
                    $this->bortle = null;
                }
            }
        } finally {
            $this->suppressLivewireUpdates = false;
        }
    }

    public function updatedSqm($value)
    {
        if ($this->suppressLivewireUpdates) {
            return;
        }

        if ($value === null || $value === '') {
            $this->suppressLivewireUpdates = true;
            $this->nelm = null;
            $this->lastAction = 'updatedSqm';
            $this->bortle = null;
            $this->suppressLivewireUpdates = false;
            return;
        }

        try {
            $fstOffset = Auth::check() ? (Auth::user()->fstOffset ?? 0.0) : 0.0;
            $sqm = (float) $value;
            $nelm = round(Magnitude::sqmToNelm($sqm, $fstOffset), 1);
            $bortle = Magnitude::sqmToBortle($sqm);

            $this->suppressLivewireUpdates = true;
            $this->nelm = $nelm;
            $this->bortle = $bortle;
        } catch (\Throwable $_) {
            $this->nelm = null;
            $this->bortle = null;
        } finally {
            $this->suppressLivewireUpdates = false;
        }
    }

    public function updatedNelm($value)
    {
        if ($this->suppressLivewireUpdates) {
            return;
        }

        if ($value === null || $value === '') {
            $this->suppressLivewireUpdates = true;
            $this->sqm = null;
            $this->lastAction = 'updatedNelm';
            $this->bortle = null;
            $this->suppressLivewireUpdates = false;
            return;
        }

        try {
            $fstOffset = Auth::check() ? (Auth::user()->fstOffset ?? 0.0) : 0.0;
            $nelm = (float) $value;
            $sqm = round(Magnitude::nelmToSqm($nelm, $fstOffset), 2);
            $bortle = Magnitude::nelmToBortle($nelm);

            $this->suppressLivewireUpdates = true;
            $this->sqm = $sqm;
            $this->bortle = $bortle;
        } catch (\Throwable $_) {
            $this->sqm = null;
            $this->bortle = null;
        } finally {
            $this->suppressLivewireUpdates = false;
        }
    }

    public function updatedBortle($value)
    {
        if ($this->suppressLivewireUpdates) {
            return;
        }

        $this->lastAction = 'updatedBortle';
        if ($value === null || $value === '') {
            return;
        }
        try {
            $fstOffset = Auth::check() ? (Auth::user()->fstOffset ?? 0.0) : 0.0;
            $b = (int) $value;
            $nelm = round(Magnitude::bortleToNelm($b, $fstOffset), 1);
            $sqm = round(Magnitude::bortleToSqm($b), 2);

            $this->suppressLivewireUpdates = true;
            $this->nelm = $nelm;
            $this->sqm = $sqm;
        } catch (\Throwable $_) {
            $this->nelm = null;
            $this->sqm = null;
        } finally {
            $this->suppressLivewireUpdates = false;
        }
    }

    public function render()
    {
        return view('livewire.observation-conditions');
    }
}
