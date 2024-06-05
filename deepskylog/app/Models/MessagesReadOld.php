<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessagesReadOld extends Model
{
    protected $connection = 'mysqlOld';

    protected $table = 'messagesRead';
}
