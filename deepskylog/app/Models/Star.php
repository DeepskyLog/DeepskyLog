<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Star extends Model
{
    protected $table = 'stars';

    public $timestamps = false;

    protected $guarded = [];

    public function displayName(): ?string
    {
        if (!empty($this->name)) {
            return $this->name;
        }

        $parts = array_filter([
            $this->flamsteed,
            $this->bayer,
            $this->bayer_suffix,
            $this->constellation_code,
        ], fn($value) => $value !== null && $value !== '');

        if ($parts === []) {
            return null;
        }

        return implode(' ', $parts);
    }
}