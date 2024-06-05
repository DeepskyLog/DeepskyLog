<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessagesDeletedOld extends Model
{
    protected $connection = 'mysqlOld';

    protected $table = 'messagesDeleted';
}
