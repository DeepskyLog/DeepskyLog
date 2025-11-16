<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Time;
use Carbon\Carbon;
use App\Models\Constellation as ConstellationModel;

class ObjectEphemerides extends Component
{
	public $objectId;
	public $objectName;
	public $sourceTypeRaw;
	public $ephemerides = null;

	// Listen for the global date change dispatched by EphemerisAside
	protected $listeners = ['ephemerisDateChanged' => 'handleEphemerisDateChange'];

	public function mount($objectId = null, $initial = null, $objectName = null, $sourceTypeRaw = null)
	{
		$this->objectId = (is_string($objectId) && trim($objectId) === '') ? null : $objectId;
		$this->objectName = $objectName ?? null;
		$this->sourceTypeRaw = $sourceTypeRaw ?? null;

		if (! empty($initial) && is_array($initial)) {
			$this->ephemerides = $initial;
			// If initial payload is missing planet-specific event fields or appearance
			// values, trigger a server-side recalc so logged-in pages get the
			// same authoritative values Livewire computes for guests.
			$needsRecalc = false;
			$requiredKeys = ['raDeg', 'decDeg', 'inferior_conjunction', 'superior_conjunction', 'greatest_western_elongation', 'greatest_eastern_elongation', 'diam1', 'mag'];
			foreach ($requiredKeys as $k) {
				if (! array_key_exists($k, $initial) || $initial[$k] === null) {
					$needsRecalc = true;
					break;
				}
			}
			if ($needsRecalc) {
				try {
					$this->recalculate(['objectId' => $this->objectId, 'objectName' => $this->objectName, 'sourceTypeRaw' => $this->sourceTypeRaw]);
				} catch (\Throwable $_) {
					// keep provided initial if recalc fails
				}
			}
			return;
		}

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
		} catch (\Throwable $_) {
		}
	}

	public function handleEphemerisDateChange($date)
	{
		// forward the date as part of a normalized payload
		$this->recalculate(['date' => $date, 'objectId' => $this->objectId, 'objectName' => $this->objectName, 'sourceTypeRaw' => $this->sourceTypeRaw]);
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
				try {
					$req = request();
					if ($req && method_exists($req, 'route')) {
						$useObjectId = $req->route('id') ?? $req->route('object') ?? $useObjectId;
					}
					if (empty($useObjectId) && $req) {
						$useObjectId = $req->query('id') ?? $req->query('object') ?? $useObjectId;
					}
				} catch (\Throwable $_) {
				}
			}

			if (empty($useObjectId)) return;

			// Resolve object record (either numeric id or slug/name)
			$obj = null;
			try {
				if (is_numeric((string)$useObjectId)) {
					$possibleIdCols = ['id', 'objectid', 'object_id', 'objectID', 'pk', 'objid'];
					$usedCol = null;
					foreach ($possibleIdCols as $col) {
						try {
							if (Schema::hasColumn('objects', $col)) {
								$usedCol = $col;
								break;
							}
						} catch (\Throwable $_) {
						}
					}
					if ($usedCol) {
						$found = DB::table('objects')->where($usedCol, $useObjectId)->first();
						if ($found) $obj = $found;
					} else {
						$found = DB::table('objects')->where('slug', trim((string)$useObjectId))->orWhere('name', trim((string)$useObjectId))->first();
						if ($found) $obj = $found;
					}
				} else {
					$found = DB::table('objects')->where('slug', trim((string)$useObjectId))->orWhere('name', trim((string)$useObjectId))->first();
					if ($found) $obj = $found;
				}

				if (empty($obj)) {
					$planetName = $payloadArr['objectName'] ?? $this->objectName ?? null;
					$sourceType = $payloadArr['sourceTypeRaw'] ?? $this->sourceTypeRaw ?? null;
					if (!empty($planetName) && is_string($sourceType) && mb_strtolower($sourceType) === 'planet') {
						$obj = (object)['id' => null, 'name' => $planetName];
					} else {
						return;
					}
				}
			} catch (\Throwable $_) {
				return;
			}

			$authUser = Auth::user();
			$userLocation = $authUser?->standardLocation ?? null;
			// If no authenticated user's standard location is available (guest view),
			// attempt to use a public/default active Location so we can still compute
			// ephemerides for anonymous visitors (useful for planets like Mercury/Venus).
			if (! $userLocation) {
				try {
					// Prefer an active location with no user_id (site-level location)
					$userLocation = \App\Models\Location::where('active', true)->whereNull('user_id')->first();
					// Fallback: any active location
					if (! $userLocation) {
						$userLocation = \App\Models\Location::where('active', true)->first();
					}
				} catch (\Throwable $_) {
					$userLocation = null;
				}
			}
			if (! $userLocation) return;

			// Date handling: allow payload-provided date
			$date = Carbon::now();
			if (!empty($payloadArr['date'])) {
				try {
					$date = Carbon::parse($payloadArr['date']);
				} catch (\Throwable $_) {
				}
			}
			try {
				$date = $date->timezone($userLocation->timezone ?? config('app.timezone'));
			} catch (\Throwable $_) {
			}

			$geo_coords = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);

			// Prepare target and equatorial coordinates (degrees)
			$raDeg = null;
			$decDeg = null;
			$computedDiam1 = null;
			$computedDiam2 = null;
			$computedMag = null;
			try {
				if (method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
					$raDeg = \App\Models\DeepskyObject::raToDecimal($obj->ra ?? $obj->RA ?? null);
					$decDeg = \App\Models\DeepskyObject::decToDecimal($obj->decl ?? $obj->DEC ?? null);
				}
			} catch (\Throwable $_) {
				$raDeg = null;
				$decDeg = null;
			}

			// If this appears to be a planet, always try to compute up-to-date
			// equatorial coordinates, diameter and magnitude for the requested
			// date using the AstronomyLibrary. We do this even if the DB has
			// stored RA/Dec to ensure date-dependent values are fresh.
			try {
				$planetName = $obj->name ?? null;
				$sourceType = $payloadArr['sourceTypeRaw'] ?? $this->sourceTypeRaw ?? ($obj->source_type_raw ?? $obj->source_type ?? null);
				$isPlanet = false;
				if (is_string($sourceType) && mb_strtolower($sourceType) === 'planet') {
					$isPlanet = true;
				}
				if (! $isPlanet && is_string($planetName)) {
					$key = preg_replace('/[^a-z]/', '', mb_strtolower($planetName));
					$map = [
						'mercury' => 'Mercury',
						'venus' => 'Venus',
						'earth' => 'Earth',
						'mars' => 'Mars',
						'jupiter' => 'Jupiter',
						'saturn' => 'Saturn',
						'uranus' => 'Uranus',
						'neptune' => 'Neptune',
						'pluto' => 'Pluto',
						'sun' => 'Sun',
						'moon' => 'Moon',
					];
					if ($key && isset($map[$key])) {
						$isPlanet = true;
					}
				}

				if ($isPlanet && is_string($planetName)) {
					$map = ['mercury' => 'Mercury', 'venus' => 'Venus', 'earth' => 'Earth', 'mars' => 'Mars', 'jupiter' => 'Jupiter', 'saturn' => 'Saturn', 'uranus' => 'Uranus', 'neptune' => 'Neptune', 'pluto' => 'Pluto', 'sun' => 'Sun', 'moon' => 'Moon'];
					$key = preg_replace('/[^a-z]/', '', mb_strtolower($planetName));
					if (isset($map[$key])) {
						$fqcn = "\\deepskylog\\AstronomyLibrary\\Targets\\{$map[$key]}";
						if (class_exists($fqcn)) {
							$planet = new $fqcn();
							try {
								if ($userLocation && isset($userLocation->longitude) && isset($userLocation->latitude)) {
									$geo = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);
									$height = $userLocation->elevation ?? 0.0;
									if (method_exists($planet, 'calculateEquatorialCoordinates')) {
										$planet->calculateEquatorialCoordinates($date, $geo, $height);
									}
									if (method_exists($planet, 'calculateApparentEquatorialCoordinates')) {
										$planet->calculateApparentEquatorialCoordinates($date);
									}
								} else {
									if (method_exists($planet, 'calculateApparentEquatorialCoordinates')) {
										$planet->calculateApparentEquatorialCoordinates($date);
									}
								}
							} catch (\Throwable $_) {
								try {
									if (method_exists($planet, 'calculateApparentEquatorialCoordinates')) {
										$planet->calculateApparentEquatorialCoordinates($date);
									}
								} catch (\Throwable $_) {
									// give up
								}
							}

							// Extract equatorial coordinates if available
							$coords = null;
							try {
								if (method_exists($planet, 'getEquatorialCoordinatesToday')) {
									$coords = $planet->getEquatorialCoordinatesToday();
								} elseif (method_exists($planet, 'getEquatorialCoordinates')) {
									$coords = $planet->getEquatorialCoordinates();
								}
							} catch (\Throwable $_) {
								$coords = null;
							}

							if ($coords) {
								// RA
								try {
									if (method_exists($coords, 'getRA')) {
										$raObj = $coords->getRA();
									} else {
										$raObj = $coords->ra ?? null;
									}
									if (is_object($raObj) && method_exists($raObj, 'getCoordinate')) {
										$raVal = $raObj->getCoordinate();
									} else {
										$raVal = $raObj;
									}
									if (is_numeric($raVal)) {
										if ((float)$raVal <= 24.0) {
											$raDeg = (float)$raVal * 15.0;
										} else {
											$raDeg = (float)$raVal;
										}
									}
								} catch (\Throwable $_) {
									$raDeg = null;
								}

								// Dec
								try {
									if (method_exists($coords, 'getDeclination')) {
										$decObj = $coords->getDeclination();
									} else {
										$decObj = $coords->dec ?? null;
									}
									if (is_object($decObj) && method_exists($decObj, 'getCoordinate')) {
										$decVal = $decObj->getCoordinate();
									} else {
										$decVal = $decObj;
									}
									if (is_numeric($decVal)) {
										$decDeg = (float)$decVal;
									}
								} catch (\Throwable $_) {
									$decDeg = null;
								}
							}

							// diameter and magnitude when supported
							try {
								if (method_exists($planet, 'calculateDiameter')) {
									$planet->calculateDiameter($date);
									$pd = $planet->getDiameter();
									if (is_array($pd) && isset($pd[0])) {
										$computedDiam1 = $pd[0];
										$computedDiam2 = $pd[1] ?? $pd[0];
									}
								}
							} catch (\Throwable $_) {
							}
							try {
								if (method_exists($planet, 'magnitude')) {
									$computedMag = $planet->magnitude($date);
								}
							} catch (\Throwable $_) {
							}
							// For inner planets (Mercury, Venus) compute notable events: inferior/superior
							// conjunction and greatest eastern/western elongation when available.
							$inferiorConjunction = null;
							$superiorConjunction = null;
							$greatestWesternElongation = null;
							$greatestEasternElongation = null;
							// illuminated fraction (0..1)
							$illuminatedFraction = null;
							// Opposition/conjunction for outer planets and perihelion/aphelion for all
							$opposition = null;
							$conjunction = null;
							$perihelionDate = null;
							$aphelionDate = null;
							try {
								if (method_exists($planet, 'inferior_conjunction')) {
									$v = $planet->inferior_conjunction($date);
									if ($v instanceof \DateTimeInterface) $inferiorConjunction = Carbon::instance($v)->toIso8601String();
									elseif (is_string($v)) $inferiorConjunction = $v;
								}
							} catch (\Throwable $_) {
								$inferiorConjunction = null;
							}
							try {
								if (method_exists($planet, 'superior_conjunction')) {
									$v = $planet->superior_conjunction($date);
									if ($v instanceof \DateTimeInterface) $superiorConjunction = Carbon::instance($v)->toIso8601String();
									elseif (is_string($v)) $superiorConjunction = $v;
								}
							} catch (\Throwable $_) {
								$superiorConjunction = null;
							}
							try {
								if (method_exists($planet, 'greatest_western_elongation')) {
									$v = $planet->greatest_western_elongation($date);
									if ($v instanceof \DateTimeInterface) $greatestWesternElongation = Carbon::instance($v)->toIso8601String();
									elseif (is_string($v)) $greatestWesternElongation = $v;
								}
							} catch (\Throwable $_) {
								$greatestWesternElongation = null;
							}
							try {
								if (method_exists($planet, 'greatest_eastern_elongation')) {
									$v = $planet->greatest_eastern_elongation($date);
									if ($v instanceof \DateTimeInterface) $greatestEasternElongation = Carbon::instance($v)->toIso8601String();
									elseif (is_string($v)) $greatestEasternElongation = $v;
								}
							} catch (\Throwable $_) {
								$greatestEasternElongation = null;
							}
							// illuminated fraction if provided by the target
							try {
								if (method_exists($planet, 'illuminatedFraction')) {
									$v = $planet->illuminatedFraction($date);
									if (is_numeric($v)) $illuminatedFraction = (float)$v;
									elseif ($v instanceof \JsonSerializable || is_string($v)) $illuminatedFraction = (float)$v;
								}
							} catch (\Throwable $_) {
								$illuminatedFraction = null;
							}
							// Opposition / conjunction for planets that support these calls
							try {
								if (method_exists($planet, 'opposition')) {
									$v = $planet->opposition($date);
									if ($v instanceof \DateTimeInterface) $opposition = Carbon::instance($v)->toIso8601String();
									elseif (is_string($v)) $opposition = $v;
								}
							} catch (\Throwable $_) {
								$opposition = null;
							}
							try {
								if (method_exists($planet, 'conjunction')) {
									$v = $planet->conjunction($date);
									if ($v instanceof \DateTimeInterface) $conjunction = Carbon::instance($v)->toIso8601String();
									elseif (is_string($v)) $conjunction = $v;
								}
							} catch (\Throwable $_) {
								$conjunction = null;
							}
							// Perihelion / Aphelion if supported
							try {
								if (method_exists($planet, 'perihelionDate')) {
									$v = $planet->perihelionDate($date);
									if ($v instanceof \DateTimeInterface) $perihelionDate = Carbon::instance($v)->toIso8601String();
									elseif (is_string($v)) $perihelionDate = $v;
								}
							} catch (\Throwable $_) {
								$perihelionDate = null;
							}
							try {
								if (method_exists($planet, 'aphelionDate')) {
									$v = $planet->aphelionDate($date);
									if ($v instanceof \DateTimeInterface) $aphelionDate = Carbon::instance($v)->toIso8601String();
									elseif (is_string($v)) $aphelionDate = $v;
								}
							} catch (\Throwable $_) {
								$aphelionDate = null;
							}
						}
					}
				}
			} catch (\Throwable $_) {
				// planet compute failed; keep whatever ra/dec we have
			}

			// Temporary debug: log computed inner-planet events and appearance values
			try {
				\Illuminate\Support\Facades\Log::info('ObjectEphemerides: planet events', [
					'planet' => $planetName ?? null,
					'date' => isset($date) ? $date->toDateString() : null,
					'inferior_conjunction' => $inferiorConjunction ?? null,
					'superior_conjunction' => $superiorConjunction ?? null,
					'greatest_western_elongation' => $greatestWesternElongation ?? null,
					'greatest_eastern_elongation' => $greatestEasternElongation ?? null,
					'opposition' => $opposition ?? null,
					'conjunction' => $conjunction ?? null,
					'perihelion' => $perihelionDate ?? null,
					'aphelion' => $aphelionDate ?? null,
					'illuminated_fraction' => $illuminatedFraction ?? null,
					'diam1' => $computedDiam1 ?? null,
					'diam2' => $computedDiam2 ?? null,
					'mag' => $computedMag ?? null,
				]);
			} catch (\Throwable $_) {
				// ignore logging errors
			}

			if ($raDeg === null || $decDeg === null) {
				// unable to determine coordinates
				$this->ephemerides = null;
				return;
			}



			// EquatorialCoordinates expects RA in hours (0..24).
			$raHours = (float)$raDeg / 15.0;
			$equa = new EquatorialCoordinates($raHours, (float)$decDeg);
			$target = new AstroTarget();
			$target->setEquatorialCoordinates($equa);

			$greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
			$deltaT = Time::deltaT($date);

			try {
				$target->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);
			} catch (\Throwable $_) {
				$this->ephemerides = null;
				return;
			}

			$transit = null;
			$rising = null;
			$setting = null;
			$bestTime = null;
			$maxHeightAtNight = null;
			$maxHeight = null;
			try {
				$transit = $target->getTransit();
			} catch (\Throwable $_) {
				$transit = null;
			}
			try {
				$rising = $target->getRising();
			} catch (\Throwable $_) {
				$rising = null;
			}
			try {
				$setting = $target->getSetting();
			} catch (\Throwable $_) {
				$setting = null;
			}
			try {
				$bestTime = $target->getBestTimeToObserve();
			} catch (\Throwable $_) {
				$bestTime = null;
			}
			try {
				$maxHeightAtNight = $target->getMaxHeightAtNight();
			} catch (\Throwable $_) {
				$maxHeightAtNight = null;
			}
			try {
				$maxHeight = $target->getMaxHeight();
			} catch (\Throwable $_) {
				$maxHeight = null;
			}

			$tz = $userLocation->timezone ?? config('app.timezone');
			if ($transit instanceof \DateTimeInterface) {
				try {
					$transit = Carbon::instance($transit)->timezone($tz)->isoFormat('HH:mm');
				} catch (\Throwable $_) {
					$transit = (string)$transit;
				}
			}
			if ($rising instanceof \DateTimeInterface) {
				try {
					$rising = Carbon::instance($rising)->timezone($tz)->isoFormat('HH:mm');
				} catch (\Throwable $_) {
					$rising = (string)$rising;
				}
			}
			if ($setting instanceof \DateTimeInterface) {
				try {
					$setting = Carbon::instance($setting)->timezone($tz)->isoFormat('HH:mm');
					$setting = (string)$setting;
				} catch (\Throwable $_) {
				}
			}
			if ($bestTime instanceof \DateTimeInterface) {
				try {
					$bestTime = Carbon::instance($bestTime)->timezone($tz)->isoFormat('HH:mm');
				} catch (\Throwable $_) {
					$bestTime = (string)$bestTime;
				}
			}

			try {
				if (is_object($maxHeightAtNight) && method_exists($maxHeightAtNight, 'getCoordinate')) {
					$maxHeightAtNight = $maxHeightAtNight->getCoordinate();
				}
			} catch (\Throwable $_) {
			}
			try {
				if (is_object($maxHeight) && method_exists($maxHeight, 'getCoordinate')) {
					$maxHeight = $maxHeight->getCoordinate();
				}
			} catch (\Throwable $_) {
			}
			if (is_numeric($maxHeightAtNight)) $maxHeightAtNight = round($maxHeightAtNight, 1);
			if (is_numeric($maxHeight)) $maxHeight = round($maxHeight, 1);

			$altitudeGraph = null;
			try {
				$altitudeGraph = $target->altitudeGraph($geo_coords, $date);
			} catch (\Throwable $_) {
				$altitudeGraph = null;
			}
			$yearGraph = null;
			try {
				$yearGraph = $target->yearGraph($geo_coords, $date);
			} catch (\Throwable $_) {
				$yearGraph = null;
			}
			$yearMagGraph = null;
			try {
				// yearMagnitudeGraph is implemented in the AstronomyLibrary Target
				// and returns an embedded image. Prefer using the concrete planet
				// target instance when available (it implements magnitude(date)).
				if (isset($planet) && $planet) {
					try {
						$yearMagGraph = $planet->yearMagnitudeGraph($geo_coords, $date);
					} catch (\Throwable $_) {
						// fallback to generic target below
						$yearMagGraph = null;
					}
				}
				if (empty($yearMagGraph)) {
					// Ensure the generic target has a magnitude value so sampling
					// inside yearMagnitudeGraph() can fall back to getMagnitude().
					try {
						if (isset($computedMag) && $computedMag !== null) {
							$target->setMagnitude($computedMag);
						} elseif (isset($obj) && isset($obj->mag) && $obj->mag != 99.9) {
							$target->setMagnitude($obj->mag);
						}
						$yearMagGraph = $target->yearMagnitudeGraph($geo_coords, $date);
					} catch (\Throwable $_) {
						$yearMagGraph = null;
					}
				}
			} catch (\Throwable $_) {
				$yearMagGraph = null;
			}

			$yearDiamGraph = null;
			try {
				// Prefer concrete planet implementation when available
				if (isset($planet) && $planet) {
					try {
						$yearDiamGraph = $planet->yearDiameterGraph($geo_coords, $date);
					} catch (\Throwable $_) {
						$yearDiamGraph = null;
					}
				}
				if (empty($yearDiamGraph)) {
					try {
						// Ensure generic target has diameter values
						if (isset($computedDiam1) && $computedDiam1 !== null) {
							$target->setDiameter($computedDiam1, $computedDiam2 ?? $computedDiam1);
						} elseif (isset($obj) && isset($obj->diam1)) {
							$target->setDiameter($obj->diam1, $obj->diam2 ?? $obj->diam1);
						}
						$yearDiamGraph = $target->yearDiameterGraph($geo_coords, $date);
					} catch (\Throwable $_) {
						$yearDiamGraph = null;
					}
				}
			} catch (\Throwable $_) {
				$yearDiamGraph = null;
			}

			// Attempt to derive constellation from computed equatorial coordinates
			$consName = null;
			$consCode = null;
			try {
				if (isset($coords) && $coords) {
					if (method_exists($coords, 'getConstellation')) {
						try {
							$c = $coords->getConstellation();
							if (is_string($c) && ! empty($c)) {
								$consName = $c;
							} elseif (is_object($c)) {
								if (isset($c->name)) $consName = $c->name;
								if (isset($c->id)) $consCode = $c->id;
							}
						} catch (\Throwable $_) {
						}
					}
					if (! $consName && method_exists($coords, 'constellation')) {
						try {
							$c = $coords->constellation();
							if (is_string($c) && ! empty($c)) $consName = $c;
						} catch (\Throwable $_) {
						}
					}
				}

				if (! $consName && isset($planet) && $planet) {
					if (method_exists($planet, 'getConstellation')) {
						try {
							$c = $planet->getConstellation();
							if (is_string($c) && ! empty($c)) $consName = $c;
						} catch (\Throwable $_) {
						}
					}
					if (! $consName && method_exists($planet, 'constellation')) {
						try {
							$c = $planet->constellation();
							if (is_string($c) && ! empty($c)) $consName = $c;
						} catch (\Throwable $_) {
						}
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
			} catch (\Throwable $_) {
				// ignore constellation resolution errors
			}

			// Include computed appearance/coordinates in the ephemerides payload so
			// the view can render them or dispatch to the browser immediately when
			// the Livewire component is re-rendered. This ensures anonymous pages
			// or clients that miss the server browser event still receive fresh
			// RA/Dec, magnitude and diameter values embedded in the response.
			// logging of computed constellation intentionally removed to avoid log noise
			$this->ephemerides = [
				'date' => $date->timezone($tz)->toDateString(),
				'rising' => $rising,
				'transit' => $transit,
				'setting' => $setting,
				'best_time' => $bestTime,
				'max_height_at_night' => $maxHeightAtNight,
				'max_height' => $maxHeight,
				'altitude_graph' => $altitudeGraph,
				'year_graph' => $yearGraph,
				'year_magnitude_graph' => $yearMagGraph,
				'year_diameter_graph' => $yearDiamGraph,
				// Added appearance/coordinate fields
				'raDeg' => $raDeg,
				'decDeg' => $decDeg,
				'constellation' => $consName ?? null,
				'constellation_code' => $consCode ?? null,
				// inner-planet special events
				'inferior_conjunction' => $inferiorConjunction ?? null,
				'superior_conjunction' => $superiorConjunction ?? null,
				'greatest_western_elongation' => $greatestWesternElongation ?? null,
				'greatest_eastern_elongation' => $greatestEasternElongation ?? null,
				'illuminated_fraction' => $illuminatedFraction ?? null,
				// opposition/conjunction for outer planets (if available)
				'opposition' => $opposition ?? null,
				'conjunction' => $conjunction ?? null,
				// orbital extrema
				'perihelion' => $perihelionDate ?? null,
				'aphelion' => $aphelionDate ?? null,
				'diam1' => $computedDiam1 ?? ($obj->diam1 ?? null),
				'diam2' => $computedDiam2 ?? ($obj->diam2 ?? null),
				// illuminated_fraction already set from the planet target above
				'illuminated_fraction' => $illuminatedFraction ?? null,
				// expose object identifiers used to resolve this ephemeris
				'objectId' => $obj->id ?? ($useObjectId ?? null),
				'objectSlug' => $obj->slug ?? ($obj->name ?? null),
			];

			// Notify the aladin preview component with computed coordinates and
			// appearance (diameter/magnitude) so it can update contrast / optimum
			// detection calculations immediately when the date changes.
			try {
				// Ensure the payload always contains a usable identifier for tracing
				// and for the client to update immediately. If the DB-resolved object
				// does not have an `id` (fallback planet object), fall back to the
				// original identifier we attempted to use (`$useObjectId`). Also
				// include `objectSlug` to help tracing when numeric IDs are not
				// available.
				$emitPayload = [
					'objectId' => $obj->id ?? ($useObjectId ?? null),
					'objectSlug' => $obj->slug ?? ($obj->name ?? null),
					'objectName' => $obj->name ?? null,
					'date' => $date->toIso8601String(),
					'raDeg' => $raDeg,
					'decDeg' => $decDeg,
					'inferior_conjunction' => $this->ephemerides['inferior_conjunction'] ?? null,
					'superior_conjunction' => $this->ephemerides['superior_conjunction'] ?? null,
					'greatest_western_elongation' => $this->ephemerides['greatest_western_elongation'] ?? null,
					'greatest_eastern_elongation' => $this->ephemerides['greatest_eastern_elongation'] ?? null,
					// opposition/conjunction (outer planets) and orbital extrema
					'opposition' => $this->ephemerides['opposition'] ?? null,
					'conjunction' => $this->ephemerides['conjunction'] ?? null,
					'perihelion' => $this->ephemerides['perihelion'] ?? null,
					'aphelion' => $this->ephemerides['aphelion'] ?? null,
					'diam1' => $computedDiam1 ?? ($obj->diam1 ?? null),
					'diam2' => $computedDiam2 ?? ($obj->diam2 ?? null),
					'mag' => $computedMag ?? ($obj->mag ?? null),
					'illuminated_fraction' => $this->ephemerides['illuminated_fraction'] ?? null,
					'ephemerides' => $this->ephemerides,
					'constellation' => $this->ephemerides['constellation'] ?? null,
					'constellation_code' => $this->ephemerides['constellation_code'] ?? null,
					'_resolved_using' => $useObjectId ?? null,
					'_ts' => Carbon::now()->toIso8601String(),
				];
				// Emit to the preview component so it can update its state/server-side
				// listeners. Use emitTo to target the specific component instance by name.
				// (Removed verbose emit logging per request)
				// Emit a global Livewire event so any mounted preview component
				// listening for `objectEphemeridesUpdated` receives the payload
				// during the same Livewire response cycle. Using a global emit
				// is more reliable than emitTo when component instance names
				// or mount timing vary across pages.
				$this->emit('objectEphemeridesUpdated', $emitPayload);
				// Also emit to the `object-constellation` Livewire component (if mounted)
				try {
					$this->emitTo('object-constellation', 'setConstellation', $emitPayload['constellation'] ?? null);
					// Also broadcast a generic event for other listeners if needed
					$this->emit('objectConstellationUpdated', ['objectId' => $emitPayload['objectId'] ?? null, 'constellation' => $emitPayload['constellation'] ?? null]);
				} catch (\Throwable $_) {
					// non-fatal if emitTo fails (component not mounted)
				}
				// Also dispatch a browser event so non-authenticated pages (no Livewire preview mounted)
				// still receive the computed ephemerides and update client-side UI listeners.
				try {
					try {
						Log::info('ObjectEphemerides: dispatching browser event aladin-preview-info-updated', ['payload' => is_array($emitPayload) ? $emitPayload : json_decode(json_encode($emitPayload))]);
					} catch (\Throwable $_) {
						// ignore logging errors
					}
					$this->dispatchBrowserEvent('aladin-preview-info-updated', $emitPayload);
					// Also dispatch a specialized event to force-update the small Livewire
					// `object-constellation` component instance on the client. The client
					// listener will locate the mounted Livewire component and set its
					// `constellation` public property immediately.
					try {
						$this->dispatchBrowserEvent('dsl-force-constellation', [
							'objectId' => $emitPayload['objectId'] ?? null,
							'constellation' => $emitPayload['constellation'] ?? null,
						]);
					} catch (\Throwable $_) {
						// non-fatal
					}
				} catch (\Throwable $_) {
					// non-fatal if browser event dispatch fails in the server environment
				}
			} catch (\Throwable $_) {
				// non-fatal if emit fails
			}
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
