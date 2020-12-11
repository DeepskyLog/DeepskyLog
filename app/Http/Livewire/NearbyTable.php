<?php

namespace App\Http\Livewire;

use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use deepskylog\AstronomyLibrary\Coordinates\Coordinate;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class NearbyTable extends LivewireDatatable
{
    public $zoom;
    public $targetid;
    public $slug;
    private $_currentTarget;
    private $_targets;
    private $_types;
    public $_constellations = [];

    protected $listeners = [
        'zoomUpdated' => 'zoomUpdated', 'locationChanged' => 'locationChanged', 'instrumentChanged' => 'locationChanged',
    ];

    public function locationChanged()
    {
        // When the location of the instrument is changed, the values should be recalculated
        $targetname                   = \App\Models\TargetName::where('slug', $this->slug)->with('target')->first();
        $this->_targets               = $targetname->target->getNearbyObjects($this->zoom);
    }

    public function zoomUpdated($zoom)
    {
        $this->zoom                   = $zoom;
        $targetname                   = \App\Models\TargetName::where('slug', $this->slug)->with('target')->first();
        $this->_targets               = $targetname->target->getNearbyObjects($this->zoom);
        $this->_constellations        = $this->_targets->groupBy('constellation')->get()->pluck('constellation')->flatten()->toArray();
    }

    public function builder()
    {
        $targetname = \App\Models\TargetName::where('slug', $this->slug)->with('target')->first();
        // dd($targetname->target->ra);
        // $target = \App\Models\Target::where('id', $this->targetid)->first();
        $this->_targets               = $targetname->target->getNearbyObjects($this->zoom);
        // TODO: Should we move this to targetnames to be able to search for names?
        // dd($this->_targets->get());
        $this->_constellations = $this->_targets->groupBy('constellation')->get()->pluck('constellation')->flatten()->toArray();

        return $this->_targets;
    }

    public function columns()
    {
        $toReturn = [
            Column::name('target_name')->callback(['target_name', 'id'], function ($target_name, $id) {
                $this->_currentTarget = \App\Models\Target::where('id', $id)->first();
                return '<a href="/target/' . $this->_currentTarget->slug . '">' . $this->_currentTarget->target_name . '</a>';
            })->label(_i('Name'))
            ->searchable('target_name'),
            Column::name('constellation.name')->label(_i('Constellation'))
                ->filterable($this->constellations),
            NumberColumn::name('mag')->label(_i('Mag'))->filterable(),
            NumberColumn::name('subr')->label(_i('SB'))->filterable(),
            Column::name(_i('type.type'))
            ->callback(['type.type'], function ($type) {
                return _i($type);
            })
            ->label(_i('Type'))->filterable($this->getTypes()),
            Column::name('diam1')->callback(['id', 'pa'], function ($id, $pa) {
                if ($pa != 999) {
                    return $this->_currentTarget->size() . '/' . $this->_currentTarget->pa . '°';
                } else {
                    return $this->_currentTarget->size();
                }
            })->sortBy('diam1')->label(_i('Size')),
            Column::name('ra')->callback(['ra'], function ($ra) {
                return (new Coordinate($ra))->convertToHours();
            })->label(_i('RA')),
            Column::name('decl')->callback(['decl'], function ($decl) {
                return (new Coordinate($decl))->convertToDegrees();
            })->label(_i('Decl')), ];

        if (auth()->user()) {
            array_push(
                $toReturn,
                Column::name(auth()->user()->standardAtlasCode)->label(_i(
                    \App\Models\Atlas::where(
                        'code',
                        auth()->user()->standardAtlasCode
                    )->first()->name
                ))
            );
            array_push(
                $toReturn,
                Column::name('SBObj')->callback(['SBObj', 'id'], function ($SBObj, $id) {
                    return '<span class="' . $this->_currentTarget->contrast_type
                       . '" data-toggle="tooltip" data-placement="bottom" title="'
                       . $this->_currentTarget->contrast_popup . '">' . $this->_currentTarget->contrast
                       . '</span>';
                })->label(_i('Contrast Reserve'))
            );
            array_push(
                $toReturn,
                Column::name('SBObj')->callback(['SBObj', 'id', 'target_name'], function ($SBObj, $id, $target_name) {
                    return $this->_currentTarget->prefMagEasy;
                })->label(_i('Preferred Magnification'))
            );
            array_push(
                $toReturn,
                Column::name('rise')->callback(['SBObj', 'id', 'constellation'], function ($SBObj, $id, $constellation) {
                    return '<span data-toggle="tooltip" data-placement="bottom" title="'
                . $this->_currentTarget->rise_popup . '">' . $this->_currentTarget->rise . '</span>';
                })->label(_i('Rise'))
            );
            // TODO: Add column with Seen
            // TODO: Add column with Last Seen
            array_push(
                $toReturn,
                Column::name('transit')->callback(['SBObj', 'id', 'ra'], function ($SBObj, $id, $ra) {
                    return '<span data-toggle="tooltip" data-placement="bottom" title="'
                . $this->_currentTarget->transit_popup . '">' . $this->_currentTarget->transit . '</span>';
                })->label(_i('Transit'))
            );
            array_push(
                $toReturn,
                Column::name('set')->callback(['SBObj', 'id', 'decl'], function ($SBObj, $id, $decl) {
                    return '<span data-toggle="tooltip" data-placement="bottom" title="'
                . $this->_currentTarget->set_popup . '">' . $this->_currentTarget->set . '</span>';
                })->label(_i('Set'))
            );
            array_push(
                $toReturn,
                Column::name('BestTime')->callback(['SBObj', 'id', 'mag'], function ($SBObj, $id, $mag) {
                    return $this->_currentTarget->bestTime;
                })->label(_i('Best Time'))
            );
            array_push(
                $toReturn,
                Column::name('MaxAlt')->callback(['SBObj', 'id', 'subr'], function ($SBObj, $id, $subr) {
                    return '<span data-toggle="tooltip" data-placement="bottom" title="'
                . $this->_currentTarget->maxAlt_popup . '">' . $this->_currentTarget->maxAlt . '</span>';
                })->label(_i('Max Alt.'))
            );
            array_push(
                $toReturn,
                Column::name('HighestAlt')->callback(['SBObj', 'id', 'diam1'], function ($SBObj, $id, $diam1) {
                    return $this->_currentTarget->highest_alt;
                })->label(_i('Highest Alt.'))
            );
        };

        return $toReturn;
    }

    public function getConstellations()
    {
        // $target = \App\Models\Target::where('id', $this->targetid)->first();

        // $targets               = $target->getNearbyObjects($this->zoom);

        // // TODO: Does not work...
        // // TODO: Also add translation to real constellation name

        if ($this->_constellations) {
            dd($this->_constellations);
            return $this->_constellations;
        }
        if ($this->_targets) {
            // dd($this->_targets);
            return $this->_targets->groupBy('constellation')->get()->pluck('constellation')->flatten()->toArray();
        } else {
            return [];
        }
    }

    public function getConstellationsProperty()
    {
        return \App\Models\Constellation::pluck('name', 'id');
        if ($this->_targets) {
            dd($this->_targets);
            return $this->_targets->groupBy('constellation')->get()->pluck('constellation')->flatten()->toArray();
        } else {
            return [];
        }
        // return Planet::pluck('name');
    }

    public function getTypes()
    {
        // TODO: Implement
        return $this->_types;
    }

    // TODO: Search for the name of the object
    // TODO: Filter on constellation
    // TODO: Filter on type
    // TODO: Sort on preferred magnification
    // TODO: Sort on rise / set / transit / best time
    // TODO: Sort on max altitude / max elevation
    // TODO: Contrast reserve is not correct -> Berk 59 is -4.19 and should be 0.15
        // TODO:    Test to see if the sorting of contrast reserve is ok
}
