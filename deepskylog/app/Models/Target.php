<?php

namespace App\Models;

use App\Services\Observations\ObservationCardData;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    protected $table = 'objects';
    protected $guarded = [];
    public $timestamps = false;

    /**
     * Return the human-friendly long type for this target.
     */
    public function long_type(): string
    {
        try {
            // From the target types kept for the request (the cards call this per card).
            $type = ObservationCardData::instance()->targetTypeName($this->type);
            return $type !== null ? __($type) : __('object');
        } catch (\Throwable $_) {
            return __('object');
        }
    }
}
