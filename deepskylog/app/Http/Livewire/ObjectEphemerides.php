<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Target;
use App\Models\CometObject;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Time;
use deepskylog\AstronomyLibrary\Targets\Parabolic;
use deepskylog\AstronomyLibrary\Targets\Elliptic;
use deepskylog\AstronomyLibrary\Targets\NearParabolic;
use Carbon\Carbon;
use App\Models\Constellation as ConstellationModel;

class ObjectEphemerides extends Component
{
	public $objectId;
	public $objectName;
	public $sourceTypeRaw;

	// Suppression flags set by the parent view when a type-specific
	// component (e.g. MoonDetails) will render those rows instead.
	public $suppressTopRaDec = false;
	public $suppressEphemerides = false;
	public $ephemerides = null;

	// Listen for the global date change dispatched by EphemerisAside
	protected $listeners = [
		'ephemerisDateChanged' => 'handleEphemerisDateChange',
		// Full payload updates allow authoritative server-side ephemerides updates
		'ephemerisPayloadUpdated' => 'handleEphemerisPayload',
	];

	public function mount($objectId = null, $initial = null, $objectName = null, $sourceTypeRaw = null, $suppressTopRaDec = false, $suppressEphemerides = false)
	{
		$this->objectId = (is_string($objectId) && trim($objectId) === '') ? null : $objectId;
		$this->objectName = $objectName ?? null;
		$this->sourceTypeRaw = $sourceTypeRaw ?? null;

		// Accept suppression flags from the parent so the component can avoid
		// rendering duplicated ephemerides/ra-dec rows when a type-specific
		// Livewire component (like MoonDetails) is mounted on the page.
		$this->suppressTopRaDec = $suppressTopRaDec ?? false;
		$this->suppressEphemerides = $suppressEphemerides ?? false;

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
					// Pass the supplied initial ephemerides through so recalculate()
					// can use authoritative wrapper coordinates when computing
					// derived values (rise/transit/setting, etc.).
					$this->recalculate([
						'objectId' => $this->objectId,
						'objectName' => $this->objectName,
						'sourceTypeRaw' => $this->sourceTypeRaw,
						'ephemerides' => $initial,
					]);
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
		try {
			Log::debug('ObjectEphemerides: handleEphemerisDateChange', ['date' => $date, 'objectId' => $this->objectId]);
		} catch (\Throwable $_) {
		}
		$this->recalculate(['date' => $date, 'objectId' => $this->objectId, 'objectName' => $this->objectName, 'sourceTypeRaw' => $this->sourceTypeRaw]);
	}

	public function handleEphemerisPayload($payload = null)
	{
		try {
			try {
				$raw = is_array($payload) ? $payload : (is_object($payload) ? (array)$payload : $payload);
				$hasIllum = false;
				$illumVal = null;
				try {
					if (is_array($raw) && array_key_exists('illuminated_fraction', $raw)) {
						$hasIllum = true;
						$illumVal = $raw['illuminated_fraction'];
					} elseif (is_array($raw) && isset($raw['payload']) && is_array($raw['payload']) && array_key_exists('illuminated_fraction', $raw['payload'])) {
						$hasIllum = true;
						$illumVal = $raw['payload']['illuminated_fraction'];
					} elseif (is_array($raw) && isset($raw['ephemerides']) && is_array($raw['ephemerides']) && array_key_exists('illuminated_fraction', $raw['ephemerides'])) {
						$hasIllum = true;
						$illumVal = $raw['ephemerides']['illuminated_fraction'];
					}
				} catch (\Throwable $_) {
				}
				Log::debug('ObjectEphemerides: handleEphemerisPayload incoming', ['objectId' => $this->objectId, 'payload_has_illuminated_fraction' => $hasIllum, 'illuminated_fraction' => $illumVal, 'raw_payload' => $raw]);
			} catch (\Throwable $_) {
			}
			// Existing sanitization: remove moon illum unless targeting Moon
			$payloadArr = is_array($payload) ? $payload : (is_object($payload) ? (array)$payload : []);
			$keepIllum = false;
			$checkVals = [
				$payloadArr['objectId'] ?? null,
				$payloadArr['objectSlug'] ?? null,
				$payloadArr['sourceTypeRaw'] ?? null,
				$this->objectId ?? null,
				$this->sourceTypeRaw ?? null,
			];
			foreach ($checkVals as $v) {
				if (!empty($v) && is_string($v) && mb_strtolower(trim((string)$v)) === 'moon') {
					$keepIllum = true;
					break;
				}
			}
			if (! $keepIllum) {
				if (array_key_exists('illuminated_fraction', $payloadArr)) unset($payloadArr['illuminated_fraction']);
				if (isset($payloadArr['payload']) && is_array($payloadArr['payload']) && array_key_exists('illuminated_fraction', $payloadArr['payload'])) unset($payloadArr['payload']['illuminated_fraction']);
				if (isset($payloadArr['ephemerides']) && is_array($payloadArr['ephemerides']) && array_key_exists('illuminated_fraction', $payloadArr['ephemerides'])) unset($payloadArr['ephemerides']['illuminated_fraction']);
			}
			$this->recalculate($payloadArr);
		} catch (\Throwable $_) {
			// swallow errors
		}
	}

	/**
	 * Recalculate per-object ephemerides.
	 * Payload may contain: objectId (id or slug) and date (ISO/Y-m-d)
	 */
	public function recalculate($payload = null)
	{
		$this->ephemerides = null;
		// (debug logs removed)

		try {
			$payloadArr = is_array($payload) ? $payload : (is_object($payload) ? (array)$payload : []);

			// Accept pre-supplied ephemerides (for example authoritative wrapper coords)
			// and prefer them when computing derived ephemerides. These may be passed
			// either as top-level `raDeg`/`decDeg` keys or in an `ephemerides` array.
			$preSuppliedRaDeg = null;
			$preSuppliedDecDeg = null;
			$preSuppliedDate = null;
			if (!empty($payloadArr['ephemerides']) && is_array($payloadArr['ephemerides'])) {
				$pe = $payloadArr['ephemerides'];
				if (isset($pe['raDeg'])) $preSuppliedRaDeg = $pe['raDeg'];
				if (isset($pe['decDeg'])) $preSuppliedDecDeg = $pe['decDeg'];
				if (isset($pe['date'])) $preSuppliedDate = $pe['date'];
			}
			if (isset($payloadArr['raDeg'])) $preSuppliedRaDeg = $payloadArr['raDeg'];
			if (isset($payloadArr['decDeg'])) $preSuppliedDecDeg = $payloadArr['decDeg'];
			if (isset($payloadArr['ephemerides']['date'])) $preSuppliedDate = $payloadArr['ephemerides']['date'];

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

			if (empty($useObjectId)) {
				return;
			}

			// Resolve object record (prefer Eloquent model lookup which respects
			// configured primaryKey and DB connection). Fall back to slug/name
			// and lastly allow proceeding when authoritative wrapper coords are
			// supplied in the payload (preSuppliedRaDeg/preSuppliedDecDeg).
			$obj = null;
			try {
				// (debug removed)
				if (is_numeric((string)$useObjectId)) {
					// Prefer Eloquent model find which respects model settings
					try {
						$foundModel = Target::find((int)$useObjectId);
						if ($foundModel) {
							$obj = $foundModel;
						}
					} catch (\Throwable $_) {
						$obj = null;
					}
				}

				// If not found by id (or identifier was non-numeric) try slug/name
				if (empty($obj)) {
					$searchVal = trim((string)$useObjectId);
					// (debug removed)
					try {
						$foundModel = Target::where('slug', $searchVal)->orWhere('name', $searchVal)->first();
						if ($foundModel) {
							$obj = $foundModel;
							// (debug removed)
						}
					} catch (\Throwable $_) {
						$obj = null;
					}
				}

				// If still not found, this may be a comet stored in the legacy cometobjects table.
				if (empty($obj)) {
					// (debug removed)
					try {
						// Respect an explicit sourceTypeRaw hint: if the caller
						// indicated this is a planet, do NOT consider legacy
						// `cometobjects` matches which may share numeric ids.
						$explicitSourceType = $payloadArr['sourceTypeRaw'] ?? $this->sourceTypeRaw ?? null;
						$explicitIsPlanet = is_string($explicitSourceType) && mb_strtolower($explicitSourceType) === 'planet';

						if (is_numeric((string)$useObjectId)) {
							$co = CometObject::find((int)$useObjectId);
							if ($co) {
								if ($explicitIsPlanet) {
									// (debug removed)
								} else {
									$obj = $co;
								}
							}
						}
						if (empty($obj)) {
							$searchVal = trim((string)$useObjectId);
							$co = CometObject::where('slug', $searchVal)->orWhere('name', $searchVal)->first();
							if ($co) {
								if ($explicitIsPlanet) {
									// (debug removed)
								} else {
									$obj = $co;
								}
							}
						}
					} catch (\Throwable $_) {
						// ignore comet lookup failures
					}
				}

				// If still not found, allow proceeding only if authoritative
				// coordinates were supplied in the payload (wrapper-provided).
				if (empty($obj)) {
					$planetName = $payloadArr['objectName'] ?? $this->objectName ?? null;
					$sourceType = $payloadArr['sourceTypeRaw'] ?? $this->sourceTypeRaw ?? null;
					if (!empty($planetName) && is_string($sourceType) && mb_strtolower($sourceType) === 'planet') {
						$obj = (object)['id' => null, 'name' => $planetName];
					} else {
						if (!is_null($preSuppliedRaDeg) && !is_null($preSuppliedDecDeg)) {
							$obj = (object)[
								'id' => null,
								'name' => $payloadArr['objectName'] ?? $this->objectName ?? null,
								'ra' => null,
								'decl' => null,
							];
						} else {
							// Try a lightweight designation-only probe as a last resort
							$designationProbe = $payloadArr['objectName'] ?? $this->objectName ?? null;
							if (!empty($designationProbe) && method_exists(\App\Helpers\HorizonsProxy::class, 'calculateEquatorialCoordinates')) {
								try {
									$probeRes = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates(new \deepskylog\AstronomyLibrary\Targets\Target(), Carbon::now('UTC'), new GeographicalCoordinates(0, 0), 0.0, ['designation' => $designationProbe]);
								} catch (\Throwable $_) {
									$probeRes = null;
								}
								if (is_array($probeRes) && !empty($probeRes['coords'])) {
									$coords = $probeRes['coords'];
									$obj = (object)['id' => null, 'name' => $designationProbe, 'designation' => $designationProbe];
								}
							}
							if (empty($obj)) {
								return;
							}
						}
					}
				}
			} catch (\Throwable $_) {
				return;
			}

			// (debug removed)

			$authUser = Auth::user();
			$userLocation = $authUser?->standardLocation ?? null;
			// (debug removed)
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
			// (debug removed)
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
			$recomputeForced = false;
			try {
				if (method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
					$raDeg = \App\Models\DeepskyObject::raToDecimal($obj->ra ?? $obj->RA ?? null);
					$decDeg = \App\Models\DeepskyObject::decToDecimal($obj->decl ?? $obj->DEC ?? null);
				}
			} catch (\Throwable $_) {
				$raDeg = null;
				$decDeg = null;
			}

			// If the caller supplied authoritative coordinates (eg. from a Horizons wrapper
			// diagnostic), prefer those over DB-derived values so derived ephemerides are
			// computed using the wrapper RA/Dec.
			try {
				if (!is_null($preSuppliedRaDeg) && is_numeric($preSuppliedRaDeg)) {
					$raDeg = (float)$preSuppliedRaDeg;
				}
				if (!is_null($preSuppliedDecDeg) && is_numeric($preSuppliedDecDeg)) {
					$decDeg = (float)$preSuppliedDecDeg;
				}
			} catch (\Throwable $_) {
				// ignore override errors
			}

			// Log key state after initial RA/Dec resolution so we can debug why
			// comet recomputation or proxy calls may not be happening when the
			// aside date changes.
			// (debug removed)

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
									try {
										if (method_exists(\App\Helpers\HorizonsProxy::class, 'calculateEquatorialCoordinates')) {
											$proxyResult = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates($planet, $date, $geo, $height, ['obj' => $obj ?? null]);
										} else {
											$proxyResult = null;
										}
										if (is_array($proxyResult) && !empty($proxyResult['coords'])) {
											$computedCoords = $proxyResult['coords'];
											try {
												if (method_exists($planet, 'setEquatorialCoordinates')) {
													$planet->setEquatorialCoordinates($computedCoords);
												}
											} catch (\Throwable $_) {
											}
										} else {
											// If proxy did not return coords, fall back to library calculations
											if (method_exists($planet, 'calculateEquatorialCoordinates')) {
												$planet->calculateEquatorialCoordinates($date, $geo, $height);
											} elseif (method_exists($planet, 'calculateApparentEquatorialCoordinates')) {
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

			// If this object looks like a comet and the caller provided a specific
			// date, prefer recomputing coordinates for that date rather than using
			// stored DB RA/Dec values which are epoch-dependent. Do not override
			// authoritative pre-supplied coordinates from a wrapper (preSuppliedRaDeg).
			try {
				$sourceTypeLower = is_string($sourceType) ? mb_strtolower($sourceType) : null;
				$isComet = false;
				if ($sourceTypeLower === 'comet') {
					$isComet = true;
				} else {
					$nm = $obj->name ?? ($obj->designation ?? null);
					if (is_string($nm) && preg_match('/\b(\d{1,4}P|C\/\d{4}[A-Z0-9-]*)\b/i', $nm)) {
						$isComet = true;
					}
				}
				if ($isComet && !empty($payloadArr['date']) && ($preSuppliedDate === null || (string)$preSuppliedDate !== (string)$payloadArr['date'])) {
					// If a different date was requested than the pre-supplied wrapper
					// coordinates, force recomputation so orbital-element/proxy
					// calculations run for the requested date.
					$oldDate = $preSuppliedDate ?? null;
					$newDate = $payloadArr['date'];
					$raDeg = null;
					$decDeg = null;
					$recomputeForced = true;
				}
			} catch (\Throwable $_) {
				// ignore
			}

			// planet events logging removed

			if ($raDeg === null || $decDeg === null) {
				// Try computing coordinates for comet-like objects using orbital elements
				try {
					$eVal = isset($obj->e) ? (float)$obj->e : null;
					$qVal = isset($obj->q) ? (float)$obj->q : null;
					$aVal = isset($obj->a) ? (float)$obj->a : null;
					// comet orbital elements logging removed
					// Build a robust designation to pass to HorizonsProxy: prefer explicit designation,
					// then slug, then name, then any payload-provided objectName.
					$hDesig = null;
					try {
						$hDesig = $obj->designation ?? $obj->slug ?? $obj->name ?? ($payloadArr['objectName'] ?? $this->objectName ?? null);
					} catch (\Throwable $_) {
						$hDesig = $payloadArr['objectName'] ?? $this->objectName ?? null;
					}
					$coords = null;
					if (($eVal !== null && $qVal !== null) || ($aVal !== null && $eVal !== null)) {
						$peri = null;
						if (isset($obj->epoch) && is_numeric($obj->epoch)) {
							try {
								$peri = Time::fromJd($obj->epoch);
							} catch (\Throwable $_) {
								$peri = null;
							}
						}
						if (! $peri) $peri = Carbon::now('UTC');
						if ($eVal === 1.0) {
							$par = new Parabolic();
							$par->setOrbitalElements((float)$qVal, (float)($obj->i ?? 0.0), (float)($obj->w ?? 0.0), (float)($obj->node ?? 0.0), $peri);
							// If photometry parameters exist in comets_orbital_elements, apply them
							try {
								$phot = DB::table('comets_orbital_elements')->where('name', $obj->name ?? ($obj->designation ?? null))->first();
								if ($phot && isset($phot->H)) {
									$h = is_numeric($phot->H) ? floatval($phot->H) : null;
									$nval = isset($phot->n) && is_numeric($phot->n) ? floatval($phot->n) : 4.0;
									$phase = isset($phot->phase_coeff) && is_numeric($phot->phase_coeff) ? floatval($phot->phase_coeff) : null;
									$npre = isset($phot->n_pre) && is_numeric($phot->n_pre) ? floatval($phot->n_pre) : null;
									$npost = isset($phot->n_post) && is_numeric($phot->n_post) ? floatval($phot->n_post) : null;
									if ($h !== null) {
										$par->setCometParams($h, $nval, $phase, $npre, $npost);
									}
								}
							} catch (\Throwable $_) {
								// non-fatal if photometry probe fails
							}
							$proxyRes = null;
							try {
								$proxyRes = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates($par, $date, $geo_coords, $userLocation->elevation ?? 0.0, ['designation' => $hDesig ?? null, 'obj' => $obj, 'ignore_wrapper' => ($recomputeForced ?? false)]);
							} catch (\Throwable $_) {
								$proxyRes = null;
							}
							if (!empty($proxyRes['coords'])) {
								$coords = $proxyRes['coords'];
								if ($coords && method_exists($par, 'setEquatorialCoordinates')) {
									try {
										$par->setEquatorialCoordinates($coords);
									} catch (\Throwable $_) {
									}
								}
							} else {
								try {
									$par->calculateEquatorialCoordinates($date, $geo_coords);
								} catch (\Throwable $_) {
									try {
										$par->calculateEquatorialCoordinates($date);
									} catch (\Throwable $_) {
									}
								}
								if (method_exists($par, 'getEquatorialCoordinatesToday')) $coords = $par->getEquatorialCoordinatesToday();
								elseif (method_exists($par, 'getEquatorialCoordinates')) $coords = $par->getEquatorialCoordinates();
							}
						} elseif ($eVal !== null && $eVal < 1.0 && $aVal !== null) {
							$ell = new Elliptic();
							$ell->setOrbitalElements((float)$aVal, $eVal, (float)($obj->i ?? 0.0), (float)($obj->w ?? 0.0), (float)($obj->node ?? 0.0), $peri);
							// For elliptic (periodic) comets/asteroids, prefer H/G when available
							try {
								$phot = DB::table('comets_orbital_elements')->where('name', $obj->name ?? ($obj->designation ?? null))->first();
								if ($phot && isset($phot->H)) {
									$Hval = is_numeric($phot->H) ? floatval($phot->H) : null;
									if ($Hval !== null) {
										$Gval = 0.15; // default slope
										$ell->setHG($Hval, $Gval);
									}
								}
							} catch (\Throwable $_) {
								// ignore
							}
							$proxyRes = null;
							try {
								$proxyRes = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates($ell, $date, $geo_coords, $userLocation->elevation ?? 0.0, ['designation' => $hDesig ?? null, 'obj' => $obj, 'ignore_wrapper' => ($recomputeForced ?? false)]);
							} catch (\Throwable $_) {
								$proxyRes = null;
							}
							if (!empty($proxyRes['coords'])) {
								$coords = $proxyRes['coords'];
								if ($coords && method_exists($ell, 'setEquatorialCoordinates')) {
									try {
										$ell->setEquatorialCoordinates($coords);
									} catch (\Throwable $_) {
									}
								}
							} else {
								try {
									$ell->calculateEquatorialCoordinates($date, $geo_coords, $userLocation->elevation ?? 0.0);
								} catch (\Throwable $_) {
								}
								if (method_exists($ell, 'getEquatorialCoordinatesToday')) $coords = $ell->getEquatorialCoordinatesToday();
								elseif (method_exists($ell, 'getEquatorialCoordinates')) $coords = $ell->getEquatorialCoordinates();
							}
						} else {
							$near = new NearParabolic();
							$near->setOrbitalElements((float)$qVal, $eVal ?? 1.0, (float)($obj->i ?? 0.0), (float)($obj->w ?? 0.0), (float)($obj->node ?? 0.0), $peri);
							// Apply comet photometry params when present
							try {
								$phot = DB::table('comets_orbital_elements')->where('name', $obj->name ?? ($obj->designation ?? null))->first();
								if ($phot && isset($phot->H)) {
									$h = is_numeric($phot->H) ? floatval($phot->H) : null;
									$nval = isset($phot->n) && is_numeric($phot->n) ? floatval($phot->n) : 4.0;
									$phase = isset($phot->phase_coeff) && is_numeric($phot->phase_coeff) ? floatval($phot->phase_coeff) : null;
									$npre = isset($phot->n_pre) && is_numeric($phot->n_pre) ? floatval($phot->n_pre) : null;
									$npost = isset($phot->n_post) && is_numeric($phot->n_post) ? floatval($phot->n_post) : null;
									if ($h !== null) {
										$near->setCometParams($h, $nval, $phase, $npre, $npost);
									}
								}
							} catch (\Throwable $_) {
								// non-fatal
							}
							$proxyRes = null;
							try {
								$proxyRes = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates($near, $date, $geo_coords, $userLocation->elevation ?? 0.0, ['designation' => $hDesig ?? null, 'obj' => $obj, 'ignore_wrapper' => ($recomputeForced ?? false)]);
							} catch (\Throwable $_) {
								$proxyRes = null;
							}
							if (!empty($proxyRes['coords'])) {
								$coords = $proxyRes['coords'];
								if ($coords && method_exists($near, 'setEquatorialCoordinates')) {
									try {
										$near->setEquatorialCoordinates($coords);
									} catch (\Throwable $_) {
									}
								}
							} else {
								try {
									$near->calculateEquatorialCoordinates($date);
								} catch (\Throwable $_) {
								}
								if (method_exists($near, 'getEquatorialCoordinatesToday')) $coords = $near->getEquatorialCoordinatesToday();
								elseif (method_exists($near, 'getEquatorialCoordinates')) $coords = $near->getEquatorialCoordinates();
							}
						}
						if ($coords) {
							try {
								if (method_exists($coords, 'getRA')) $raObj = $coords->getRA();
								else $raObj = $coords->ra ?? null;
								$raVal = (is_object($raObj) && method_exists($raObj, 'getCoordinate')) ? $raObj->getCoordinate() : $raObj;
								if (is_numeric($raVal)) $raDeg = ((float)$raVal <= 24.0) ? (float)$raVal * 15.0 : (float)$raVal;
							} catch (\Throwable $_) {
							}
							try {
								if (method_exists($coords, 'getDeclination')) $decObj = $coords->getDeclination();
								else $decObj = $coords->dec ?? null;
								$decVal = (is_object($decObj) && method_exists($decObj, 'getCoordinate')) ? $decObj->getCoordinate() : $decObj;
								if (is_numeric($decVal)) $decDeg = (float)$decVal;
							} catch (\Throwable $_) {
							}
						}
					}
				} catch (\Throwable $_) {
					// ignore comet calc failures
				}
				// If orbital-element based computation didn't give coords, attempt a
				// designation-only probe via HorizonsProxy. This covers cases where
				// the DB lacks orbital elements but a wrapper or vendor can resolve
				// the current position by designation/name.
				if ($raDeg === null || $decDeg === null) {
					try {
						$designationProbe = $obj->designation ?? $obj->name ?? null;
						if (!empty($designationProbe) && method_exists(\App\Helpers\HorizonsProxy::class, 'calculateEquatorialCoordinates')) {
							try {
								$probeRes = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates(new \deepskylog\AstronomyLibrary\Targets\Target(), $date, $geo_coords, $userLocation->elevation ?? 0.0, ['designation' => $designationProbe, 'obj' => $obj, 'ignore_wrapper' => ($recomputeForced ?? false)]);
							} catch (\Throwable $_) {
								$probeRes = null;
							}
							if (is_array($probeRes) && !empty($probeRes['coords'])) {
								$coords = $probeRes['coords'];
								try {
									if (method_exists($coords, 'getRA')) $raObj = $coords->getRA();
									else $raObj = $coords->ra ?? null;
									$raVal = (is_object($raObj) && method_exists($raObj, 'getCoordinate')) ? $raObj->getCoordinate() : $raObj;
									if (is_numeric($raVal)) $raDeg = ((float)$raVal <= 24.0) ? (float)$raVal * 15.0 : (float)$raVal;
								} catch (\Throwable $_) {
								}
								try {
									if (method_exists($coords, 'getDeclination')) $decObj = $coords->getDeclination();
									else $decObj = $coords->dec ?? null;
									$decVal = (is_object($decObj) && method_exists($decObj, 'getCoordinate')) ? $decObj->getCoordinate() : $decObj;
									if (is_numeric($decVal)) $decDeg = (float)$decVal;
								} catch (\Throwable $_) {
								}
							}
						}
					} catch (\Throwable $_) {
						// ignore probe errors
					}
					if ($raDeg === null || $decDeg === null) {
						$this->ephemerides = null;
						return;
					}
				}
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

			// If we have numeric RA/Dec but no coords object (common for some
			// comet computations that returned raw degrees), create a temporary
			// EquatorialCoordinates instance so downstream constellation helpers
			// (getConstellation / constellation) can operate consistently.
			try {
				if ((empty($coords) || $coords === null) && is_numeric($raDeg) && is_numeric($decDeg)) {
					$raHoursTmp = (float)$raDeg / 15.0;
					$coords = new EquatorialCoordinates($raHoursTmp, (float)$decDeg);
				}
			} catch (\Throwable $_) {
				// ignore failures — constellation will remain unset
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
			// preparing ephemerides logging removed
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

			// Ensure the small `object-constellation` preview receives the computed
			// constellation immediately (defensive: do not overwrite non-empty values
			// on the client because `ObjectConstellation` ignores null clears).
			try {
				if (!empty($consName)) {
					$this->emitTo('object-constellation', 'setConstellation', $consName);
				}
			} catch (\Throwable $_) {
				// ignore emit failures
			}

			// Defensive: do not expose RA/Dec for the Moon — users requested hiding
			// Moon coordinates and they are not useful in the main object header.
			try {
				$sourceLowerCheck = is_string($payloadArr['sourceTypeRaw'] ?? '') ? mb_strtolower($payloadArr['sourceTypeRaw']) : null;
				$slugCheck = isset($obj) && (isset($obj->slug) || isset($obj->name)) ? (string) ($obj->slug ?? $obj->name) : null;
				$isMoonObj = false;
				if ($sourceLowerCheck === 'moon') $isMoonObj = true;
				if (!$isMoonObj && $slugCheck) {
					$key = preg_replace('/[^a-z]/', '', mb_strtolower($slugCheck));
					if ($key === 'moon') $isMoonObj = true;
				}
				if ($isMoonObj) {
					try {
						$this->ephemerides['raDeg'] = null;
					} catch (\Throwable $_) {
					}
					try {
						$this->ephemerides['decDeg'] = null;
					} catch (\Throwable $_) {
					}
					// Also clear local computed variables so they are not emitted to clients
					try {
						$raDeg = null;
					} catch (\Throwable $_) {
					}
					try {
						$decDeg = null;
					} catch (\Throwable $_) {
					}
				}
			} catch (\Throwable $_) {
			}

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
					'illumination' => $this->ephemerides['illuminated_fraction'] ?? null,
					'ephemerides' => $this->ephemerides,
					'constellation' => $this->ephemerides['constellation'] ?? null,
					'constellation_code' => $this->ephemerides['constellation_code'] ?? null,
					'_resolved_using' => $useObjectId ?? null,
					'_ts' => Carbon::now()->toIso8601String(),
				];
				// emitting ephemerides logging removed
				// Emit to the preview component so it can update its state/server-side
				// listeners. Use emitTo to target the specific component instance by name.
				// (Removed verbose emit logging per request)
				// Emit a global Livewire event so any mounted preview component
				// listening for `objectEphemeridesUpdated` receives the payload
				// during the same Livewire response cycle. Using a global emit
				// is more reliable than emitTo when component instance names
				// or mount timing vary across pages.
				// Prepare a sanitized payload for preview consumers: build an explicit
				// ephemerides array and strip heavy graph HTML so small preview
				// components never receive inline image HTML blobs.
				$sanitizedEphemerides = [];
				if (isset($emitPayload['ephemerides'])) {
					if (is_array($emitPayload['ephemerides'])) {
						$sanitizedEphemerides = $emitPayload['ephemerides'];
					} elseif (is_object($emitPayload['ephemerides'])) {
						$sanitizedEphemerides = (array)$emitPayload['ephemerides'];
					} else {
						// Fallback: attempt json decode if a string was provided
						try {
							$decoded = @json_decode((string)$emitPayload['ephemerides'], true);
							if (is_array($decoded)) $sanitizedEphemerides = $decoded;
						} catch (\Throwable $_) {
							$sanitizedEphemerides = [];
						}
					}
				}
				// Remove graph HTML keys unconditionally
				foreach (['altitude_graph', 'year_graph', 'year_magnitude_graph', 'year_diameter_graph'] as $gk) {
					if (isset($sanitizedEphemerides[$gk])) unset($sanitizedEphemerides[$gk]);
				}
				$previewPayload = $emitPayload;
				$previewPayload['ephemerides'] = $sanitizedEphemerides;

				// If parent requested suppression of ephemerides (e.g. MoonDetails mounted)
				// avoid emitting or dispatching browser events that could overwrite the
				// authoritative per-type Livewire component. This prevents later
				// out-of-order updates on Moon pages.
				if (empty($this->suppressEphemerides)) {
					try {
						Log::debug('ObjectEphemerides: about to emit objectEphemeridesUpdated', ['objectId' => $previewPayload['objectId'] ?? null, 'ephemerides' => $previewPayload['ephemerides'] ?? null]);
					} catch (\Throwable $_) {
					}
					$this->emit('objectEphemeridesUpdated', $previewPayload);
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
						// Dispatch browser event with sanitized preview payload so the client-side
						// Aladin preview doesn't receive raw embedded graph HTML.
						// Convert any Moon illuminated_fraction into a moon_illumination key
						// so generic page handlers do not treat it as the object's
						// illuminated_fraction (prevents moon illumination from
						// overwriting other object pages).
						$browserPreview = $previewPayload;
						try {
							$targetName = '';
							if (!empty($browserPreview['objectSlug'])) $targetName = mb_strtolower(trim((string)$browserPreview['objectSlug']));
							elseif (!empty($browserPreview['objectName'])) $targetName = mb_strtolower(trim((string)$browserPreview['objectName']));
							// If the payload targets the Moon, move the illuminated_fraction
							// into a dedicated `moon_illumination` field and remove the
							// generic `illuminated_fraction` so broad handlers won't pick it up.
							if ($targetName === 'moon') {
								if (array_key_exists('illuminated_fraction', $browserPreview)) {
									$browserPreview['moon_illumination'] = $browserPreview['illuminated_fraction'];
									unset($browserPreview['illuminated_fraction']);
								}
								if (isset($browserPreview['ephemerides']) && is_array($browserPreview['ephemerides']) && array_key_exists('illuminated_fraction', $browserPreview['ephemerides'])) {
									$browserPreview['ephemerides']['moon_illumination'] = $browserPreview['ephemerides']['illuminated_fraction'];
									unset($browserPreview['ephemerides']['illuminated_fraction']);
								}
							}
						} catch (\Throwable $_) {
							// ignore sanitization errors and fall back to raw preview
							$browserPreview = $previewPayload;
						}

						$this->dispatchBrowserEvent('aladin-preview-info-updated', $browserPreview);
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
