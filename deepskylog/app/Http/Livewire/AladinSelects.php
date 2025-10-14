<?php
namespace App\Http\Livewire;

use Livewire\Component;

class AladinSelects extends Component
{
    public $instrument;
    public $eyepiece;
    public $lens;
    public $instrumentSet;

    public function mount($instrument = null, $eyepiece = null, $lens = null, $instrumentSet = null)
    {
        $this->instrument = $instrument;
        $this->eyepiece = $eyepiece;
        $this->lens = $lens;
        $this->instrumentSet = $instrumentSet;
        // Emit initial values to the front-end so the page can sync immediately
        // No server-side browser event here; Livewire will render the component with
        // the public properties and the page JS reads the hidden inputs / rendered DOM.
    }

    public function updated($name, $value)
    {
        // No server->client event helper is called here because the installed
        // Livewire version does not provide dispatchBrowserEvent()/emit().
        // Livewire will update the DOM with the new values and the page JS
        // reads the hidden inputs / rendered DOM to update Aladin.
    }

    public function render()
    {
        return view('livewire.aladin-selects');
    }
}
