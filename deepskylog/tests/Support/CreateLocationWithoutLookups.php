<?php

namespace Tests\Support;

use App\Livewire\CreateLocation;

// The location form looks the elevation, country and time zone up online.
class CreateLocationWithoutLookups extends CreateLocation
{
    public function updateElevation($latitude, $longitude): void
    {
        $this->elevation = 10;
    }

    public function updateCountry($latitude, $longitude): void
    {
        $this->country = 'Belgium';
        $this->timezone = 'Europe/Brussels';
    }
}
