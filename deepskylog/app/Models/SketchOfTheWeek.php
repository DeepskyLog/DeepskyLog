<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SketchOfTheWeek extends Model
{
    public $timestamps = false;

    protected $with = ['observation', 'user'];

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

    /**
     * Establishes a relationship between the SketchOfTheWeek model and the User model.
     *
     * This method defines a one-to-one relationship between the SketchOfTheWeek model and the User model.
     * The relationship is established based on the 'user_id' attribute of the SketchOfTheWeek model and the 'id' attribute of the User model.
     *
     * @return HasOne The relationship between the SketchOfTheWeek model and the User model.
     */
    public function user(): HasOne
    {
        return $this->hasOne(related: User::class, foreignKey: 'id', localKey: 'user_id');
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
