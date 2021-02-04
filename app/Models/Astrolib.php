<?php

namespace App\Models;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

/**
 * Singleton class, to use only one instance of AstronomyLibrary
 */
class Astrolib
{
    // Hold the class instance.
    private static $instance = null;

    private AstronomyLibrary $_astrolib;
    private ?Location $_location = null;

    // The constructor is private
    // to prevent initiation with outer code.
    private function __construct()
    {
        $date            = Carbon::now();
        $coords          = new GeographicalCoordinates(0.0, 0.0);
        $this->_astrolib = new AstronomyLibrary($date, $coords);
    }

    public function getAstronomyLibrary(): AstronomyLibrary
    {
        return $this->_astrolib;
    }

    public function setLocation(Location $location)
    {
        $this->_location = $location;
    }

    public function getLocation(): ?Location
    {
        return $this->_location;
    }

    // The object is created from within the class itself
    // only if the class has no instance.
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Astrolib();
        }

        return self::$instance;
    }
}
