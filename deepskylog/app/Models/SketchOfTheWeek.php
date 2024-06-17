<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SketchOfTheWeek extends Model
{
    public $timestamps = false;

    protected $table = 'sketch_of_the_week';

    /**
     * Establishes a relationship between the SketchOfTheWeek model and the ObservationsOld model.
     *
     * This method defines a one-to-one relationship between the SketchOfTheWeek model and the ObservationsOld model.
     * The relationship is established based on the 'observation_id' attribute of the SketchOfTheWeek model and the 'id' attribute of the ObservationsOld model.
     *
     * @return HasOne The relationship between the SketchOfTheWeek model and the ObservationsOld model.
     */
    public function observation(): HasOne
    {
        return $this->hasOne(related: ObservationsOld::class, foreignKey: 'id', localKey: 'observation_id');
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
