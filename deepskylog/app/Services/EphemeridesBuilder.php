<?php

namespace App\Services;

use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Time;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class EphemeridesBuilder
{
    /**
     * Compute ephemerides for an object and return an array of values.
     * $obj may be an Eloquent model or a plain object with name/designation/ra/decl.
     */
    public static function compute($obj, Carbon $date, $userLocation, GeographicalCoordinates $geo_coords, array $options = [])
    {
        $result = [
            'raDeg' => null,
            'decDeg' => null,
            'mag' => null,
            'diam1' => null,
            'diam2' => null,
            'transit' => null,
            'rising' => null,
            'setting' => null,
            'bestTime' => null,
            'illuminated_fraction' => null,
        ];

        try {
            $coords = null;

            // Comets: calculate the position from the orbital elements
            try {
                $name = $obj->name ?? $obj->designation ?? null;
                if ($obj instanceof \App\Models\CometObject || (is_string($name) && preg_match('#^\s*(\d+[PDIX]\b|[CPDXAI]/)#i', $name))) {
                    $coords = CometCoordinates::forName($name, $date, $geo_coords, (float) ($userLocation->elevation ?? 0.0));
                }
            } catch (\Throwable $e) {
                Log::debug('EphemeridesBuilder: comet coordinates failed', ['err' => $e->getMessage()]);
            }

            // Fall back to stored RA/Dec fields on the object
            if (empty($coords)) {
                try {
                    if (method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
                        $raDeg = \App\Models\DeepskyObject::raToDecimal($obj->ra ?? $obj->RA ?? null);
                        $decDeg = \App\Models\DeepskyObject::decToDecimal($obj->decl ?? $obj->DEC ?? null);
                        if (is_numeric($raDeg) && is_numeric($decDeg)) {
                            // RA in hours, like the comet coordinates above
                            $coords = (object) ['ra' => $raDeg / 15.0, 'dec' => $decDeg];
                        }
                    }
                } catch (\Throwable $_) {
                    $coords = null;
                }
            }

            if (empty($coords)) {
                return $result;
            }

            // RA is in hours here, both from the comet coordinates and from the fallback
            $raVal = method_exists($coords, 'getRA') ? $coords->getRA() : ($coords->ra ?? null);
            $decVal = method_exists($coords, 'getDeclination') ? $coords->getDeclination() : ($coords->dec ?? null);

            $raDeg = null;
            if (is_object($raVal) && method_exists($raVal, 'getCoordinate')) {
                $raNum = $raVal->getCoordinate();
            } else {
                $raNum = $raVal;
            }
            if (is_numeric($raNum)) {
                $raDeg = (float) $raNum * 15.0;
            }

            $decDeg = null;
            if (is_object($decVal) && method_exists($decVal, 'getCoordinate')) {
                $decNum = $decVal->getCoordinate();
            } else {
                $decNum = $decVal;
            }
            if (is_numeric($decNum)) {
                $decDeg = (float) $decNum;
            }

            if ($raDeg === null || $decDeg === null)
                return $result;

            $raHours = (float) $raDeg / 15.0;
            $equa = new EquatorialCoordinates($raHours, (float) $decDeg);
            $target = new AstroTarget();
            $target->setEquatorialCoordinates($equa);

            $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
            $deltaT = Time::deltaT($date);

            try {
                $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);
            } catch (\Throwable $_) {
                return $result;
            }

            // extract some values where available
            try {
                if (method_exists($target, 'getMagnitude')) {
                    $result['mag'] = $target->getMagnitude();
                }
            } catch (\Throwable $_) {
            }
            try {
                if (method_exists($target, 'getDiameter')) {
                    $pd = $target->getDiameter();
                    if (is_array($pd) && isset($pd[0])) {
                        $result['diam1'] = $pd[0];
                        $result['diam2'] = $pd[1] ?? $pd[0];
                    }
                }
            } catch (\Throwable $_) {
            }

            // Best-effort times: transit/rise/set
            try {
                if (method_exists($target, 'getTransit'))
                    $result['transit'] = $target->getTransit();
            } catch (\Throwable $_) {
            }
            try {
                if (method_exists($target, 'getRising'))
                    $result['rising'] = $target->getRising();
            } catch (\Throwable $_) {
            }
            try {
                if (method_exists($target, 'getSetting'))
                    $result['setting'] = $target->getSetting();
            } catch (\Throwable $_) {
            }

            $result['raDeg'] = $raDeg;
            $result['decDeg'] = $decDeg;

            // illuminated fraction if available
            try {
                if (method_exists($target, 'illuminatedFraction')) {
                    $v = $target->illuminatedFraction($date);
                    if (is_numeric($v))
                        $result['illuminated_fraction'] = (float) $v;
                }
            } catch (\Throwable $_) {
            }

        } catch (\Throwable $e) {
            Log::debug('EphemeridesBuilder: compute failed', ['err' => $e->getMessage()]);
        }

        return $result;
    }
}
