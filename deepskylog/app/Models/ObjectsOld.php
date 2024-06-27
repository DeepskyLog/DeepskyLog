<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectsOld extends Model
{
    public $timestamps = false;

    protected $connection = 'mysqlOld';

    protected $table = 'objects';

    public function long_type(): string
    {
        return match ($this->type) {
            'CLANB' => __('Cluster with nebulosity'),
            'PLNNB' => __('Planetary nebula'),
            'OPNCL' => __('Open cluster'),
            'SNREM' => __('Supernova remnant'),
            'GALXY' => __('Galaxy'),
            'EMINB' => __('Emission nebula'),
            'GLOCL' => __('Globular cluster'),
            'NONEX' => __('Nonexistent'),
            'WRNEB' => __('Wolf-Rayet nebula'),
            'DRKNB' => __('Dark nebula'),
            'HII' => __('H-II region'),
            'RNHII' => __('Reflection nebula and H-II'),
            'ENRNN' => __('Emission and reflection nebula'),
            'GALCL' => __('Galaxy cluster'),
            'REFNB' => __('Reflection nebula'),
            'ASTER' => __('Asterism'),
            'DS' => __('Double star'),
            'GACAN' => __('Cluster with nebulosity in galaxy'),
            'QUASR' => __('Quasar'),
            'BRTNB' => __('Bright nebula'),
            'ENSTR' => __('Emission nebula around a star'),
            'GXADN' => __('Diffuse nebula in galaxy'),
            'GXAGC' => __('Globular cluster in galaxy'),
            'LMCCN' => __('Cluster with nebulosity in LMC'),
            'LMCDN' => __('Diffuse nebula in LMC'),
            'LMCGC' => __('Globular cluster in LMC'),
            'LMCOC' => __('Open cluster in LMC'),
            'SMCCN' => __('Cluster with nebulosity in SMC'),
            'SMCDN' => __('Diffuse nebula in SMC'),
            'SMCGC' => __('Globular cluster in SMC'),
            'SMCOC' => __('Open cluster in SMC'),
            'SNOVA' => __('Supernova'),
            'STNEB' => __('Nebula around star'),
            'AA1STAR' => __('Star'),
            'AA3STAR' => __('3 stars'),
            'AA4STAR' => __('4 stars'),
            'AA8STAR' => __('8 stars'),
        };
    }
}
