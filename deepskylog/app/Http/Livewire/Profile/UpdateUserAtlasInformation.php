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
        'overviewdsos'                              => 'required|numeric|max:20.0|min:1.0',
        'lookupdsos'                                => 'required|numeric|max:20.0|min:1.0',
        'detaildsos'                                => 'required|numeric|max:20.0|min:1.0',
        'overviewstars'                             => 'required|numeric|max:20.0|min:1.0',
        'lookupstars'                               => 'required|numeric|max:20.0|min:1.0',
        'detailstars'                               => 'required|numeric|max:20.0|min:1.0',
        'photosize1'                                => 'required|numeric|max:3600|min:1',
        'photosize2'                                => 'required|numeric|max:3600|min:1',
        'atlaspagefont'                             => 'required|numeric|max:9|min:6',
    ];

    public $overviewFoV;
    public $lookupFoV;
    public $detailFoV;
    public $overviewdsos;
    public $lookupdsos;
    public $detaildsos;
    public $overviewstars;
    public $lookupstars;
    public $detailstars;
    public $photosize1;
    public $photosize2;
    public $atlaspagefont;

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
        if (auth()->user()->overviewdsos) {
            $this->overviewdsos = auth()->user()->overviewdsos;
        } else {
            $this->overviewdsos = 10;
        }
        if (auth()->user()->lookupdsos) {
            $this->lookupdsos = auth()->user()->lookupdsos;
        } else {
            $this->lookupdsos = 12;
        }
        if (auth()->user()->detaildsos) {
            $this->detaildsos = auth()->user()->detaildsos;
        } else {
            $this->detaildsos = 15;
        }
        if (auth()->user()->overviewstars) {
            $this->overviewstars = auth()->user()->overviewstars;
        } else {
            $this->overviewstars = 10;
        }
        if (auth()->user()->lookupstars) {
            $this->lookupstars = auth()->user()->lookupstars;
        } else {
            $this->lookupstars = 12;
        }
        if (auth()->user()->detailstars) {
            $this->detailstars = auth()->user()->detailstars;
        } else {
            $this->detailstars = 15;
        }
        $this->photosize1 = auth()->user()->photosize1;
        $this->photosize2 = auth()->user()->photosize2;
        $this->atlaspagefont = auth()->user()->atlaspagefont;
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
            'overviewdsos' => $this->overviewdsos,
            'lookupdsos' => $this->lookupdsos,
            'detaildsos' => $this->detaildsos,
            'overviewstars' => $this->overviewstars,
            'lookupstars' => $this->lookupstars,
            'detailstars' => $this->detailstars,
            'photosize1' => $this->photosize1,
            'photosize2' => $this->photosize2,
            'atlaspagefont' => $this->atlaspagefont
        ])->save();

        $this->emit('saved');
    }

    public function render()
    {
        return view('livewire.profile.update-user-atlas-information');
    }
}
