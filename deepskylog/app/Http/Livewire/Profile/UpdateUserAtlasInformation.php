<?php

namespace App\Http\Livewire\Profile;

use App\Models\User;
use Livewire\Component;

class UpdateUserAtlasInformation extends Component
{
    protected $rules = [
        'overviewFoV'                               => 'required|numeric|max:3600|min:1',
        'lookupFoV'                                 => 'required|numeric|max:3600|min:1',
        'detailFoV'                                 => 'required|numeric|max:3600|min:1',
        // 'overviewObjectMagnitude'                   => 'required|numeric|max:20.0|min:1.0',
        // 'lookupObjectMagnitude'                     => 'required|numeric|max:20.0|min:1.0',
        // 'detailObjectMagnitude'                     => 'required|numeric|max:20.0|min:1.0',
        // 'overviewStarMagnitude'                     => 'required|numeric|max:20.0|min:1.0',
        // 'lookupStarMagnitude'                       => 'required|numeric|max:20.0|min:1.0',
        // 'detailStarMagnitude'                       => 'required|numeric|max:20.0|min:1.0',
        // 'photosize1'                                => 'required|numeric|max:3600|min:1',
        // 'photosize2'                                => 'required|numeric|max:3600|min:1',
        // 'atlaspagefont'                             => 'required|numeric|max:9|min:6',
    ];

    public $overviewFoV;
    public $lookupFoV;
    public $detailFoV;

    /**
     * Sets the database values.
     *
     * @return void
     */
    public function mount()
    {
        $this->lookupFoV = auth()->user()->lookupFoV;
        $this->detailFoV = auth()->user()->detailFoV;
        $this->overviewFoV = auth()->user()->overviewFoV;
    }
    /**
     * Validate and update the given user's atlas information.
     *
     * @param  array<string, string>  $input
     */
    public function updateAtlasInformation(): void
    {
        $this->validate();

        auth()->user()->forceFill([
            'overviewFoV' => $this->overviewFoV,
            'lookupFoV' => $this->lookupFoV,
            'detailFoV' => $this->detailFoV,
        ])->save();

        $this->emit('saved');
    }

    public function render()
    {
        return view('livewire.profile.update-user-atlas-information');
    }
}
