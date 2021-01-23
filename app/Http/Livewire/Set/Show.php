<?php

namespace App\Http\Livewire\Set;

use App\Models\Set;
use Livewire\Component;
use App\Models\Instrument;

class Show extends Component
{
    public Set $set;
    public String $title           = '';
    public $description;
    public String $origDescription = '';
    public bool $showInstruments   = false;
    public bool $changeTitle       = false;
    public bool $changeDescription = false;
    public $addInstrument          = [];

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
    }

    public function showInstruments()
    {
        $this->showInstruments = true;
    }

    public function hideInstruments()
    {
        $this->showInstruments = false;
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

            $this->showInstruments = false;
        }
    }

    public function render()
    {
        return view('livewire.set.show');
    }
}
