<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The description of an observation translated into one of the languages of
 * the site (#310), written by App\Jobs\TranslateObservationDescriptions and
 * read through App\Services\Observations\DescriptionTranslations. A null
 * description: nothing to show in that language.
 */
class ObservationTranslation extends Model
{
    protected $fillable = ['observation_id', 'language', 'description'];

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }
}
