<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiltersOld extends Model
{
    protected $casts = ['id' => 'string'];

    protected $connection = 'mysqlOld';

    protected $table = 'filters';

    /**
     * Establishes a relationship between the FiltersOld model and the User model.
     *
     * This method sets the database connection to 'mysql' and defines a one-to-one relationship between the FiltersOld model and the User model.
     * The relationship is established based on the 'observer' attribute of the FiltersOld model and the 'username' attribute of the User model.
     *
     * @return BelongsTo The relationship between the FiltersOld model and the User model.
     */
    public function user(): BelongsTo
    {
        return $this->setConnection('mysql')->belongsTo(related: User::class, foreignKey: 'observer', ownerKey: 'username');
    }
}
