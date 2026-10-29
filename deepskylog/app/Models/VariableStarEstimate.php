<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The magnitude estimate of a variable star, nova or supernova made with an
 * observation (an object of TYPES): the magnitude ("fainter than" when the
 * star was not seen), the labels of the comparison stars on the AAVSO chart
 * (110 for the star of magnitude 11.0), the chart id of the AAVSO Variable
 * Star Plotter and the comment codes of COMMENT_CODES. Exported in the AAVSO
 * Visual File Format by App\Services\Observations\AavsoExport.
 */
class VariableStarEstimate extends Model
{
    /** The object types an estimate can be made of: single stars (variables, novae) and supernovae. */
    public const TYPES = ['AA1STAR', 'SNOVA'];

    /** The comment codes of the AAVSO Visual File Format. */
    public const COMMENT_CODES = ['B', 'U', 'W', 'L', 'D', 'Y', 'K', 'S', 'Z', 'I', 'V'];

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'observation_id';

    protected $fillable = ['observation_id', 'magnitude', 'fainter', 'comparison1', 'comparison2', 'chart', 'comment_codes'];

    protected $casts = [
        'magnitude' => 'float',
        'fainter' => 'boolean',
    ];

    public static function appliesTo(?string $type): bool
    {
        return in_array(strtoupper(trim((string) $type)), self::TYPES, true);
    }

    /**
     * The localized meaning of each comment code.
     *
     * @return array<string, string>
     */
    public static function commentCodeLabels(): array
    {
        return [
            'B' => __('Sky bright (Moon, twilight, light pollution)'),
            'U' => __('Clouds, haze or smoke'),
            'W' => __('Poor seeing'),
            'L' => __('Low in the sky or obstructed'),
            'D' => __('Unusual activity (fading, flare)'),
            'Y' => __('Outburst'),
            'K' => __('Non-AAVSO chart'),
            'S' => __('Problem with the comparison sequence'),
            'Z' => __('Magnitude uncertain'),
            'I' => __('Identification of the star uncertain'),
            'V' => __('Faint star, near the observing limit'),
        ];
    }

    /**
     * The known comment codes among the given letters, in the order of
     * COMMENT_CODES, without repeats; null for none.
     *
     * @param  string|array<int, string>|null  $codes
     */
    public static function normalizeCommentCodes(string|array|null $codes): ?string
    {
        $letters = is_array($codes) ? implode('', $codes) : (string) $codes;
        $letters = str_split(strtoupper(preg_replace('/[^A-Za-z]/', '', $letters)));
        $kept = implode('', array_values(array_intersect(self::COMMENT_CODES, $letters)));

        return $kept === '' ? null : $kept;
    }

    /**
     * Stores the estimate of the observation, or deletes it when $values has
     * no magnitude.
     *
     * @param  array{magnitude?: mixed, fainter?: mixed, comparison1?: ?string, comparison2?: ?string, chart?: ?string, comment_codes?: string|array<int, string>|null}  $values
     */
    public static function saveFor(int $observationId, array $values): void
    {
        $magnitude = $values['magnitude'] ?? null;
        if ($magnitude === null || $magnitude === '' || ! is_numeric($magnitude)) {
            self::whereKey($observationId)->delete();

            return;
        }

        $text = fn (string $key) => ($value = trim((string) ($values[$key] ?? ''))) === '' ? null : $value;

        self::updateOrCreate(['observation_id' => $observationId], [
            'magnitude' => round((float) $magnitude, 2),
            'fainter' => ! empty($values['fainter']),
            'comparison1' => $text('comparison1'),
            'comparison2' => $text('comparison2'),
            'chart' => $text('chart') === null ? null : strtoupper($text('chart')),
            'comment_codes' => self::normalizeCommentCodes($values['comment_codes'] ?? null),
        ]);
    }

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }
}
