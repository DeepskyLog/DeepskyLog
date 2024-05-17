<?php

namespace App\Livewire\Profile;

use Livewire\Component;

class UpdateUserObservingInformation extends Component
{
    public $stdlocation;

    public $stdtelescope;

    public $standardAtlasCode;

    public $showInches;

    protected $rules = [
        'stdlocation' => 'numeric',
        'stdtelescope' => 'numeric',
    ];

    /**
     * Sets the database values.
     *
     * @return void
     */
    public function mount()
    {
        $this->stdlocation = auth()->user()->stdlocation;
        $this->stdtelescope = auth()->user()->stdtelescope;
        $this->standardAtlasCode = auth()->user()->standardAtlasCode;
        $this->showInches = auth()->user()->showInches;
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
