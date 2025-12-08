<?php

namespace App\Helpers;

use App\Helpers\HorizonsWrapper;
use App\Helpers\HorizonsDesignation;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use Illuminate\Support\Facades\Log;

class HorizonsProxy
{
    /**
     * Centralized caller for calculateEquatorialCoordinates.
     * If wrapper diagnostics exist for the designation/date, return those
     * coordinates and skip calling the vendor helper. Otherwise call the
     * underlying target's calculateEquatorialCoordinates and return usedWrapper=false.
     *
     * @param object $target  Concrete astronomy target instance (Elliptic, Parabolic, etc.)
     * @param CarbonImmutable $date
     * @param mixed $geo
     * @param mixed $height
     * @param array $options  Optional keys: 'designation' => string, 'obj' => model
     * @return array ['usedWrapper'=>bool,'coords'=>EquatorialCoordinates|null,'source'=>string|null]
     */
    public static function calculateEquatorialCoordinates($target, Carbon $date, $geo = null, $height = null, array $options = []): array
    {
        try {
            $designation = $options['designation'] ?? null;
            $ignoreWrapper = $options['ignore_wrapper'] ?? $options['ignoreWrapper'] ?? false;
            if (empty($designation) && isset($options['obj'])) {
                try {
                    $designation = $options['obj']->slug ?? $options['obj']->name ?? null;
                } catch (\Throwable $_) {
                }
            }

            $cand = [];
            if (! empty($designation)) $cand[] = HorizonsDesignation::canonicalize($designation);
            // also include raw provided forms and model name/slug
            if (! empty($designation)) $cand[] = (string)$designation;
            if (isset($options['obj'])) {
                try {
                    $cand[] = $options['obj']->slug ?? null;
                } catch (\Throwable $_) {
                }
                try {
                    $cand[] = $options['obj']->name ?? null;
                } catch (\Throwable $_) {
                }
            }

            // Add extra normalized variants to increase match chance
            $extra = [];
            foreach ($cand as $c) {
                if (! $c) continue;
                $s = trim((string)$c);
                $extra[] = $s;
                $extra[] = strtoupper($s);
                $extra[] = strtolower($s);
                $extra[] = str_replace('/', ' ', $s);
                $extra[] = str_replace(' ', '', $s);
            }
            $candidates = array_values(array_unique(array_filter(array_merge($cand, $extra))));

            try {
                Log::debug('HorizonsProxy: calculateEquatorialCoordinates called', ['designation' => $designation ?? null, 'ignore_wrapper' => $ignoreWrapper, 'candidates' => $candidates]);
            } catch (\Throwable $_) {
            }

            if (! $ignoreWrapper) {
                // permissive window: 7 days age, 1 hour tolerance
                $wrapper = HorizonsWrapper::latestCoordinatesForDesignation($candidates, $date, 7 * 86400, 3600);
                if ($wrapper && isset($wrapper['ra_hours']) && isset($wrapper['dec_deg'])) {
                    try {
                        $coords = new EquatorialCoordinates($wrapper['ra_hours'], $wrapper['dec_deg']);
                        Log::info('HorizonsProxy: using HorizonsWrapper coords', ['file' => $wrapper['source_file'] ?? null, 'ra_hours' => $wrapper['ra_hours'], 'dec_deg' => $wrapper['dec_deg'], 'designation' => $designation ?? null]);
                    } catch (\Throwable $_) {
                        $coords = null;
                    }
                    return ['usedWrapper' => true, 'coords' => $coords, 'source' => $wrapper['source_file'] ?? null];
                }
            }
        } catch (\Throwable $e) {
            // Fall through to calling vendor helper
            Log::debug('HorizonsProxy: wrapper probe failed', ['err' => $e->getMessage()]);
        }

        // No wrapper match so far — do one final permissive probe to avoid
        // accidentally calling the vendor helper when wrapper diagnostics exist
        // but didn't match earlier variant lists. Use a longer age window and
        // broader tolerance and include additional normalization variants.
        try {
            if ($ignoreWrapper) {
                // Skip permissive probe and designation-only probe when caller requests
                // vendor computation (force fresh computation for the requested date).
                throw new \Exception('ignore_wrapper set');
            }
            $probeCandidates = $candidates ?? [];
            // add some raw fallbacks from options
            if (! empty($designation)) {
                $probeCandidates[] = $designation;
                $probeCandidates[] = str_replace('/', ' ', $designation);
                $probeCandidates[] = str_replace(' ', '', $designation);
                $probeCandidates[] = strtoupper($designation);
                $probeCandidates[] = strtolower($designation);
            }
            if (isset($options['obj'])) {
                try {
                    $probeCandidates[] = $options['obj']->slug ?? null;
                    $probeCandidates[] = $options['obj']->name ?? null;
                } catch (\Throwable $_) {
                }
            }
            $probeCandidates = array_values(array_unique(array_filter(array_map(fn($v) => $v === null ? null : trim((string)$v), $probeCandidates))));
            if (! empty($probeCandidates)) {
                $robust = HorizonsWrapper::latestCoordinatesForDesignation($probeCandidates, $date, 30 * 86400, 24 * 3600);
                if ($robust && isset($robust['ra_hours']) && isset($robust['dec_deg'])) {
                    try {
                        $coords = new EquatorialCoordinates($robust['ra_hours'], $robust['dec_deg']);
                        Log::warning('HorizonsProxy: found wrapper coords in permissive probe — using wrapper instead of vendor', ['file' => $robust['source_file'] ?? null, 'ra_hours' => $robust['ra_hours'], 'dec_deg' => $robust['dec_deg'], 'designation' => $designation ?? null]);
                    } catch (\Throwable $_) {
                        $coords = null;
                    }
                    if ($coords) {
                        return ['usedWrapper' => true, 'coords' => $coords, 'source' => $robust['source_file'] ?? null];
                    }
                }
            }
        } catch (\Throwable $_) {
            // ignore permissive probe failures and proceed to vendor helper
        }

        // Final attempt: ignore the requested date entirely and search recent
        // wrapper diagnostics by designation only. This handles cases where
        // the recorded wrapper run used a different timestamp but is still
        // the authoritative source for the object.
        try {
            if ($ignoreWrapper) {
                // Skip designation-only probe when ignoring wrapper data
                throw new \Exception('ignore_wrapper set');
            }
            $byDesig = $candidates ?? [];
            if (! empty($designation)) {
                $byDesig[] = $designation;
            }
            if (isset($options['obj'])) {
                try {
                    $byDesig[] = $options['obj']->slug ?? null;
                    $byDesig[] = $options['obj']->name ?? null;
                } catch (\Throwable $_) {
                }
            }
            $byDesig = array_values(array_unique(array_filter(array_map(fn($v) => $v === null ? null : trim((string)$v), $byDesig))));
            if (! empty($byDesig)) {
                $any = HorizonsWrapper::latestCoordinatesForDesignation($byDesig, null, 30 * 86400, 0);
                if ($any && isset($any['ra_hours']) && isset($any['dec_deg'])) {
                    try {
                        $coords = new EquatorialCoordinates($any['ra_hours'], $any['dec_deg']);
                        Log::warning('HorizonsProxy: using wrapper coords found by designation-only probe (ignoring date)', ['file' => $any['source_file'] ?? null, 'ra_hours' => $any['ra_hours'], 'dec_deg' => $any['dec_deg'], 'designation' => $designation ?? null]);
                    } catch (\Throwable $_) {
                        $coords = null;
                    }
                    if ($coords) {
                        return ['usedWrapper' => true, 'coords' => $coords, 'source' => $any['source_file'] ?? null];
                    }
                }
            }
        } catch (\Throwable $_) {
            // ignore and proceed to vendor helper
        }

        // No wrapper found — call underlying target implementation and attempt
        // to return any computed equatorial coordinates the target exposes.
        $vendorCoords = null;
        try {
            // Preserve original calling signature variations
            if ($geo !== null && $height !== null) {
                $target->calculateEquatorialCoordinates($date, $geo, $height);
            } elseif ($geo !== null) {
                $target->calculateEquatorialCoordinates($date, $geo);
            } else {
                $target->calculateEquatorialCoordinates($date);
            }
            Log::info('HorizonsProxy: vendor helper invoked', ['designation' => $designation ?? null, 'date_utc' => $date->toIso8601String()]);

            try {
                $vc_preview = ['has_method_getEquatorialCoordinatesToday' => method_exists($target, 'getEquatorialCoordinatesToday'), 'has_method_getEquatorialCoordinates' => method_exists($target, 'getEquatorialCoordinates'), 'has_method_getEquatorialCoordinatesFor' => method_exists($target, 'getEquatorialCoordinatesFor')];
                Log::debug('HorizonsProxy: extracting vendor coordinates, target capabilities', $vc_preview);
            } catch (\Throwable $_) {
            }

            // Attempt to extract equatorial coordinates from the concrete target
            try {
                if (method_exists($target, 'getEquatorialCoordinatesToday')) {
                    $vendorCoords = $target->getEquatorialCoordinatesToday();
                } elseif (method_exists($target, 'getEquatorialCoordinates')) {
                    $vendorCoords = $target->getEquatorialCoordinates();
                } elseif (method_exists($target, 'getEquatorialCoordinatesFor')) {
                    // some implementations may provide alternate accessor
                    $vendorCoords = $target->getEquatorialCoordinatesFor($date);
                }
            } catch (\Throwable $_) {
                $vendorCoords = null;
            }
            try {
                Log::debug('HorizonsProxy: vendor result', ['designation' => $designation ?? null, 'vendorCoords_present' => ($vendorCoords !== null), 'vendorCoords_class' => is_object($vendorCoords) ? get_class($vendorCoords) : null]);
            } catch (\Throwable $_) {
            }
        } catch (\Throwable $_) {
            // best-effort: ignore
        }

        // If vendor returned no coordinates but we have a designation, try a
        // last-resort: invoke the local wrapper script which calls the
        // vendor horizons_radec helper and writes a diagnostics wrapper JSON.
        // Afterwards probe the wrapper files for a fresh result.
        if (($vendorCoords === null) && ! empty($designation)) {
            try {
                Log::info('HorizonsProxy: vendor produced no coords; invoking wrapper script as fallback', ['designation' => $designation, 'date_utc' => $date->toIso8601String()]);
                $script = base_path('scripts/horizons_wrapper.php');
                // Prefer the current PHP binary; fall back to env lookup if empty.
                $php = (defined('PHP_BINARY') && trim((string)PHP_BINARY) !== '') ? PHP_BINARY : '/usr/bin/env php';

                // Determine geographical inputs (fallback to 0 if unknown)
                $lon = 0.0;
                $lat = 0.0;
                $alt = 0.0;
                if ($geo !== null) {
                    try {
                        if (method_exists($geo, 'getLongitude')) {
                            $lon = $geo->getLongitude()->getCoordinate();
                        }
                        if (method_exists($geo, 'getLatitude')) {
                            $lat = $geo->getLatitude()->getCoordinate();
                        }
                    } catch (\Throwable $_) {
                        $lon = 0.0;
                        $lat = 0.0;
                    }
                }
                if ($height !== null) {
                    $alt = (float) $height;
                }

                // Verify the script exists and is readable before attempting to run it.
                if (! file_exists($script)) {
                    Log::debug('HorizonsProxy: wrapper script missing', ['script' => $script]);
                } else {
                    // Log script permissions and resolved PHP interpreter for diagnostics.
                    try {
                        // Choose a sensible PHP interpreter for invoking the wrapper.
                        $php_exec_diag = null;
                        $chosenInterpreter = null;
                        if (defined('PHP_BINARY') && trim((string)PHP_BINARY) !== '') {
                            $chosenInterpreter = (string)PHP_BINARY;
                        }
                        $candidates = ['/usr/bin/php', '/usr/local/bin/php', '/bin/php'];
                        foreach ($candidates as $cand) {
                            if ($chosenInterpreter === null && file_exists($cand) && is_executable($cand)) {
                                $chosenInterpreter = $cand;
                                break;
                            }
                        }
                        if ($chosenInterpreter === null) {
                            // fall back to env if no explicit php binary found
                            $php_exec_diag = '/usr/bin/env php';
                        } else {
                            $php_exec_diag = $chosenInterpreter;
                        }
                        $info = [
                            'script' => $script,
                            'exists' => file_exists($script),
                            'readable' => is_readable($script),
                            'executable' => is_executable($script),
                            'perms' => substr(sprintf('%o', fileperms($script)), -4),
                            'php_exec' => $php_exec_diag,
                        ];
                        Log::debug('HorizonsProxy: wrapper script diagnostics', $info);
                    } catch (\Throwable $_) {
                    }

                    if (! is_readable($script)) {
                        // If it's not readable, trying to execute it will likely fail.
                        Log::debug('HorizonsProxy: wrapper script not readable; skipping exec', ['script' => $script]);
                    } else {
                        // Construct the command with an explicit interpreter. If PHP_BINARY
                        // is not available, use '/usr/bin/env' with 'php' as a separate arg
                        // (don't escape the combined string which would make it a single
                        // non-existent executable).
                        // Prefer an explicit php binary when available; otherwise fall
                        // back to '/usr/bin/env' with 'php' as arg.
                        if (! empty($chosenInterpreter)) {
                            $interpreter = $chosenInterpreter;
                            $interpreterArg = null;
                        } else {
                            $interpreter = '/usr/bin/env';
                            $interpreterArg = 'php';
                        }

                        $parts = [];
                        $parts[] = escapeshellcmd($interpreter);
                        if ($interpreterArg !== null) $parts[] = escapeshellarg($interpreterArg);
                        $parts[] = escapeshellarg($script);
                        $parts[] = escapeshellarg((string)$designation);
                        $parts[] = escapeshellarg($date->format('Y-m-d H:i'));
                        $parts[] = escapeshellarg((string)$lon);
                        $parts[] = escapeshellarg((string)$lat);
                        $parts[] = escapeshellarg((string)$alt);

                        $cmd = implode(' ', $parts) . ' 2>&1';

                        $out = [];
                        $ret = 0;
                        exec($cmd, $out, $ret);
                        $outStr = implode("\n", $out);
                        try {
                            Log::debug('HorizonsProxy: wrapper script exit', ['cmd' => $cmd, 'exit' => $ret, 'output' => $outStr]);
                        } catch (\Throwable $_) {
                        }

                        // If the wrapper script printed the explicit diagnostics path (WROTE_DIAG: /path/to/file),
                        // try to load that file directly and return coords immediately. This avoids any subtle
                        // date-matching or normalization mismatch when the wrapper just wrote a fresh file.
                        try {
                            if (preg_match('/WROTE_DIAG:\s*(\S+)/', $outStr, $m)) {
                                $diagPath = $m[1];
                                if (file_exists($diagPath) && is_readable($diagPath)) {
                                    $txt2 = @file_get_contents($diagPath);
                                    $j2 = @json_decode($txt2, true);
                                    $parsed2 = $j2['parsed_stdout'] ?? ($j2['stdout'] ? @json_decode($j2['stdout'], true) : null);
                                    if (is_array($parsed2) && isset($parsed2['ra_hours']) && isset($parsed2['dec_deg'])) {
                                        try {
                                            $coords = new EquatorialCoordinates($parsed2['ra_hours'], $parsed2['dec_deg']);
                                            Log::info('HorizonsProxy: using wrapper coords from WROTE_DIAG path', ['file' => $diagPath, 'ra_hours' => $parsed2['ra_hours'], 'dec_deg' => $parsed2['dec_deg'], 'designation' => $designation ?? null]);
                                        } catch (\Throwable $_) {
                                            $coords = null;
                                        }
                                        if ($coords) {
                                            return ['usedWrapper' => true, 'coords' => $coords, 'source' => $diagPath];
                                        }
                                    }
                                }
                            }
                        } catch (\Throwable $_) {
                            // ignore any diagnostic-file parsing errors and fall through
                        }
                    }
                }

                // Re-check wrapper diagnostics with a modest window to pick up the
                // newly-generated wrapper file (if any).
                $fresh = HorizonsWrapper::latestCoordinatesForDesignation($candidates ?? [$designation], $date, 86400, 120);
                if ($fresh && isset($fresh['ra_hours']) && isset($fresh['dec_deg'])) {
                    try {
                        $coords = new EquatorialCoordinates($fresh['ra_hours'], $fresh['dec_deg']);
                        Log::info('HorizonsProxy: using wrapper coords produced by fallback wrapper script', ['file' => $fresh['source_file'] ?? null, 'ra_hours' => $fresh['ra_hours'], 'dec_deg' => $fresh['dec_deg'], 'designation' => $designation ?? null]);
                    } catch (\Throwable $_) {
                        $coords = null;
                    }
                    if ($coords) {
                        return ['usedWrapper' => true, 'coords' => $coords, 'source' => $fresh['source_file'] ?? null];
                    }
                }
                // If date-matched fresh wrapper file wasn't found, try a looser
                // designation-only probe to pick up newly generated wrapper
                // diagnostics that may have differing internal timestamps or
                // slightly different date formatting. This avoids the UI ending
                // up with no coords when the wrapper just wrote a matching file
                // but the strict date-match failed.
                try {
                    $loose = HorizonsWrapper::latestCoordinatesForDesignation($candidates ?? [$designation], null, 86400, 0);
                    if ($loose && isset($loose['ra_hours']) && isset($loose['dec_deg'])) {
                        try {
                            $coords = new EquatorialCoordinates($loose['ra_hours'], $loose['dec_deg']);
                            Log::warning('HorizonsProxy: using wrapper coords found by designation-only fallback after wrapper invocation', ['file' => $loose['source_file'] ?? null, 'ra_hours' => $loose['ra_hours'], 'dec_deg' => $loose['dec_deg'], 'designation' => $designation ?? null]);
                        } catch (\Throwable $_) {
                            $coords = null;
                        }
                        if ($coords) {
                            return ['usedWrapper' => true, 'coords' => $coords, 'source' => $loose['source_file'] ?? null];
                        }
                    }
                } catch (\Throwable $_) {
                    // ignore
                }
            } catch (\Throwable $e) {
                Log::debug('HorizonsProxy: fallback wrapper invocation failed', ['err' => $e->getMessage()]);
            }
        }

        return ['usedWrapper' => false, 'coords' => $vendorCoords ?? null, 'source' => null];
    }
}
