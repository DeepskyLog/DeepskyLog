<?php

namespace Tests\Support;

use App\Livewire\CreateLocation;

// The location form looks the elevation, country and time zone up online.
class CreateLocationCountingLookups extends CreateLocation
{
    public static int $lookups = 0;

    public function updateElevation($latitude, $longitude): void
    {
        self::$lookups++;
    }

    public function updateCountry($latitude, $longitude): void
    {
        self::$lookups++;
    }
}
