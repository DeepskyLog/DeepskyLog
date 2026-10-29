<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\ObserverActivity;
use Livewire\Component;

/**
 * The observing activity calendar on the observer page: a heatmap of the
 * nights with observations of one year, coloured by the number of different
 * objects seen, with the new Moons marked; each night links to the blog page
 * that starts with it.
 */
class ObserverActivityCalendar extends Component
{
    public int $observerId;

    public ?int $year = null;

    public function mount(User $observer): void
    {
        $this->observerId = $observer->id;
    }

    public function render()
    {
        $observer = User::findOrFail($this->observerId);
        $activity = new ObserverActivity($observer);
        $years    = $activity->years();

        if ($years !== [] && ! in_array($this->year, $years, true)) {
            $this->year = $years[0];
        }

        return view('livewire.observer-activity-calendar', [
            'activity' => $activity,
            'years'    => $years,
            'calendar' => $years === [] ? null : $activity->year($this->year),
        ]);
    }
}
