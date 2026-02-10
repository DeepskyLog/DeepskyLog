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

    /**
     * Return a flat array suitable for indexing by TNTSearch.
     */
    public function toTntArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'source_type' => $this->source_type,
            'metadata' => $this->metadata,
        ];
    }
}
