<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageRead extends Model
{
    protected $table = 'messages_read';

    protected $fillable = ['id', 'receiver', 'read_at'];

    public $timestamps = false;

    public $incrementing = false; // id is message id

    protected $primaryKey = ['id', 'receiver'];
}
