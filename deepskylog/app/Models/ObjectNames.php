<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for the `objectnames` table (aliases/canonical names).
 *
 * This replaces the legacy `ObjectNamesOld` model and explicitly uses the
 * primary `mysql` connection so alias lookups read from the current DB.
 */
class ObjectNames extends Model
{
    // Use the primary mysql connection for objectnames
    protected $connection = 'mysql';

    protected $table = 'objectnames';

    protected $guarded = [];

    public $timestamps = false;
}
