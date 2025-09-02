<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessagesReadOld extends Model
{
    protected $connection = 'mysqlOld';

    protected $table = 'messagesRead';

    protected $fillable = ['id', 'receiver'];

    // Legacy table does not have Laravel timestamps
    public $timestamps = false;

    // The id field is the message id (not an auto-incrementing PK)
    public $incrementing = false;
}
