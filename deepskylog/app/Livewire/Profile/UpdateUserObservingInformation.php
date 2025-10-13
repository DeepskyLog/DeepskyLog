<?php

namespace App\Livewire\Profile;

use Livewire\Component;

class UpdateUserObservingInformation extends Component
{
    public $stdlocation;

    public $stdtelescope;

    public $stdinstrumentset;

    public $standardAtlasCode;

    public $showInches;
    
    // New defaults for Aladin preview
    public $stdeyepiece;
    public $stdlens;

    protected $rules = [
        'stdlocation' => 'numeric',
        'stdtelescope' => 'numeric',
        'stdinstrumentset' => 'numeric',
        'showInches' => 'boolean',
        'stdeyepiece' => 'numeric|nullable',
        'stdlens' => 'numeric|nullable',
    ];

    /**
     * Sets the database values.
     */
    public function mount(): void
    {
        $this->stdlocation = auth()->user()->stdlocation;
        $this->stdtelescope = auth()->user()->stdtelescope;
        $this->stdinstrumentset = auth()->user()->stdinstrumentset ?? null;
        $this->standardAtlasCode = auth()->user()->standardAtlasCode;
        $this->showInches = boolval(auth()->user()->showInches);
        $this->stdeyepiece = auth()->user()->stdeyepiece ?? null;
        $this->stdlens = auth()->user()->stdlens ?? null;
    }

    /**
     * Validate and update the given user's observing information.
     */
    public function updateObservingInformation(): void
    {
        $this->validate();

        if ($this->stdlocation == 0) {
            auth()->user()->forceFill([
                'stdlocation' => null,
            ])->save();
        } else {
            auth()->user()->forceFill([
                'stdlocation' => $this->stdlocation,
            ])->save();
        }
        if ($this->stdtelescope == 0) {
            auth()->user()->forceFill([
                'stdtelescope' => null,
            ])->save();
        } else {
            auth()->user()->forceFill([
                'stdtelescope' => $this->stdtelescope,
            ])->save();
        }
        if ($this->stdinstrumentset == 0) {
            auth()->user()->forceFill([
                'stdinstrumentset' => null,
            ])->save();
        } else {
            auth()->user()->forceFill([
                'stdinstrumentset' => $this->stdinstrumentset,
            ])->save();
        }
        // Persist eyepiece and lens defaults
        if ($this->stdeyepiece == 0) {
            auth()->user()->forceFill(['stdeyepiece' => null])->save();
        } else {
            auth()->user()->forceFill(['stdeyepiece' => $this->stdeyepiece])->save();
        }
        if ($this->stdlens == 0) {
            auth()->user()->forceFill(['stdlens' => null])->save();
        } else {
            auth()->user()->forceFill(['stdlens' => $this->stdlens])->save();
        }
        auth()->user()->forceFill([
            'standardAtlasCode' => $this->standardAtlasCode,
            'showInches' => $this->showInches,
        ])->save();

        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.profile.update-user-observing-information');
    }
}
