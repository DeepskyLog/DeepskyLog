<?php

namespace App\Http\Livewire\Location;

use Livewire\Component;

class Show extends Component
{
    public $location;
    public $media;

    protected $listeners = ['dateChanged' => 'dateChanged'];

    public function dateChanged()
    {
    }

    public function render()
    {
        return view('livewire.location.show');
    }
}
