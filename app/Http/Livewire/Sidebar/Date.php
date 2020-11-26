<?php

namespace App\Http\Livewire\Sidebar;

use Exception;
use Livewire\Component;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class Date extends Component
{
    public $dslDate;
    public $carbonDate;
    public $carbonDateString;
    public $date;
    public $location;

    public function mount()
    {
        $this->dslDate          = Session::get('date');
        $date                   = \Carbon\Carbon::createFromFormat('Y-m-d', $this->dslDate);
        $date->hour             = 12;
        $this->carbonDate       = $date;
        $this->carbonDateString = $this->carbonDate->isoFormat('LL');
        $this->date             = $this->carbonDate;

        // Moon rise and set
        $this->location = Location::where('id', Auth::user()->stdlocation)->first();
    }

    /**
     * Set the session when updating.
     *
     * @param mixed $propertyName The name of the property
     *
     * @return void
     */
    public function updated($propertyName)
    {
        try {
            $date                   = \Carbon\Carbon::createFromFormat('Y-m-d', $this->carbonDateString);
            $date->hour             = 12;
            $this->carbonDate       = $date;
            Request::session()->put('date', $this->carbonDateString);
            $this->carbonDateString = $this->carbonDate->isoFormat('LL');
            $this->date             = $this->carbonDate;
            $this->emit('dateChanged');
        } catch (Exception $e) {
        }
    }

    public function render()
    {
        return view('livewire.sidebar.date');
    }
}
