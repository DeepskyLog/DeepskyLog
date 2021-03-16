<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ObservationListOld extends Pivot
{
    protected $connection = 'mysqlOld';

    protected $table = 'observerobjectlist';
}
