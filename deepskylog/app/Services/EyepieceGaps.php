<?php

namespace App\Services;

use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\InstrumentSet;
use App\Models\Lens;
use App\Models\User;

/**
 * The eyepiece ladder of a telescope: the magnifications the observer's
 * active eyepieces give with it (with the lens always used with it, and with
 * each of their other active lenses, a Barlow or reducer, on top), each with
 * its exit pupil and true field, a zoom eyepiece as the range it covers.
 * With an instrument set, only the eyepieces and lenses of that set.
 *
 * gaps() points out what the ladder misses, in exit pupils: a widest field
 * well below what a wide 2" eyepiece gives (low), consecutive steps more than
 * GAP_RATIO apart (between) and no step near the highest useful magnification
 * (high), and suggests the focal lengths of eyepieces that fill them, at most
 * MAX_SUGGESTIONS_PER_GAP per gap, spaced about STEP_RATIO apart. A missing
 * end counts as a step for the gaps between, so a ladder of one eyepiece gets
 * suggestions over the whole range. EquipmentGain (PLANNED_EYEPIECE) counts
 * the objects a suggested eyepiece adds, with magnifications().
 */
class EyepieceGaps
{
    /** Consecutive exit pupils further apart than this factor leave a gap. */
    public const GAP_RATIO = 1.7;

    /** The spacing the suggestions in a gap aim at. */
    public const STEP_RATIO = 1.5;

    public const MAX_SUGGESTIONS_PER_GAP = 2;

    /**
     * The exit pupil (mm) of the lowest magnification suggested: a wider one
     * only shows a brighter sky, and an older eye's pupil is smaller.
     */
    public const LOW_EXIT_PUPIL_MM = 6.0;

    /** The exit pupil (mm) of the highest useful magnification, about twice the aperture in mm. */
    public const HIGH_EXIT_PUPIL_MM = 0.7;

    /** Above this magnification the seeing rarely allows a sharp image. */
    public const MAX_MAGNIFICATION = 350;

    /** The widest field stop (mm) of a 2" eyepiece. */
    public const FIELD_STOP_MM = 46.0;

    /** The apparent field (degrees) of the suggested eyepieces. */
    public const SUGGESTED_APPARENT_FOV = 68.0;

    /** A widest field below this part of the suggested lowest magnification's is a gap. */
    public const FIELD_SHORTFALL = 0.8;

    /** A smallest exit pupil more than this factor above the highest useful one is a gap. */
    public const HIGH_SHORTFALL = 1.4;

    /** @var list<array>|null */
    private ?array $pieces = null;

    public function __construct(public readonly User $user, public readonly Instrument $instrument, public readonly ?InstrumentSet $set = null)
    {
    }

    /**
     * The observer's instrument set of the id; null for none or another
     * observer's.
     */
    public static function set(User $user, mixed $id): ?InstrumentSet
    {
        return is_numeric($id) && (int) $id > 0 ? InstrumentSet::where('user_id', $user->id)->find((int) $id) : null;
    }

    /**
     * Whether the instrument takes eyepieces: a telescope with an aperture and
     * focal length, not binoculars or a finder with a fixed magnification.
     */
    public static function applies(Instrument $instrument): bool
    {
        return (float) $instrument->aperture_mm     > 0
            && (float) $instrument->focal_length_mm > 0
            && ! ((float) $instrument->fixedMagnification > 0);
    }

    /**
     * The focal length (mm) with the lens always used with the telescope.
     */
    public function focalLength(): float
    {
        return (float) $this->instrument->focal_length_mm * ContrastReserveCalculator::lensFactor($this->instrument->lens_id);
    }

    public function focalRatio(): float
    {
        return $this->focalLength() / (float) $this->instrument->aperture_mm;
    }

    /**
     * The rows of the ladder, the lowest magnification first: a magnification
     * (a range for a zoom), its exit pupils, the widest true field (degrees,
     * null when unknown) and the eyepieces (with a lens) giving it.
     *
     * @return list<array{magnification: int, max_magnification: ?int, exit_pupil: float, min_exit_pupil: float, field: ?float, combinations: list<array{eyepiece: Eyepiece, lens: ?Lens}>}>
     */
    public function steps(): array
    {
        $rows = [];
        foreach ($this->pieces() as $piece) {
            $key = $piece['magnification'].'-'.$piece['max_magnification'];
            if (! isset($rows[$key])) {
                $rows[$key] = [...$piece, 'combinations' => []];
            } elseif ($piece['field'] !== null && ($rows[$key]['field'] === null || $piece['field'] > $rows[$key]['field'])) {
                $rows[$key]['field'] = $piece['field'];
            }
            $rows[$key]['combinations'][] = ['eyepiece' => $piece['eyepiece'], 'lens' => $piece['lens']];
        }
        $rows = array_values(array_map(fn (array $row) => array_diff_key($row, ['eyepiece' => true, 'lens' => true]), $rows));
        usort($rows, fn (array $a, array $b) => [$a['magnification'], $a['max_magnification']] <=> [$b['magnification'], $b['max_magnification']]);

        return $rows;
    }

    /**
     * The gaps of the ladder, from the low to the high magnifications, each
     * with the eyepieces suggested to fill it. A low gap gives the widest
     * field there is (`field`), the largest exit pupil (`from`) and the
     * lowest magnification; a high gap the smallest exit pupil (`from`) and
     * highest magnification; a gap between the exit pupils on both sides
     * (`from` > `to`) and their magnifications.
     *
     * @return list<array{kind: string, from: ?float, to: ?float, field: ?float, magnification: ?int, to_magnification: ?int, suggestions: list<array>}>
     */
    public function gaps(): array
    {
        $pieces = $this->pieces();
        $high   = $this->highExitPupil();
        $lowest = $this->suggestion($this->lowFocalLength(), self::SUGGESTED_APPARENT_FOV);

        // a step at more than the highest useful magnification fills nothing
        $useful = array_values(array_filter($pieces, fn (array $piece) => $piece['exit_pupil'] >= $high / self::HIGH_SHORTFALL));
        $fields = array_filter(array_column($pieces, 'field'), fn ($field) => $field !== null);
        $widest = $fields ? max($fields) : null;
        $ends   = array_map(fn (array $piece) => [$piece['exit_pupil'], $piece['min_exit_pupil'], $piece['magnification'], $piece['max_magnification'] ?? $piece['magnification']], $useful);

        $gaps = [];
        if ($pieces === []
            || ($widest !== null && $widest < self::FIELD_SHORTFALL * $lowest['field'])
            || ($widest === null && max(array_column($pieces, 'exit_pupil')) < self::FIELD_SHORTFALL * $lowest['exit_pupil'])) {
            $gaps['low'] = [
                'kind'             => 'low',
                'from'             => $pieces ? round(max(array_column($pieces, 'exit_pupil')), 1) : null,
                'to'               => null,
                'field'            => $widest,
                'magnification'    => $pieces ? min(array_column($pieces, 'magnification')) : null,
                'to_magnification' => null,
                'suggestions'      => [$lowest],
            ];
            $ends[] = [$lowest['exit_pupil'], $lowest['exit_pupil'], $lowest['magnification'], $lowest['magnification']];
        }

        $smallest = $useful ? min(array_column($useful, 'min_exit_pupil')) : null;
        if ($smallest === null || $smallest > self::HIGH_SHORTFALL * $high) {
            $highest      = $this->suggestion($this->roundFocalLength($high * $this->focalRatio()), self::SUGGESTED_APPARENT_FOV);
            $gaps['high'] = [
                'kind'             => 'high',
                'from'             => $smallest === null ? null : round($smallest, 1),
                'to'               => null,
                'field'            => null,
                'magnification'    => $useful ? max(array_map(fn (array $piece) => $piece['max_magnification'] ?? $piece['magnification'], $useful)) : null,
                'to_magnification' => null,
                'suggestions'      => [$highest],
            ];
            $ends[] = [$highest['exit_pupil'], $highest['exit_pupil'], $highest['magnification'], $highest['magnification']];
        }

        // the steps from the largest exit pupil down, as intervals (a zoom covers its range)
        usort($ends, fn (array $a, array $b) => $b[0] <=> $a[0]);
        $between = [];
        $upper   = null;
        foreach ($ends as $end) {
            if ($upper !== null && $end[0] < $upper[1] && $upper[1] / $end[0] > self::GAP_RATIO) {
                $between[] = [
                    'kind'             => 'between',
                    'from'             => round($upper[1], 1),
                    'to'               => round($end[0], 1),
                    'field'            => null,
                    'magnification'    => $upper[3],
                    'to_magnification' => $end[2],
                    'suggestions'      => $this->fill($upper[1], $end[0]),
                ];
            }
            if ($upper === null || $end[1] < $upper[1]) {
                $upper = $end;
            }
        }

        $gaps = [...(isset($gaps['low']) ? [$gaps['low']] : []), ...$between, ...(isset($gaps['high']) ? [$gaps['high']] : [])];

        // a suggestion the observer already has (a gap only there through a lens) is left out
        $owned = array_map(fn (array $piece) => (float) $piece['eyepiece']->focal_length_mm, array_filter($pieces, fn (array $piece) => $piece['lens'] === null));
        foreach ($gaps as $i => $gap) {
            $gaps[$i]['suggestions'] = array_values(array_filter($gap['suggestions'], fn (array $suggestion) => ! in_array($suggestion['focal_length'], $owned, false)));
        }

        return array_values(array_filter($gaps, fn (array $gap) => $gap['suggestions'] !== []));
    }

    /**
     * The magnifications of the ladder with the eyepieces giving them, for
     * ContrastReserveCalculator::calculate(), with a planned eyepiece when
     * given. An eyepiece used with a lens stands in as a copy whose field stop
     * is divided by the lens factor, so ContrastReserveCalculator::fields()
     * gives its true field; a zoom gives both ends.
     *
     * @return array<int, list<Eyepiece>>
     */
    public function magnifications(?Eyepiece $planned = null): array
    {
        $pieces = $this->pieces();
        if ($planned) {
            $pieces = [...$pieces, ...$this->piecesOf($planned, null)];
        }

        $magnifications = [];
        foreach ($pieces as $piece) {
            $factor = $piece['lens'] ? (float) $piece['lens']->factor : 1.0;
            $copy   = $factor === 1.0 ? $piece['eyepiece'] : (clone $piece['eyepiece'])->forceFill([
                'field_stop_mm' => (float) $piece['eyepiece']->field_stop_mm > 0 ? (float) $piece['eyepiece']->field_stop_mm / $factor : null,
            ]);
            $magnifications[$piece['magnification']][] = $copy;
            if ($piece['max_magnification'] !== null) {
                // the field of the short end of a zoom: estimated from its apparent field
                $magnifications[$piece['max_magnification']][] = (clone $copy)->forceFill(['field_stop_mm' => null]);
            }
        }
        ksort($magnifications);

        return $magnifications;
    }

    /**
     * What an eyepiece of the focal length would give with the telescope: its
     * magnification, exit pupil and true field (with the apparent field, at
     * most the field stop of a 2" eyepiece; else estimated).
     *
     * @return array{focal_length: float, apparent_fov: ?float, magnification: int, exit_pupil: float, field: float}
     */
    public function suggestion(float $focalLength, ?float $apparentFov): array
    {
        $eyepiece = self::plannedEyepiece(['focal_length' => $focalLength, 'apparent_fov' => $apparentFov]);
        $piece    = $this->piecesOf($eyepiece, null)[0];

        return [
            'focal_length'  => $focalLength,
            'apparent_fov'  => $apparentFov,
            'magnification' => $piece['magnification'],
            'exit_pupil'    => round($piece['exit_pupil'], 1),
            'field'         => $piece['field'] ?? Instrument::FIXED_MAGNIFICATION_APPARENT_FOV / $piece['magnification'],
        ];
    }

    /**
     * An unsaved eyepiece of the focal length (mm) and apparent field
     * (degrees, null when unknown), with the field stop that apparent field
     * gives, at most FIELD_STOP_MM.
     */
    public static function plannedEyepiece(array $planned): Eyepiece
    {
        $focalLength = (float) $planned['focal_length'];
        $apparentFov = isset($planned['apparent_fov']) && (float) $planned['apparent_fov'] > 0 ? (float) $planned['apparent_fov'] : null;

        $eyepiece = (new Eyepiece())->forceFill([
            'name'                => '',
            'focal_length_mm'     => $focalLength,
            'max_focal_length_mm' => null,
            'apparentFOV'         => $apparentFov,
            'field_stop_mm'       => $apparentFov ? min(self::FIELD_STOP_MM, $focalLength * deg2rad($apparentFov)) : null,
            'active'              => true,
        ]);
        $eyepiece->setRelation('eyepiece_make', null);
        $eyepiece->setRelation('eyepiece_type', null);

        return $eyepiece;
    }

    /**
     * The exit pupil of the highest useful magnification: HIGH_EXIT_PUPIL_MM,
     * or that of MAX_MAGNIFICATION for a large aperture.
     */
    public function highExitPupil(): float
    {
        return max(self::HIGH_EXIT_PUPIL_MM, (float) $this->instrument->aperture_mm / self::MAX_MAGNIFICATION);
    }

    /**
     * The focal length of the suggested lowest magnification: an exit pupil of
     * LOW_EXIT_PUPIL_MM, unless a wide 2" eyepiece reaches its field stop
     * before that.
     */
    private function lowFocalLength(): float
    {
        return $this->roundFocalLength(min(self::LOW_EXIT_PUPIL_MM * $this->focalRatio(), self::FIELD_STOP_MM / deg2rad(self::SUGGESTED_APPARENT_FOV)));
    }

    /**
     * The eyepieces filling the gap between two exit pupils, evenly spaced
     * on a logarithmic scale, about STEP_RATIO apart.
     *
     * @return list<array>
     */
    private function fill(float $from, float $to): array
    {
        $ratio = $from / $to;
        $count = (int) max(1, min(self::MAX_SUGGESTIONS_PER_GAP, ceil(log($ratio) / log(self::STEP_RATIO)) - 1));

        $suggestions = [];
        for ($i = 1; $i <= $count; $i++) {
            $exitPupil   = $from * $ratio ** (-$i / ($count + 1));
            $focalLength = $this->roundFocalLength($exitPupil * $this->focalRatio());
            if (! isset($suggestions[(string) $focalLength])) {
                $suggestions[(string) $focalLength] = $this->suggestion($focalLength, self::SUGGESTED_APPARENT_FOV);
            }
        }

        return array_values($suggestions);
    }

    /**
     * Focal lengths eyepieces are sold in: to half a millimetre below 10 mm,
     * whole millimetres above.
     */
    private function roundFocalLength(float $focalLength): float
    {
        return $focalLength < 10 ? max(1.0, round($focalLength * 2) / 2) : round($focalLength);
    }

    /**
     * Every eyepiece with every lens (and without), as magnification ranges.
     *
     * @return list<array{magnification: int, max_magnification: ?int, exit_pupil: float, min_exit_pupil: float, field: ?float, eyepiece: Eyepiece, lens: ?Lens}>
     */
    private function pieces(): array
    {
        if ($this->pieces !== null) {
            return $this->pieces;
        }
        $eyepieces = Eyepiece::where('user_id', $this->user->id)
            ->where('active', true)
            ->where('focal_length_mm', '>', 0)
            ->when($this->set, fn ($query) => $query->whereIn('id', $this->set->eyepieces()->pluck('eyepieces.id')))
            ->orderByFocalLength('desc')
            ->get();
        $lenses = Lens::where('user_id', $this->user->id)
            ->where('active', true)
            ->where('factor', '>', 0)
            ->when($this->set, fn ($query) => $query->whereIn('id', $this->set->lenses()->pluck('lenses.id')))
            ->when($this->instrument->lens_id, fn ($query) => $query->whereKeyNot($this->instrument->lens_id))
            ->get()
            ->sortBy('factor')
            ->values();

        $pieces = [];
        foreach ($eyepieces as $eyepiece) {
            foreach ([null, ...$lenses] as $lens) {
                array_push($pieces, ...$this->piecesOf($eyepiece, $lens));
            }
        }

        return $this->pieces = $pieces;
    }

    /**
     * The magnification range of an eyepiece with a lens; none when it gives
     * no magnification.
     *
     * @return list<array>
     */
    private function piecesOf(Eyepiece $eyepiece, ?Lens $lens): array
    {
        $focalLength = $this->focalLength() * ($lens ? (float) $lens->factor : 1.0);
        $aperture    = (float) $this->instrument->aperture_mm;
        $long        = (float) $eyepiece->focal_length_mm;
        $short       = $long;
        if ((float) $eyepiece->max_focal_length_mm > $long) {
            // a zoom: focal_length_mm is its short end
            $long = (float) $eyepiece->max_focal_length_mm;
        }
        $magnification = (int) round($focalLength / $long);
        if ($magnification <= 0) {
            return [];
        }
        $maxMagnification = $short < $long ? (int) round($focalLength / $short) : null;

        if ((float) $eyepiece->field_stop_mm > 0 && $short === $long) {
            $field = (float) $eyepiece->field_stop_mm / $focalLength * 57.2958;
        } elseif ((float) $eyepiece->apparentFOV > 10) {
            $field = (float) $eyepiece->apparentFOV / ($focalLength / $long);
        } else {
            $field = null;
        }

        return [[
            'magnification'     => $magnification,
            'max_magnification' => $maxMagnification !== $magnification ? $maxMagnification : null,
            'exit_pupil'        => $aperture / ($focalLength / $long),
            'min_exit_pupil'    => $aperture / ($focalLength / $short),
            'field'             => $field,
            'eyepiece'          => $eyepiece,
            'lens'              => $lens,
        ]];
    }
}
