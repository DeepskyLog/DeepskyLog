<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageDeleted extends Model
{
    protected $table = 'messages_deleted';

    protected $fillable = ['id', 'receiver', 'deleted_at'];

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = ['id', 'receiver'];
}
