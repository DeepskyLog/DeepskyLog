<?php

namespace App\Http\Livewire\Instrument;

use Livewire\Component;
use App\Models\Instrument;
use Illuminate\Support\Facades\Auth;

class View extends Component
{
    protected $listeners = [
        'activate' => 'activate',
        'delete'   => 'delete',
        'standard' => 'standard',
    ];

    public function activate($id)
    {
        $instrument = Instrument::where('id', $id)->first();
        $instrument->toggleActive();
        if ($instrument->active) {
            session()->flash('message', _i('Instrument %s is active', $instrument->name));
        } else {
            session()->flash('message', _i('Instrument %s is not longer active', $instrument->name));
        }
        $this->emit('refreshLivewireDatatable');
    }

    public function standard($id)
    {
        Auth::user()->update(['stdtelescope' => $id]);
        $this->emit('refreshLivewireDatatable');
    }

    public function delete($id)
    {
        $instrument = Instrument::where('id', $id)->first();

        if ($instrument->observations > 0) {
            session()->flash(
                'message',
                _i(
                    'Instrument %s has observations. Impossible to delete.',
                    $instrument->name
                )
            );
        } else {
            if (Instrument::find($id)->hasMedia('instrument')) {
                Instrument::find($id)
                    ->getFirstMedia('instrument')
                    ->delete();
            }
            $instrument->delete();
            $this->emit('refreshLivewireDatatable');

            session()->flash(
                'message',
                _i('Instrument %s deleted', $instrument->name)
            );
        }
    }

    public function render()
    {
        return view('livewire.instrument.view');
    }
}
