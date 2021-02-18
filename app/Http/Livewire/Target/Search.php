<?php

namespace App\Http\Livewire\Target;

use Livewire\Component;

class Search extends Component
{
    public bool $addExtraSearchParameter = false;
    public String $allCatalogs;
    public string $constellations;
    public $catalog;
    public String $searchCriteria;
    public $criteria;
    public $numberOfConstellations = 0;
    public $numberOfNames          = 0;
    public $constellation;

    public function mount()
    {
        $this->allCatalogs    = \App\Models\TargetName::getCatalogsChoices();
        $this->constellations = \App\Models\Constellation::getConstellationChoices();
        $this->searchCriteria = '<option value=""></option>';
        $this->searchCriteria .= '<option value="name">Object name</option>';
        $this->searchCriteria .= '<option value="constellation">Constellation</option>';
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
        if ($propertyName == 'criteria') {
            if ($this->criteria == 'constellation') {
                $this->numberOfConstellations++;
            }
            if ($this->criteria == 'name') {
                $this->numberOfNames++;
            }
            $this->addExtraSearchParameter = false;
            $this->criteria                = '';
        }
    }

    public function addSearch()
    {
        $this->addExtraSearchParameter = true;
    }

    // TODO: When we first add constellations to the search criteria and then names, the dropdown for the names shows the constellations!!!
    // TODO: Remove one of the search criteria
    // TODO: Add extra search criteria
    // TODO: Write the clear button
    // TODO: Save the search criteria?
    // TODO: Translate

    public function render()
    {
        return view('livewire.target.search');
    }
}
