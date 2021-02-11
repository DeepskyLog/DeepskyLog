<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
    private $_eyepieces                       = null;
    private $_lenses                          = null;
    private $_telescope                       = null;
    private ?Location $_location              = null;
    private float $_height                    = 0.0;

    // The constructor is private
    // to prevent initiation with outer code.
    private function __construct()
    {
        $date            = Carbon::now();
        $coords          = new GeographicalCoordinates(0.0, 0.0);
        $this->_astrolib = new AstronomyLibrary($date, $coords);
        if (!Auth::guest()) {
            $this->_eyepieces = \App\Models\Eyepiece::where('user_id', Auth::user()->id)->where('active', 1)->get();
            $this->_lenses    = \App\Models\Lens::where('user_id', Auth::user()->id)->get();
            $this->_telescope = \App\Models\Instrument::where(
                'id',
                Auth::user()->stdtelescope
            )->get()->first();
        }
    }

    public function getAstronomyLibrary(): AstronomyLibrary
    {
        return $this->_astrolib;
    }

    public function setLocation(Location $location)
    {
        $this->_location = $location;
        $this->_height   = $location->elevation;
        $this->_astrolib->setGeographicalCoordinates(new GeographicalCoordinates($location->longitude, $location->latitude));
    }

    public function getLocation():?Location
    {
        return $this->_location;
    }

    public function getGeographicalCoordinates(): ?GeographicalCoordinates
    {
        return $this->_astrolib->getGeographicalCoordinates();
    }

    public function getHeight(): float
    {
        return $this->_height;
    }

    public function getEyepieces()
    {
        return $this->_eyepieces;
    }

    public function getLenses()
    {
        return $this->_lenses;
    }

    public function getTelescope()
    {
        return $this->_telescope;
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
