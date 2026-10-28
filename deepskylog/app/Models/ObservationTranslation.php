<?php

namespace App\Models;

use App\Casts\PurifiedHtml;
use App\Support\Html;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The description of an observation translated into one of the languages of
 * the site (#310), written by App\Jobs\TranslateObservationDescriptions and
 * read through App\Services\Observations\DescriptionTranslations. A null
 * description: nothing to show in that language. by_observer marks a
 * description the observer wrote themselves in that language (#321), which
 * is shown instead of Google's translation and never replaced by it.
 */
class ObservationTranslation extends Model
{
    protected $fillable = ['observation_id', 'language', 'description', 'by_observer'];

    protected function casts(): array
    {
        return [
            'by_observer' => 'boolean',
            'description' => PurifiedHtml::class,
        ];
    }

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    /**
     * The descriptions the observer wrote themselves, by language.
     *
     * @return array<string, string>
     */
    public static function byObserverFor(int $observationId): array
    {
        return self::where('observation_id', $observationId)->where('by_observer', true)
            ->pluck('description', 'language')->filter()->all();
    }

    /**
     * Store the descriptions the observer wrote in other languages of the
     * site: an empty one removes theirs (Google's translation takes over
     * again, from the scheduled translations), and one in the language of the
     * observation itself is dropped.
     *
     * @param  array<string, string|null>  $descriptions  by language
     */
    public static function syncByObserver(Observation $observation, array $descriptions): void
    {
        foreach (array_values(config('app.available_locales', [])) as $language) {
            $text = $descriptions[$language] ?? null;
            $own = self::where('observation_id', $observation->id)->where('language', $language)->where('by_observer', true);
            if ($language === $observation->language || Html::isBlank($text)) {
                $own->delete();

                continue;
            }
            self::updateOrCreate(
                ['observation_id' => $observation->id, 'language' => $language],
                ['description' => $text, 'by_observer' => true],
            );
        }
    }
}
