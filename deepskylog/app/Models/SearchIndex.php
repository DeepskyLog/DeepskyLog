<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchIndex extends Model
{
    protected $table = 'search_index';
    protected $guarded = [];
    protected $casts = [
        'metadata' => 'array',
    ];
}
