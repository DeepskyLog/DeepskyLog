<?php

namespace App\Livewire;

use App\Models\SketchOfTheMonth;
use Carbon\Carbon;
use Livewire\Component;

/**
 * Chooses the sketch of the month: the 8th of a month without one, and one of
 * the sketches of the week of the month before. The choice is posted to
 * SketchOfTheMonthController::store(), which shows the text to share.
 */
class ChooseSketchOfTheMonth extends Component
{
    public string $date = '';

    public function mount(?string $selected = null): void
    {
        $this->date = (string) $selected;
    }

    public function render()
    {
        $dates = SketchOfTheMonth::freeDates();
        $values = array_map(fn (Carbon $date) => $date->toDateString(), $dates);
        if (! in_array($this->date, $values, true)) {
            $this->date = $values[0];
        }

        $date = Carbon::parse($this->date);

        return view('livewire.choose-sketch-of-the-month', [
            'dates' => $dates,
            'month' => $date->copy()->startOfMonth()->subMonthNoOverflow(),
            'candidates' => SketchOfTheMonth::candidates($date),
        ]);
    }
}
