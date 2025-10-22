<?php
namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Time;
use Carbon\Carbon;

class ObjectEphemerides extends Component
{
	public $objectId;
	public $ephemerides = null;

	// Listen for the global date change dispatched by EphemerisAside
	protected $listeners = ['ephemerisDateChanged' => 'handleEphemerisDateChange'];

	public function mount($objectId = null)
	{
		$this->objectId = (is_string($objectId) && trim($objectId) === '') ? null : $objectId;
		// Initialize ephemerides on mount so the object page shows values immediately
		try {
			$this->recalculate(['objectId' => $this->objectId]);
		} catch (\Throwable $_) {
			// ignore initialization errors
		}
	}

	public function setObjectId($objectId)
	{
		$this->objectId = (is_string($objectId) && trim($objectId) === '') ? null : $objectId;
		try {
			$this->recalculate(['objectId' => $this->objectId]);
		} catch (\Throwable $_) {}
	}

	public function handleEphemerisDateChange($date)
	{
		// forward the date as part of a normalized payload
		$this->recalculate(['date' => $date, 'objectId' => $this->objectId]);
	}

	/**
	 * Recalculate per-object ephemerides.
	 * Payload may contain: objectId (id or slug) and date (ISO/Y-m-d)
	 */
	public function recalculate($payload = null)
	{
		$this->ephemerides = null;

		try {
			$payloadArr = is_array($payload) ? $payload : (is_object($payload) ? (array)$payload : []);

			// Determine object id/slug to use
			$useObjectId = $payloadArr['objectId'] ?? $this->objectId;
			if (empty($useObjectId)) {
				// Try route/query fallbacks
				try {
					$req = request();
					if ($req && method_exists($req, 'route')) {
						$useObjectId = $req->route('id') ?? $req->route('object') ?? $useObjectId;
					}
					if (empty($useObjectId) && $req) {
						$useObjectId = $req->query('id') ?? $req->query('object') ?? $useObjectId;
					}
				} catch (\Throwable $_) {}
			}

			if (empty($useObjectId)) return;

			// Resolve object record (either numeric id or slug/name)
			$obj = null;
			if (is_numeric((string)$useObjectId)) {
				$obj = \App\Models\DeepskyObject::where('id', $useObjectId)->first();
			} else {
				$found = DB::table('objects')->where('slug', trim((string)$useObjectId))->orWhere('name', trim((string)$useObjectId))->first();
				if ($found) $obj = $found;
			}
			if (empty($obj)) return;

			$authUser = Auth::user();
			$userLocation = $authUser?->standardLocation ?? null;
			if (! $userLocation) return;

			// Date handling: allow payload-provided date
			$date = Carbon::now();
			if (!empty($payloadArr['date'])) {
				try { $date = Carbon::parse($payloadArr['date']); } catch (\Throwable $_) {}
			}
			try { $date = $date->timezone($userLocation->timezone ?? config('app.timezone')); } catch (\Throwable $_) {}

			$geo_coords = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);

			// Prepare target and equatorial coordinates
			$raDeg = null; $decDeg = null;
			try {
				if (method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
					$raDeg = \App\Models\DeepskyObject::raToDecimal($obj->ra ?? $obj->RA ?? null);
					$decDeg = \App\Models\DeepskyObject::decToDecimal($obj->decl ?? $obj->DEC ?? null);
				}
			} catch (\Throwable $_) { $raDeg = null; $decDeg = null; }
			if ($raDeg === null || $decDeg === null) {
				$raDeg = is_numeric($obj->ra ?? null) ? (float)($obj->ra) : $raDeg;
				$decDeg = is_numeric($obj->decl ?? null) ? (float)($obj->decl) : $decDeg;
			}

			if ($raDeg === null || $decDeg === null) return;

			$equa = new EquatorialCoordinates($raDeg, $decDeg);
			$target = new AstroTarget();
			$target->setEquatorialCoordinates($equa);

			$greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
			$deltaT = Time::deltaT($date);

			$target->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);

			$transit = null; $rising = null; $setting = null; $bestTime = null; $maxHeightAtNight = null; $maxHeight = null;
			try { $transit = $target->getTransit(); } catch (\Throwable $_) { $transit = null; }
			try { $rising = $target->getRising(); } catch (\Throwable $_) { $rising = null; }
			try { $setting = $target->getSetting(); } catch (\Throwable $_) { $setting = null; }
			try { $bestTime = $target->getBestTimeToObserve(); } catch (\Throwable $_) { $bestTime = null; }
			try { $maxHeightAtNight = $target->getMaxHeightAtNight(); } catch (\Throwable $_) { $maxHeightAtNight = null; }
			try { $maxHeight = $target->getMaxHeight(); } catch (\Throwable $_) { $maxHeight = null; }

			$tz = $userLocation->timezone ?? config('app.timezone');
			if ($transit instanceof \DateTimeInterface) { try { $transit = Carbon::instance($transit)->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $transit = (string)$transit; } }
			if ($rising instanceof \DateTimeInterface) { try { $rising = Carbon::instance($rising)->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $rising = (string)$rising; } }
			if ($setting instanceof \DateTimeInterface) { try { $setting = Carbon::instance($setting)->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $setting = (string)$setting; } }
			if ($bestTime instanceof \DateTimeInterface) { try { $bestTime = Carbon::instance($bestTime)->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $bestTime = (string)$bestTime; } }

			try { if (is_object($maxHeightAtNight) && method_exists($maxHeightAtNight, 'getCoordinate')) { $maxHeightAtNight = $maxHeightAtNight->getCoordinate(); } } catch (\Throwable $_) {}
			try { if (is_object($maxHeight) && method_exists($maxHeight, 'getCoordinate')) { $maxHeight = $maxHeight->getCoordinate(); } } catch (\Throwable $_) {}
			if (is_numeric($maxHeightAtNight)) $maxHeightAtNight = round($maxHeightAtNight, 1);
			if (is_numeric($maxHeight)) $maxHeight = round($maxHeight, 1);

			$altitudeGraph = null;
			try { $altitudeGraph = $target->altitudeGraph($geo_coords, $date); } catch (\Throwable $_) { $altitudeGraph = null; }

			$this->ephemerides = [
				'date' => $date->timezone($tz)->toDateString(),
				'rising' => $rising,
				'transit' => $transit,
				'setting' => $setting,
				'best_time' => $bestTime,
				'max_height_at_night' => $maxHeightAtNight,
				'max_height' => $maxHeight,
				'altitude_graph' => $altitudeGraph,
			];

		} catch (\Throwable $_) {
			// keep ephemerides null on error
			$this->ephemerides = null;
		}
	}

	public function render()
	{
		return view('livewire.object-ephemerides');
	}
}

