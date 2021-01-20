<?php

namespace App\Http\Livewire\Set;

use App\Models\Set;
use Livewire\Component;

class Show extends Component
{
    public Set $set;
    public String $title         = '';
    public bool $showInstruments = false;
    public bool $changeTitle     = false;
    public $addInstrument        = [];

    protected $rules = [
        'title'        => 'required|max:100|min:4',
        // 'description'  => 'required|max:1000',
    ];

    public function mount()
    {
        $this->title = $this->set->name;
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

        if ($propertyName == 'addInstrument') {
            // dd($this->set);
            // Add to the set
            // $this->addInstrument[0]
            dd($this->addInstrument[0]);
            // Delete to the set???
        }
    }

    public function render()
    {
        return view('livewire.set.show');
    }
}
