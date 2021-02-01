<?php

namespace App\Http\Livewire\Target;

use Livewire\Component;

class Search extends Component
{
    public String $allCatalogs;
    public $catalog;

    public function mount()
    {
        $this->allCatalogs = \App\Models\TargetName::getCatalogsChoices();
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
        if ($propertyName == 'catalog') {
            dd($this->catalog);
        }
    }

    public function render()
    {
        return view('livewire.target.search');
    }
}
