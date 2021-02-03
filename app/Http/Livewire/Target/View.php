<?php

namespace App\Http\Livewire\Target;

use Livewire\Component;

class View extends Component
{
    public $location;
    public $instrument;
    public $targetsToShow;

    public function render()
    {
        return view('livewire.target.view');
    }
}
