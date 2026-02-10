<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Time;
use Carbon\Carbon;
use App\Models\Location;
use App\Models\Constellation as ConstellationModel;

class CometDetails extends Component
{
    public $objectId;
    public $magnitudes = [];
    public $ephemerides = null;
    public $sourceTypeRaw = null;
    // Track the current aside-selected date (Y-m-d) to ignore out-of-order payloads
    public $asideDate = null;
    // Timestamp of last-applied ephemerides payload to avoid out-of-order updates
    public $lastEphemeridesTs = null;

    protected $listeners = [
        'objectEphemeridesUpdated' => 'handleEphemeridesUpdated',
        'aladinPreviewUpdated' => 'handleEphemeridesUpdated',
        // Listen for the aside date change so we can verify incoming
        // `objectEphemeridesUpdated` payloads match the currently-selected date.
        'ephemerisDateChanged' => 'setAsideDate',
    ];


    public function mount($objectId = null, $initial = null)
    {
        $this->objectId = $objectId;
        if (is_array($initial)) {
            if (isset($initial['magnitudes'])) $this->magnitudes = $initial['magnitudes'];
            if (isset($initial['ephemerides'])) $this->ephemerides = $initial['ephemerides'];
            if (isset($initial['sourceTypeRaw'])) $this->sourceTypeRaw = $initial['sourceTypeRaw'];
            try {
                $this->ensureAltitudeGraphs();
            } catch (\Throwable $_) {
            }
        }

        // Keep mount lightweight; rely on the hidden `object-ephemerides`
        // component to recompute and emit `objectEphemeridesUpdated`.
    }

    public function handleEphemeridesUpdated($payload)
    {
        try {
            Log::debug('CometDetails: handleEphemeridesUpdated received', ['objectId' => $this->objectId, 'payload' => is_array($payload) ? $payload : (is_object($payload) ? (array)$payload : $payload)]);
        } catch (\Throwable $_) {
        }
        try {
            if (!is_array($payload)) {
                // sometimes Livewire will forward an object-like payload
                $payload = (array) $payload;
            }
            $oid = $payload['objectId'] ?? ($payload['objectSlug'] ?? null);
            // Determine payload date (prefer ephemerides.date if present)
            $payloadDate = null;
            if (isset($payload['ephemerides']) && is_array($payload['ephemerides']) && isset($payload['ephemerides']['date'])) {
                $payloadDate = $payload['ephemerides']['date'];
            } elseif (isset($payload['date'])) {
                try {
                    $payloadDate = Carbon::parse($payload['date'])->toDateString();
                } catch (\Throwable $_) {
                    $payloadDate = null;
                }
            }
            // If we have a known aside date and the payload's date doesn't match,
            // skip this payload to avoid applying an out-of-order/previous value.
            if (!empty($this->asideDate) && !empty($payloadDate) && $this->asideDate !== $payloadDate) {
                try {
                    Log::debug('CometDetails: ignoring ephemerides payload with mismatched date', ['objectId' => $this->objectId, 'payloadDate' => $payloadDate, 'asideDate' => $this->asideDate]);
                } catch (\Throwable $_) {
                }
                return;
            }

            // Respect payload timestamp ordering: only apply payloads newer than
            // the last applied to avoid race-condition where an older recompute
            // overwrites a newer one. Payloads from EphemerisAside/ObjectEphemerides
            // include an `_ts` ISO timestamp when emitted.
            $payloadTs = null;
            try {
                if (isset($payload['_ts'])) {
                    $payloadTs = Carbon::parse($payload['_ts'])->getTimestamp();
                } elseif (isset($payload['ephemerides']) && is_array($payload['ephemerides']) && isset($payload['ephemerides']['_ts'])) {
                    $payloadTs = Carbon::parse($payload['ephemerides']['_ts'])->getTimestamp();
                }
            } catch (\Throwable $_) {
                $payloadTs = null;
            }
            if (!is_null($payloadTs) && !is_null($this->lastEphemeridesTs) && $payloadTs < $this->lastEphemeridesTs) {
                try {
                    Log::debug('CometDetails: ignoring older ephemerides payload by timestamp', ['objectId' => $this->objectId, 'payloadTs' => $payloadTs, 'lastTs' => $this->lastEphemeridesTs]);
                } catch (\Throwable $_) {
                }
                return;
            }
            if ($oid == $this->objectId || (string) $oid === (string) $this->objectId) {
                // update ephemerides payload when available
                if (isset($payload['ephemerides'])) {
                    $this->ephemerides = $payload['ephemerides'];
                } else {
                    // payload may be the ephemerides itself
                    $this->ephemerides = $payload;
                }
                // record last-applied timestamp
                try {
                    if (!is_null($payloadTs)) $this->lastEphemeridesTs = $payloadTs;
                } catch (\Throwable $_) {
                }
                try {
                    $this->ensureAltitudeGraphs();
                } catch (\Throwable $_) {
                    // non-fatal
                }
            }
        } catch (\Throwable $_) {
            // swallow errors to avoid breaking Livewire listeners
        }
    }

    public function setAsideDate($date)
    {
        try {
            $this->asideDate = $date ? Carbon::parse($date)->toDateString() : null;
        } catch (\Throwable $_) {
            $this->asideDate = null;
        }
    }

    /**
     * Attempt to compute altitude and year graphs if missing using the
     * laravel-astronomy-library Target helper.
     */
    protected function ensureAltitudeGraphs()
    {
        if (empty($this->ephemerides) || !is_array($this->ephemerides)) return;
        // If graphs already present, nothing to do
        $hasAlt = !empty($this->ephemerides['altitude_graph']);
        $hasYear = !empty($this->ephemerides['year_graph']);
        if ($hasAlt && $hasYear) return;

        // Need ra/dec to build target
        $raDeg = $this->ephemerides['raDeg'] ?? null;
        $decDeg = $this->ephemerides['decDeg'] ?? null;
        if (!is_numeric($raDeg) || !is_numeric($decDeg)) return;

        // obtain a location: prefer authenticated user's standardLocation,
        // otherwise fall back to the first active Location row (same as EphemerisAside)
        try {
            $user = auth()->user();
            $loc = null;
            if ($user && method_exists($user, 'standardLocation') && $user->standardLocation) {
                $loc = $user->standardLocation;
            } else {
                try {
                    $loc = Location::where('active', 1)->first();
                } catch (\Throwable $_) {
                    $loc = null;
                }
            }
            if (! $loc || ! isset($loc->longitude) || ! isset($loc->latitude)) return;
        } catch (\Throwable $_) {
            return;
        }

        try {
            $dateStr = $this->ephemerides['date'] ?? null;
            $date = $dateStr ? Carbon::parse($dateStr) : Carbon::now();
        } catch (\Throwable $_) {
            $date = Carbon::now();
        }

        try {
            $geo = new GeographicalCoordinates($loc->longitude, $loc->latitude);
            $raHours = (float)$raDeg / 15.0;
            $equa = new EquatorialCoordinates($raHours, (float)$decDeg);
            $target = new AstroTarget();
            $target->setEquatorialCoordinates($equa);

            $gst = Time::apparentSiderialTimeGreenwich($date);
            $dT = Time::deltaT($date);
            try {
                $target->calculateEphemerides($geo, $gst, $dT);
            } catch (\Throwable $_) {
                try {
                    $target->calculateEphemerides($geo);
                } catch (\Throwable $_) {
                }
            }

            if (! $hasAlt) {
                try {
                    $alt = $target->altitudeGraph($geo, $date);
                    if ($alt) $this->ephemerides['altitude_graph'] = $alt;
                } catch (\Throwable $_) {
                }
            }
            if (! $hasYear) {
                try {
                    $yg = $target->yearGraph($geo, $date);
                    if ($yg) $this->ephemerides['year_graph'] = $yg;
                } catch (\Throwable $_) {
                }
            }
            // Compute constellation if missing
            if (empty($this->ephemerides['constellation']) || empty($this->ephemerides['constellation_code'])) {
                try {
                    $consName = null;
                    $consCode = null;
                    if (method_exists($equa, 'getConstellation')) {
                        try {
                            $c = $equa->getConstellation();
                            if (is_string($c) && !empty($c)) $consName = $c;
                            elseif (is_object($c)) {
                                if (isset($c->name)) $consName = $c->name;
                                if (isset($c->id)) $consCode = $c->id;
                            }
                        } catch (\Throwable $_) {
                        }
                    }
                    if (!$consName && method_exists($equa, 'constellation')) {
                        try {
                            $c = $equa->constellation();
                            if (is_string($c) && !empty($c)) $consName = $c;
                        } catch (\Throwable $_) {
                        }
                    }
                    if ($consName) {
                        try {
                            $found = ConstellationModel::where('name', $consName)->orWhere('id', $consName)->first();
                            if ($found) {
                                $consName = $found->name;
                                $consCode = $found->id;
                            }
                        } catch (\Throwable $_) {
                        }
                    }
                    if ($consName) $this->ephemerides['constellation'] = $consName;
                    if ($consCode) $this->ephemerides['constellation_code'] = $consCode;
                } catch (\Throwable $_) {
                }
            }

            // Compute max heights if missing
            if (!isset($this->ephemerides['max_height']) || !isset($this->ephemerides['max_height_at_night'])) {
                try {
                    $mh = null;
                    $mhn = null;
                    try {
                        $mhn = $target->getMaxHeightAtNight();
                    } catch (\Throwable $_) {
                        $mhn = null;
                    }
                    try {
                        $mh = $target->getMaxHeight();
                    } catch (\Throwable $_) {
                        $mh = null;
                    }
                    try {
                        if (is_object($mhn) && method_exists($mhn, 'getCoordinate')) $mhn = $mhn->getCoordinate();
                    } catch (\Throwable $_) {
                    }
                    try {
                        if (is_object($mh) && method_exists($mh, 'getCoordinate')) $mh = $mh->getCoordinate();
                    } catch (\Throwable $_) {
                    }
                    if (is_numeric($mhn)) $mhn = round($mhn, 1);
                    if (is_numeric($mh)) $mh = round($mh, 1);
                    if ($mhn !== null) $this->ephemerides['max_height_at_night'] = $mhn;
                    if ($mh !== null) $this->ephemerides['max_height'] = $mh;
                } catch (\Throwable $_) {
                }
            }
        } catch (\Throwable $_) {
            // swallow any errors to avoid breaking Livewire
        }
    }

    public function render()
    {
        return view('livewire.comet-details');
    }
}
