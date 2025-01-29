<?php

/**
 * Old instrument eloquent model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Old instrument eloquent model.
 */
class InstrumentsOld extends Model
{
    protected $casts = ['id' => 'string'];

    protected $connection = 'mysqlOld';

    protected $table = 'instruments';

    /**
     * Establishes a relationship between the InstrumentsOld model and the User model.
     *
     * This method sets the database connection to 'mysql' and defines a one-to-one relationship between the InstrumentsOld model and the User model.
     * The relationship is established based on the 'observer' attribute of the InstrumentsOld model and the 'username' attribute of the User model.
     *
     * @return BelongsTo The relationship between the InstrumentsOld model and the User model.
     */
    public function user(): BelongsTo
    {
        return $this->setConnection('mysql')->belongsTo(related: User::class, foreignKey: 'observer', ownerKey: 'username');
    }
}
