<?php

namespace App\Observers;

use App\Models\Observation;
use App\Models\Instrument;
use App\Models\Eyepiece;
use App\Models\Lens;
use App\Models\Filter;
use App\Models\Location;

class ObservationObserver
{
    public function created(Observation $obs): void
    {
        if ($obs->instrument_id)
            Instrument::where('id', $obs->instrument_id)->increment('observations');
        if ($obs->eyepiece_id)
            Eyepiece::where('id', $obs->eyepiece_id)->increment('observations');
        if ($obs->lens_id)
            Lens::where('id', $obs->lens_id)->increment('observations');
        if ($obs->filter_id)
            Filter::where('id', $obs->filter_id)->increment('observations');
        if ($obs->location_id)
            Location::where('id', $obs->location_id)->increment('observations');
    }

    public function deleted(Observation $obs): void
    {
        if ($obs->instrument_id)
            Instrument::where('id', $obs->instrument_id)->decrement('observations');
        if ($obs->eyepiece_id)
            Eyepiece::where('id', $obs->eyepiece_id)->decrement('observations');
        if ($obs->lens_id)
            Lens::where('id', $obs->lens_id)->decrement('observations');
        if ($obs->filter_id)
            Filter::where('id', $obs->filter_id)->decrement('observations');
        if ($obs->location_id)
            Location::where('id', $obs->location_id)->decrement('observations');
    }

    public function restored(Observation $obs): void
    {
        $this->created($obs);
    }

    public function updated(Observation $obs): void
    {
        if ($obs->isDirty('instrument_id')) {
            $old = $obs->getOriginal('instrument_id');
            $new = $obs->instrument_id;
            if ($old)
                Instrument::where('id', $old)->decrement('observations');
            if ($new)
                Instrument::where('id', $new)->increment('observations');
        }

        if ($obs->isDirty('eyepiece_id')) {
            $old = $obs->getOriginal('eyepiece_id');
            $new = $obs->eyepiece_id;
            if ($old)
                Eyepiece::where('id', $old)->decrement('observations');
            if ($new)
                Eyepiece::where('id', $new)->increment('observations');
        }

        if ($obs->isDirty('lens_id')) {
            $old = $obs->getOriginal('lens_id');
            $new = $obs->lens_id;
            if ($old)
                Lens::where('id', $old)->decrement('observations');
            if ($new)
                Lens::where('id', $new)->increment('observations');
        }

        if ($obs->isDirty('filter_id')) {
            $old = $obs->getOriginal('filter_id');
            $new = $obs->filter_id;
            if ($old)
                Filter::where('id', $old)->decrement('observations');
            if ($new)
                Filter::where('id', $new)->increment('observations');
        }

        if ($obs->isDirty('location_id')) {
            $old = $obs->getOriginal('location_id');
            $new = $obs->location_id;
            if ($old)
                Location::where('id', $old)->decrement('observations');
            if ($new)
                Location::where('id', $new)->increment('observations');
        }
    }
}
