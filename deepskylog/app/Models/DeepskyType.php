<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeepskyType extends Model
{
    // Table name follows Laravel convention: deepskytypes
    public $timestamps = false;
    public $table = 'deepskytypes';
    
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
}
