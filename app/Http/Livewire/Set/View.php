<?php

namespace App\Http\Livewire\Set;

use App\Models\Set;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class View extends Component
{
    public $add;
    public $showAddSetField = false;
    public $name;
    public $description;

    protected $listeners = [
        'delete'   => 'delete',
    ];

    protected $rules = [
        'name'        => 'required|max:100|min:4',
        'description' => 'required|max:1000',
    ];

    public function newSet()
    {
        $this->showAddSetField = true;
    }

    public function mount()
    {
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

    public function delete($id)
    {
        $set = Set::where('id', $id)->first();

        $set->delete();
        $this->emit('refreshLivewireDatatable');

        session()->flash(
            'message',
            _i('Equipment set %s deleted', $set->name)
        );
    }

    /**
     * Method that is called when the submit button is pushed.
     *
     * @return void
     */
    public function save()
    {
        $this->validate();

        // Create the new equipment set
        if (count(\App\Models\Set::where('name', $this->name)->where('user_id', Auth::id())->get())) {
            session()->flash('message', _i('Equipment set %s already exists.', $this->name));
        } else {
            $set = \App\Models\Set::create(
                [
                    'name'        => $this->name,
                    'description' => $this->description['body'],
                    'user_id'     => Auth::id(),
                ]
            );
            session()->flash('message', _i('Equipment set %s created', $set->name));

            $this->showAddSetField = false;
            $this->emit('refreshLivewireDatatable');
        }
    }

    public function render()
    {
        return view('livewire.set.view');
    }
}
