<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Atlas extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Get atlases to use in the drop down menu.
     *
     * @return string The list with the atlases
     */
    public static function getAtlasChoices(): String
    {
        $toReturn       = '';
        $atlases        = Atlas::all();
        // Check if the user is logged in and has a standard atlas set.
        if (Auth::user()) {
            $selected = Auth::user()->standardAtlasCode;
        } else {
            $selected = null;
        }
        foreach ($atlases as $atlas) {
            if ($atlas['code'] == $selected) {
                $toReturn .= "<option selected='selected' value='" . $atlas['code'] . "'>" . $atlas['name'] . '</option>';
            } else {
                $toReturn .= "<option value='" . $atlas['code'] . "'>" . $atlas['name'] . '</option>';
            }
        }
        return $toReturn;
    }
}
