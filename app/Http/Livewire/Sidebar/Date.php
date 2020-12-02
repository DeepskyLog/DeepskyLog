<?php

namespace App\Http\Livewire\Sidebar;

use Exception;
use Livewire\Component;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class Date extends Component
{
    public $carbonDate;
    public $carbonDateString;
    public $date;
    public $location;

    public function mount()
    {
        // Current date
        $dslDate                = Session::get('date');
        $date                   = \Carbon\Carbon::createFromFormat('Y-m-d', $dslDate);
        $date->hour             = 12;
        $this->carbonDate       = $date;
        $this->carbonDateString = $this->carbonDate->isoFormat('LL');
        $this->date             = $this->carbonDate;
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
            $date                   = \Carbon\Carbon::createFromIsoFormat('LL', $this->carbonDateString, null, \deepskylog\LaravelGettext\Facades\LaravelGettext::getLocaleLanguage());
            $date->hour             = 12;
            $this->carbonDate       = $date;
            Request::session()->put('date', $date->isoFormat('Y-M-D'));
            // $this->carbonDateString = $this->carbonDate->isoFormat('LL');
            $this->date             = $date;
            $this->emit('dateChanged');
        } catch (Exception $e) {
            dd($e);
        }
    }

    public function render()
    {
        return view('livewire.sidebar.date');
    }
}
