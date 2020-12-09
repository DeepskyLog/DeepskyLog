<?php

namespace App\Http\Livewire\Target;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Ephemerides extends Component
{
    public $target;
    public $location;

    protected $listeners = ['locationChanged' => 'locationChanged'];

    public function locationChanged()
    {
        $this->location = \App\Models\Location::where('id', Auth::user()->stdlocation)->first();
    }

    public function mount()
    {
        $this->location = \App\Models\Location::where('id', Auth::user()->stdlocation)->first();
    }

    public function render()
    {
        return view('livewire.target.ephemerides');
    }
}
