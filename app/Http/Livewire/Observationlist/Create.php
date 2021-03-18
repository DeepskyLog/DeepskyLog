<?php

namespace App\Http\Livewire\Observationlist;

use Livewire\Component;
use App\Models\ObservationList;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $observationList;
    public $update;
    public $name;
    public $description;
    public String $origDescription = '';
    public $discoverable;

    protected $rules = [
        'name'               => ['required', 'min:6', 'max:100'],
    ];

    public function mount()
    {
        if ($this->observationList->exists) {
            $this->update               = true;
            $this->name                 = $this->observationList->name;
            $this->origDescription      = $this->observationList->description;
            $this->discoverable         = $this->observationList->discoverable;
        } else {
            $this->update      = false;
        }
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

    public function save()
    {
        $this->validate();

        if ($this->discoverable) {
            $disc = 1;
        } else {
            $disc = 0;
        }

        if ($this->update) {
            // Update the existing observation list
            $this->observationList->update(['name' => $this->name]);
            $this->observationList->update(['description' => $this->description['body']]);
            $this->observationList->update(['discoverable' => $disc]);
            laraflash(_i('Observation list %s updated', $this->name))->success();
        } else {
            // Create a new observation list
            $list = ObservationList::create(
                ['user_id'               => Auth::user()->id,
                    'name'               => $this->name,
                    'description'        => $this->description['body'],
                    'discoverable'       => $disc, ]
            );

            Auth::user()->update(['activeList' => $list->slug]);
            laraflash(_i('Observation list %s created', $list->name))->success();
        }

        // View the page with all observation lists for the user
        return redirect(route('observationList.index'));
    }

    public function render()
    {
        return view('livewire.observationlist.create');
    }
}
