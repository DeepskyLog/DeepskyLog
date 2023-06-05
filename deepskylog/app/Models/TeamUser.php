<?php

/**
 * Old locations eloquent model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Old locations eloquent model.
 */
class TeamUser extends Model
{
    protected $casts = ['id' => 'string'];

    protected $connection = 'mysql';

    protected $table = 'team_user';
}
