<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Constellation extends Model
{
    public $incrementing = false;

    /**
     * Adds the link to the targets.
     *
     * @return BelongsTo the targets this constellation belongs to
     */
    public function target()
    {
        return $this->belongsTo('App\Models\Target', 'id', 'constellation');
    }

    /**
     * Get constellations to use in the drop down menu.
     *
     * @return string The list with the constellations
     */
    public static function getConstellationChoices(): String
    {
        $toReturn       = "<option value=''></option>";
        $constellations = Constellation::all();
        foreach ($constellations as $cons) {
            $toReturn .= "<option value='" . $cons['id'] . "'>" . $cons['name'] . '</option>';
        }

        return $toReturn;
    }
}
