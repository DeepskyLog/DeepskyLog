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

            // Try HorizonsProxy helper first if available
            try {
                if (method_exists(\App\Helpers\HorizonsProxy::class, 'calculateEquatorialCoordinates')) {
                    $designation = $obj->designation ?? $obj->slug ?? $obj->name ?? null;
                    $proxyRes = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates(new \deepskylog\AstronomyLibrary\Targets\Target(), $date, $geo_coords, $userLocation->elevation ?? 0.0, ['designation' => $designation, 'obj' => $obj]);
                    if (is_array($proxyRes) && !empty($proxyRes['coords'])) {
                        $coords = $proxyRes['coords'];
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('EphemeridesBuilder: horizons proxy failed', ['err' => $e->getMessage()]);
            }

            // Fall back to stored RA/Dec fields on the object
            if (empty($coords)) {
                try {
                    if (method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
                        $raDeg = \App\Models\DeepskyObject::raToDecimal($obj->ra ?? $obj->RA ?? null);
                        $decDeg = \App\Models\DeepskyObject::decToDecimal($obj->decl ?? $obj->DEC ?? null);
                        if (is_numeric($raDeg) && is_numeric($decDeg)) {
                            $coords = (object) ['ra' => $raDeg, 'dec' => $decDeg];
                        }
                    }
                } catch (\Throwable $_) {
                    $coords = null;
                }
            }

            if (empty($coords)) {
                return $result;
            }

            // Convert RA in degrees/hours accordingly
            $raVal = $coords->getRA ? (method_exists($coords, 'getRA') ? $coords->getRA() : ($coords->ra ?? null)) : ($coords->ra ?? null);
            $decVal = $coords->getDeclination ? (method_exists($coords, 'getDeclination') ? $coords->getDeclination() : ($coords->dec ?? null)) : ($coords->dec ?? null);

            $raDeg = null;
            if (is_object($raVal) && method_exists($raVal, 'getCoordinate')) {
                $raNum = $raVal->getCoordinate();
            } else {
                $raNum = $raVal;
            }
            if (is_numeric($raNum)) {
                $raDeg = ((float) $raNum <= 24.0) ? (float) $raNum * 15.0 : (float) $raNum;
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
