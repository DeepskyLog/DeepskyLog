<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Log;

class HorizonsWrapper
{
    /**
     * Search recent wrapper diagnostics for coordinates matching one of the
     * provided designation candidates. Returns ["ra_hours"=>..., "dec_deg"=>...] or null.
     */
    public static function latestCoordinatesForDesignation(array $candidates, $targetDate = null, int $maxAgeSeconds = 86400, int $toleranceSeconds = 120)
    {
        if (empty($candidates)) return null;
        $checked = 0;
        $candidates = array_values(array_filter(array_map(fn($v) => $v === null ? null : trim((string)$v), $candidates)));
        // Normalize candidates to canonical form to improve matching with wrapper diagnostics
        try {
            if (! empty($candidates)) {
                foreach ($candidates as &$c) {
                    try {
                        $c = \App\Helpers\HorizonsDesignation::canonicalize($c);
                    } catch (\Throwable $_) {
                        // ignore and keep original
                    }
                }
                $candidates = array_values(array_unique(array_filter($candidates)));
            }
        } catch (\Throwable $_) {
            // ignore
        }
        if (empty($candidates)) return null;

        $logdir = storage_path('logs');
        if (! is_dir($logdir)) return null;

        $files = glob($logdir . '/horizons_wrapper_*.json');
        $filesFound = is_array($files) ? count($files) : 0;
        if (! $files) return null;

        // Sort newest first by modification time
        usort($files, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        $now = time();
        // Normalize target timestamp if provided
        $targetTs = null;
        if ($targetDate !== null) {
            if ($targetDate instanceof \DateTimeInterface) {
                $targetTs = $targetDate->getTimestamp();
            } elseif (is_string($targetDate)) {
                $t = @strtotime($targetDate);
                if ($t !== false) $targetTs = $t;
            }
        }
        foreach ($files as $f) {
            $checked++;
            $mtime = @filemtime($f) ?: 0;
            if ($now - $mtime > $maxAgeSeconds) {
                // file too old, stop searching further (files are sorted newest-first)
                break;
            }
            $txt = @file_get_contents($f);
            if (! $txt) continue;
            $json = @json_decode($txt, true);
            if (! is_array($json)) continue;

            // If a target date was provided, prefer files whose internal "date" matches within tolerance
            if ($targetTs !== null) {
                $fileDateStr = $json['date'] ?? null;
                if ($fileDateStr) {
                    // Parse file's date string in the same timezone as the target date
                    $fileTs = false;
                    try {
                        if ($targetDate instanceof \DateTimeInterface) {
                            $tz = $targetDate->getTimezone();
                        } else {
                            $tz = new \DateTimeZone(date_default_timezone_get());
                        }
                        $dt = \DateTime::createFromFormat('Y-m-d H:i', $fileDateStr, $tz);
                        if ($dt !== false) {
                            $fileTs = $dt->getTimestamp();
                        } else {
                            // fallback to strtotime if createFromFormat fails
                            $fileTs = @strtotime($fileDateStr);
                        }
                    } catch (\Throwable $_) {
                        $fileTs = @strtotime($fileDateStr);
                    }

                    if ($fileTs === false || abs($fileTs - $targetTs) > $toleranceSeconds) {
                        // not a match by date, skip
                        continue;
                    }
                } else {
                    // no internal date to compare, skip
                    continue;
                }
            }

            // Try to match top-level designation or parsed designation
            $fileDesig = isset($json['designation']) ? (string)$json['designation'] : null;
            $parsed = $json['parsed_stdout'] ?? ($json['stdout'] ? @json_decode($json['stdout'], true) : null);

                foreach ($candidates as $cand) {
                if ($cand === null || $cand === '') continue;
                // direct match against top-level designation
                if ($fileDesig && stripos($fileDesig, $cand) !== false) {
                        if (is_array($parsed) && isset($parsed['ra_hours']) && isset($parsed['dec_deg'])) {
                            // match found in wrapper diagnostics
                            return ['ra_hours' => $parsed['ra_hours'], 'dec_deg' => $parsed['dec_deg'], 'source_file' => $f];
                        }
                    }
                // sometimes wrapper stores short code while hDesig contains full name; allow substring match both ways
                if ($fileDesig && stripos($cand, $fileDesig) !== false) {
                    if (is_array($parsed) && isset($parsed['ra_hours']) && isset($parsed['dec_deg'])) {
                        return ['ra_hours' => $parsed['ra_hours'], 'dec_deg' => $parsed['dec_deg'], 'source_file' => $f];
                    }
                }
                // check parsed stdout for designation-like fields
                if (is_array($parsed) && isset($parsed['used_command']) && stripos($parsed['used_command'], $cand) !== false) {
                    if (isset($parsed['ra_hours']) && isset($parsed['dec_deg'])) {
                        // match found in parsed stdout
                        return ['ra_hours' => $parsed['ra_hours'], 'dec_deg' => $parsed['dec_deg'], 'source_file' => $f];
                    }
                }
            }
        }

        // no match found in wrapper diagnostics

        return null;
    }
}
