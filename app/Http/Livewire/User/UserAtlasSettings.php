<?php

namespace App\Http\Livewire\User;

use App\Models\User;
use Livewire\Component;

class UserAtlasSettings extends Component
{
    public User $user;
    public $overviewFov;
    public $lookupFov;
    public $detailFov;
    public $overviewObjectMagnitude;
    public $lookupObjectMagnitude;
    public $detailObjectMagnitude;
    public $overviewStarMagnitude;
    public $lookupStarMagnitude;
    public $detailStarMagnitude;
    public $photosize1;
    public $photosize2;
    public $atlaspagefont;

    protected $rules = [
        'overviewFov'                               => 'required|numeric|max:3600|min:1',
        'lookupFov'                                 => 'required|numeric|max:3600|min:1',
        'detailFov'                                 => 'required|numeric|max:3600|min:1',
        'overviewObjectMagnitude'                   => 'required|numeric|max:20.0|min:1.0',
        'lookupObjectMagnitude'                     => 'required|numeric|max:20.0|min:1.0',
        'detailObjectMagnitude'                     => 'required|numeric|max:20.0|min:1.0',
        'overviewStarMagnitude'                     => 'required|numeric|max:20.0|min:1.0',
        'lookupStarMagnitude'                       => 'required|numeric|max:20.0|min:1.0',
        'detailStarMagnitude'                       => 'required|numeric|max:20.0|min:1.0',
        'photosize1'                                => 'required|numeric|max:3600|min:1',
        'photosize2'                                => 'required|numeric|max:3600|min:1',
        'atlaspagefont'                             => 'required|numeric|max:9|min:6',
    ];

    /**
     * Sets the database values.
     *
     * @return void
     */
    public function mount()
    {
        $this->overviewFov                                  = $this->user->overviewFoV;
        $this->lookupFov                                    = $this->user->lookupFoV;
        $this->detailFov                                    = $this->user->detailFoV;
        $this->overviewObjectMagnitude                      = $this->user->overviewdsos;
        $this->lookupObjectMagnitude                        = $this->user->lookupdsos;
        $this->detailObjectMagnitude                        = $this->user->detaildsos;
        $this->overviewStarMagnitude                        = $this->user->overviewstars;
        $this->lookupStarMagnitude                          = $this->user->lookupstars;
        $this->detailStarMagnitude                          = $this->user->detailstars;
        $this->photosize1                                   = $this->user->photosize1;
        $this->photosize2                                   = $this->user->photosize2;
        $this->atlaspagefont                                = $this->user->atlaspagefont;
    }

    /**
     * Real time validation.
     *
     * @param mixed $propertyName The name of the property
     *
     * @return void
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Method that is called when the submit button is pushed.
     *
     * @return void
     */
    public function save()
    {
        $this->validate();

        $this->user->update(['overviewFoV' => $this->overviewFov]);
        $this->user->update(['lookupFoV' => $this->lookupFov]);
        $this->user->update(['detailFoV' => $this->detailFov]);
        $this->user->update(['overviewdsos' => $this->overviewObjectMagnitude]);
        $this->user->update(['lookupdsos' => $this->lookupObjectMagnitude]);
        $this->user->update(['detaildsos' => $this->detailObjectMagnitude]);
        $this->user->update(['overviewstars' => $this->overviewStarMagnitude]);
        $this->user->update(['lookupstars' => $this->lookupStarMagnitude]);
        $this->user->update(['detailstars' => $this->detailStarMagnitude]);
        $this->user->update(['photosize1' => $this->photosize1]);
        $this->user->update(['photosize2' => $this->photosize2]);
        $this->user->update(['atlaspagefont' => $this->atlaspagefont]);
    }

    /**
     * Render the page.
     *
     * @return View|Factory The view for the livewire component
     */
    public function render()
    {
        return view('livewire.user.user-atlas-settings');
    }
}
