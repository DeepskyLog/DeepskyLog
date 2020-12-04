<?php

namespace App\Http\Livewire\Eyepiece;

use Livewire\Component;
use App\Models\Eyepiece;
use Illuminate\Support\Facades\Auth;

class View extends Component
{
    public $instrument;
    public $lens;

    protected $listeners = [
        'activate' => 'activate',
        'delete'   => 'delete',
    ];

    public function activate($id)
    {
        $eyepiece = Eyepiece::where('id', $id)->first();
        $eyepiece->toggleActive();
        if ($eyepiece->active) {
            session()->flash('message', _i('Eyepiece %s is active', $eyepiece->name));
        } else {
            session()->flash('message', _i('Eyepiece %s is not longer active', $eyepiece->name));
        }
    }

    public function delete($id)
    {
        $eyepiece = Eyepiece::where('id', $id)->first();

        if ($eyepiece->observations > 0) {
            session()->flash(
                'message',
                _i(
                    'Eyepiece %s has observations. Impossible to delete.',
                    $eyepiece->name
                )
            );
        } else {
            if (Eyepiece::find($id)->hasMedia('eyepiece')) {
                Eyepiece::find($id)
                    ->getFirstMedia('eyepiece')
                    ->delete();
            }
            $eyepiece->delete();
            $this->emit('refreshLivewireDatatable');

            session()->flash(
                'message',
                _i('Eyepiece %s deleted', $eyepiece->name)
            );
        }
    }

    /**
     * Sets the database values.
     *
     * @return void
     */
    public function mount()
    {
        if (!Auth::guest()) {
            $this->instrument   = Auth::user()->stdtelescope;
            $this->lens         = Auth::user()->stdlens;
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
        if ($propertyName == 'instrument') {
            Auth::user()->update(['stdtelescope' => $this->instrument]);
            $this->emit('refreshLivewireDatatable');
        }

        if ($propertyName == 'lens') {
            Auth::user()->update(['stdlens' => $this->lens]);
            $this->emit('refreshLivewireDatatable');
        }
    }

    public function render()
    {
        return view('livewire.eyepiece.view');
    }
}
