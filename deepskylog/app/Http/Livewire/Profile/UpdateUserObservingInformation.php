<?php

namespace App\Http\Livewire\Profile;

use Livewire\Component;

class UpdateUserObservingInformation extends Component
{
    public $stdlocation;

    protected $rules = [
        'stdlocation' => 'numeric',
    ];
    /**
     * Sets the database values.
     *
     * @return void
     */
    public function mount()
    {
        $this->stdlocation = auth()->user()->stdlocation;
    }

    /**
     * Validate and update the given user's observing information.
     *
     */
    public function updateObservingInformation(): void
    {
        $this->validate();
        // dd($this->stdlocation);

        if ($this->stdlocation == 0) {
            auth()->user()->forceFill([
                'stdlocation' => null,
            ])->save();
        } else {
            auth()->user()->forceFill([
                'stdlocation' => $this->stdlocation,
            ])->save();
        }

        $this->emit('saved');
    }

    public function render()
    {
        return view('livewire.profile.update-user-observing-information');
    }
}
