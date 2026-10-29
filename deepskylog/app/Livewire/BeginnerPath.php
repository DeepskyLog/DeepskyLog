<?php

namespace App\Livewire;

use App\Services\BeginnerPath as Path;
use App\Services\EquipmentGain;
use App\Services\RevisitObjects;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * The beginner path (/beginner-path, App\Services\BeginnerPath): starts a
 * path for the observer's instrument (or a starter instrument) and location,
 * and shows its objects in order with the next one, their finding notes and
 * links to the field views and the observation form.
 */
class BeginnerPath extends Component
{
    /** 'instrument:<id>' or 'starter:<key>' (Path::instrument()). */
    public string $instrument = '';

    public string $location = '';

    /** Shows the form to start a new path while one is running. */
    public bool $restarting = false;

    public function mount(): void
    {
        $user             = Auth::user();
        $this->instrument = Path::defaultChoice($user);
        $this->location   = (string) (EquipmentGain::location($user, null)?->id ?? '');
    }

    public function start()
    {
        $user     = Auth::user();
        $location = EquipmentGain::location($user, (int) $this->location ?: null);
        if (! $location) {
            $this->addError('location', __('Add a location to see which objects you can see.'));

            return null;
        }

        $goal = Path::start($user, Path::instrument($user, $this->instrument), $location);
        if (! $goal) {
            $this->addError('instrument', __('No objects suit this instrument and location.'));

            return null;
        }
        session()->flash('success', __('Your beginner path is ready: :count objects.', ['count' => $goal->progress()['total']]));

        return redirect()->route('beginner-path');
    }

    public function render()
    {
        $user     = Auth::user();
        $goal     = Path::current($user);
        $location = EquipmentGain::location($user, null);
        $steps    = $goal ? Path::steps($goal, $location) : [];
        $decode   = fn ($name) => html_entity_decode((string) $name, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return view('livewire.beginner-path', [
            'goal'        => $goal,
            'steps'       => $steps,
            'next'        => Path::next($steps),
            'instruments' => RevisitObjects::instruments($user)
                ->mapWithKeys(fn ($instrument) => ['instrument:' . $instrument->id => $decode($instrument->fullName())])
                ->all(),
            'starters' => collect(array_keys(Path::STARTER_INSTRUMENTS))
                ->mapWithKeys(fn (string $key) => ['starter:' . $key => Path::instrumentName(Path::starter($key))])
                ->all(),
            'locations' => EquipmentGain::locations($user)
                ->mapWithKeys(fn ($location) => [(int) $location->id => $decode($location->name)])
                ->all(),
        ]);
    }
}
