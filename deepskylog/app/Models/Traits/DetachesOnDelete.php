<?php

namespace App\Models\Traits;

use App\Models\User;

/**
 * Cleans up references to equipment or a location before it is deleted: the
 * instrument_set_* pivot rows (their foreign keys have no cascade, so the
 * delete would fail) and the users that have it as their default.
 *
 * The model defines instrument_sets() and, when users can pick it as their
 * default, standardUserColumn() returning the users column (e.g. stdtelescope).
 */
trait DetachesOnDelete
{
    protected static function bootDetachesOnDelete(): void
    {
        static::deleting(function (self $model) {
            $model->instrument_sets()->detach();

            $column = $model->standardUserColumn();
            if ($column !== null) {
                User::where($column, $model->getKey())->update([$column => null]);
            }
        });
    }

    public function standardUserColumn(): ?string
    {
        return null;
    }
}
