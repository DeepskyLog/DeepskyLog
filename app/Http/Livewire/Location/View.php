<?php

namespace App\Http\Livewire\Location;

use Livewire\Component;
use App\Models\Location;
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
        $location = Location::where('id', $id)->first();
        if (Auth::user()->stdtelescope == $id) {
            session()->flash('message', _i('Default location can not be deactivated'));
        } else {
            $location->toggleActive();
            if ($location->active) {
                session()->flash('message', _i('Location %s is active', $location->name));
            } else {
                session()->flash('message', _i('Location %s is not longer active', $location->name));
            }
        }
        $this->emit('refreshLivewireDatatable');
    }

    public function standard($id)
    {
        Auth::user()->update(['stdlocation' => $id]);
        $this->emit('refreshLivewireDatatable');
    }

    public function delete($id)
    {
        $location = Location::where('id', $id)->first();

        if ($location->observations > 0) {
            session()->flash(
                'message',
                _i(
                    'Location %s has observations. Impossible to delete.',
                    $location->name
                )
            );
        } else {
            if (Location::find($id)->hasMedia('location')) {
                Location::find($id)
                    ->getFirstMedia('location')
                    ->delete();
            }
            $location->delete();
            $this->emit('refreshLivewireDatatable');

            session()->flash(
                'message',
                _i('Location %s deleted', $location->name)
            );
        }
    }

    public function render()
    {
        return view('livewire.location.view');
    }
}
