<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lenses extends Model
{
    protected $fillable = [
        'observer_id', 'name', 'factor', 'active'
    ];

    public function active($active = true)
    {
        if ($active == false) {
            $this->update(['active' => 0]);
        } else {
            $this->update(compact('active'));
        }
    }

    public function inactive()
    {
        $this->active(false);
    }

    public function observer()
    {
        // Also method on observer: addLens($lens)
        return $this->belongsTo(Observer::class);
    }

    public function observation()
    {
        return $this->belongsTo(Observation::class);
    }
}
