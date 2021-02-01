<?php

namespace App\Http\Livewire\Set;

use App\Models\Set;
use App\Models\Lens;
use App\Models\Filter;
use Livewire\Component;
use App\Models\Eyepiece;
use App\Models\Instrument;

class Show extends Component
{
    public Set $set;
    public String $title           = '';
    public $description;
    public String $origDescription = '';
    public bool $showInstruments   = false;
    public bool $showEyepieces     = false;
    public bool $showFilters       = false;
    public bool $showLenses        = false;
    public bool $changeTitle       = false;
    public bool $changeDescription = false;
    public $addInstrument          = [];
    public $addEyepieces           = [];
    public $addFilters             = [];
    public $addLenses              = [];

    protected $rules = [
        'title'        => 'required|max:100|min:4',
        'description'  => 'required|max:1000',
    ];

    public function mount()
    {
        $this->title               = $this->set->name;
        $this->origDescription     = $this->set->description;
        // Populate the list with the instruments from the set
        $this->addInstrument       = array_fill_keys($this->set->instruments()->get()->pluck('id')->toArray(), 1);
        // Populate the list with the eyepieces from the set
        $this->addEyepieces       = array_fill_keys($this->set->eyepieces()->get()->pluck('id')->toArray(), 1);
        // Populate the list with the filters from the set
        $this->addFilters       = array_fill_keys($this->set->filters()->get()->pluck('id')->toArray(), 1);
        // Populate the list with the lenses from the set
        $this->addLenses       = array_fill_keys($this->set->lenses()->get()->pluck('id')->toArray(), 1);
    }

    public function showInstruments()
    {
        $this->showInstruments = true;
    }

    public function hideInstruments()
    {
        $this->showInstruments = false;
    }

    public function showEyepieces()
    {
        $this->showEyepieces = true;
    }

    public function hideEyepieces()
    {
        $this->showEyepieces = false;
    }

    public function showFilters()
    {
        $this->showFilters = true;
    }

    public function hideFilters()
    {
        $this->showFilters = false;
    }

    public function showLenses()
    {
        $this->showLenses = true;
    }

    public function hideLenses()
    {
        $this->showLenses = false;
    }

    public function adaptTitle()
    {
        $this->changeTitle = true;
    }

    public function hideTitle()
    {
        $this->changeTitle = false;
        // Save the changes to the database
        $this->set->update(['name' => $this->title]);
    }

    public function adaptDescription()
    {
        $this->origDescription     = $this->set->description;
        $this->changeDescription   = true;
    }

    public function save()
    {
        $this->changeDescription = false;
        // Save the changes to the database
        $this->set->update(['description' => $this->description['body']]);
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
        if ($propertyName == 'title') {
            $this->validateOnly('title');
        }

        if ($propertyName == 'description.body') {
            $this->validateOnly('description');
        }

        if (str_contains($propertyName, 'addInstrument')) {
            $toRemove   = array_search(false, $this->addInstrument);

            if ($toRemove) {
                // We need to remove an instrument from the equipment set
                $instrument = Instrument::find($toRemove);
                $this->set->instruments()->detach($instrument);
            } else {
                // Add to the set
                $currentInstruments = $this->set->instruments()->get()->pluck('id')->toArray();
                foreach ($this->addInstrument as $instrument=>$value) {
                    if (!in_array($instrument, $currentInstruments)) {
                        $ins = Instrument::find($instrument);
                        $this->set->instruments()->save($ins);
                    }
                }
            }
            $this->addInstrument       = array_fill_keys($this->set->instruments()->get()->pluck('id')->toArray(), 1);
        }
        if (str_contains($propertyName, 'addEyepiece')) {
            $toRemove   = array_search(false, $this->addEyepieces);

            if ($toRemove) {
                // We need to remove an eyepiece from the equipment set
                $eyepiece = Eyepiece::find($toRemove);
                $this->set->eyepieces()->detach($eyepiece);
            } else {
                // Add to the set
                $currentEyepieces = $this->set->eyepieces()->get()->pluck('id')->toArray();
                foreach ($this->addEyepieces as $eyepiece=>$value) {
                    if (!in_array($eyepiece, $currentEyepieces)) {
                        $eye = Eyepiece::find($eyepiece);
                        $this->set->eyepieces()->save($eye);
                    }
                }
            }
            $this->addEyepieces = array_fill_keys($this->set->eyepieces()->get()->pluck('id')->toArray(), 1);
        }
        if (str_contains($propertyName, 'addFilter')) {
            $toRemove   = array_search(false, $this->addFilters);

            if ($toRemove) {
                // We need to remove a filter from the equipment set
                $filter = Filter::find($toRemove);
                $this->set->filters()->detach($filter);
            } else {
                // Add to the set
                $currentFilters = $this->set->filters()->get()->pluck('id')->toArray();
                foreach ($this->addFilters as $filter=>$value) {
                    if (!in_array($filter, $currentFilters)) {
                        $fil = Filter::find($filter);
                        $this->set->filters()->save($fil);
                    }
                }
            }
            $this->addFilters = array_fill_keys($this->set->filters()->get()->pluck('id')->toArray(), 1);
        }
        if (str_contains($propertyName, 'addLens')) {
            $toRemove   = array_search(false, $this->addLenses);

            if ($toRemove) {
                // We need to remove a lens from the equipment set
                $lens = Lens::find($toRemove);
                $this->set->lenses()->detach($lens);
            } else {
                // Add to the set
                $currentLenses = $this->set->lenses()->get()->pluck('id')->toArray();
                foreach ($this->addLenses as $lens=>$value) {
                    if (!in_array($lens, $currentLenses)) {
                        $lns = Lens::find($lens);
                        $this->set->lenses()->save($lns);
                    }
                }
            }
            $this->addLenses = array_fill_keys($this->set->lenses()->get()->pluck('id')->toArray(), 1);
        }
    }

    public function render()
    {
        return view('livewire.set.show');
    }
}
