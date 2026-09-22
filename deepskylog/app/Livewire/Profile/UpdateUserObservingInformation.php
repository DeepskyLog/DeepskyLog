<?php

namespace App\Livewire\Profile;

use Livewire\Component;

class UpdateUserObservingInformation extends Component
{
    /**
     * Fallback atlas, matching the database default of users.standardAtlasCode,
     * which is a NOT NULL column.
     */
    private const DEFAULT_ATLAS_CODE = 'urano';

    public $stdlocation;

    public $stdtelescope;

    public $stdinstrumentset;

    public $standardAtlasCode;

    public $showInches;

    // New defaults for Aladin preview
    public $stdeyepiece;

    public $stdlens;

    protected $rules = [
        'stdlocation' => 'nullable|numeric',
        'stdtelescope' => 'nullable|numeric',
        'stdinstrumentset' => 'nullable|numeric',
        'standardAtlasCode' => 'required|string|exists:atlases,code',
        'showInches' => 'boolean',
        'stdeyepiece' => 'nullable|numeric',
        'stdlens' => 'nullable|numeric',
    ];

    /**
     * Sets the database values.
     */
    public function mount(): void
    {
        $this->stdlocation = auth()->user()->stdlocation;
        $this->stdtelescope = auth()->user()->stdtelescope;
        $this->stdinstrumentset = auth()->user()->stdinstrumentset ?? null;
        $this->standardAtlasCode = auth()->user()->standardAtlasCode ?: self::DEFAULT_ATLAS_CODE;
        $this->showInches = boolval(auth()->user()->showInches);
        $this->stdeyepiece = auth()->user()->stdeyepiece ?? null;
        $this->stdlens = auth()->user()->stdlens ?? null;
    }

    /**
     * Validate and update the given user's observing information.
     */
    public function updateObservingInformation(): void
    {
        // An unset select sends an empty string, which is not numeric.
        foreach (['stdlocation', 'stdtelescope', 'stdinstrumentset', 'stdeyepiece', 'stdlens'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }

        // standardAtlasCode is NOT NULL, so a cleared select falls back to the default.
        if (blank($this->standardAtlasCode)) {
            $this->standardAtlasCode = self::DEFAULT_ATLAS_CODE;
        }

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
