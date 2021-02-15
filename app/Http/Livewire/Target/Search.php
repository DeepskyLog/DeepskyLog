<?php

namespace App\Http\Livewire\Target;

use Livewire\Component;

class Search extends Component
{
    public String $allCatalogs;
    public string $constellations;
    public $catalog;

    public function mount()
    {
        $this->allCatalogs    = \App\Models\TargetName::getCatalogsChoices();
        $this->constellations = \App\Models\Constellation::getConstellationChoices();
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
    }

    public function render()
    {
        return view('livewire.target.search');
    }
}
