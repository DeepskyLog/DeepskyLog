<?php

namespace App\Models;

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
            $type = TargetType::where('id', $this->type)->first();
            return $type ? __($type->type) : __('object');
        } catch (\Throwable $_) {
            return __('object');
        }
    }
}
