<?php

namespace App\Http\Livewire;

use App\Models\Eyepiece;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use deepskylog\AstronomyLibrary\Coordinates\Coordinate;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class EyepieceTable extends LivewireDatatable
{
    public $model = Eyepiece::class;
    public $instrument;

    public function builder()
    {
        if (auth()->user()->isAdmin()) {
            return Eyepiece::with('user')->select('eyepieces.*');
        } else {
            return Eyepiece::where(
                'user_id',
                auth()->user()->id
            )->select('eyepieces.*');
        }
    }

    public function columns()
    {
        $toReturn = [
            Column::name('name')->callback(['name', 'id'], function ($name, $id) {
                return '<a href="/eyepiece/' . $id . '">' . $name . '</a>';
            })->label(_i('Name'))
            ->searchable(),
            NumberColumn::callback(['focalLength'], function ($focalLength) {
                return $focalLength . 'mm';
            })->label(_i('Focal Length'))
            ->defaultSort('asc'),
            NumberColumn::callback(['maxFocalLength'], function ($maxFocalLength) {
                if ($maxFocalLength) {
                    return $maxFocalLength . 'mm';
                } else {
                    return '';
                }
            })->label(_i('Maximum Focal Length')),
            NumberColumn::callback(['apparentFOV'], function ($apparentFOV) {
                if ($apparentFOV) {
                    return $apparentFOV . 'º';
                } else {
                    return '';
                }
            })->label(_i('Apparent Field of View')), ];
        if (!auth()->user()->isAdmin()) {
            array_push(
                $toReturn,
                NumberColumn::callback(['apparentFOV', 'focalLength'], function ($apparentFOV, $focalLength) {
                    if ($apparentFOV) {
                        if (auth()->user()->stdtelescope) {
                            $instrument = \App\Models\Instrument::where('id', auth()->user()->stdtelescope)->first();
                            if ($instrument->fd) {
                                if (auth()->user()->stdlens) {
                                    $factor = \App\Models\Lens::where('id', auth()->user()->stdlens)->first()->factor;
                                } else {
                                    $factor = 1;
                                }
                                return (new Coordinate($apparentFOV / (($instrument->diameter * $instrument->fd * $factor / $focalLength))))->convertToShortDegrees();
                            }
                        }
                    } else {
                        return '';
                    }
                })->label(_i('True Field of View'))
                ->sortBy('apparentFOV * focalLength')
            );
            array_push(
                $toReturn,
                NumberColumn::callback(['focalLength', 'name'], function ($focalLength, $name) {
                    if (auth()->user()->stdtelescope) {
                        $instrument = \App\Models\Instrument::where('id', auth()->user()->stdtelescope)->first();
                        if ($instrument->fd) {
                            if (auth()->user()->stdlens) {
                                $factor = \App\Models\Lens::where('id', auth()->user()->stdlens)->first()->factor;
                            } else {
                                $factor = 1;
                            }
                            return round($instrument->diameter * $instrument->fd * $factor / $focalLength) . 'x';
                        }
                    }
                    return '';
                })->label(_i('Magnification'))
            ->sortBy('focalLength')
            );

            array_push(
                $toReturn,
                NumberColumn::callback(['focalLength', 'name', 'apparentFOV'], function ($focalLength, $name, $apparentFOV) {
                    if (auth()->user()->stdtelescope) {
                        $instrument = \App\Models\Instrument::where('id', auth()->user()->stdtelescope)->first();
                        if (auth()->user()->stdlens) {
                            $factor = \App\Models\Lens::where('id', auth()->user()->stdlens)->first()->factor;
                        } else {
                            $factor = 1;
                        }
                        if ($instrument->fd) {
                            return round($instrument->diameter / ($instrument->diameter * $instrument->fd * $factor / $focalLength), 1) . 'mm';
                        }
                    }
                    return '';
                })->label(_i('Pupil Size'))
                ->sortBy('focalLength')
            );
        }
        array_push(
            $toReturn,
            Column::name('brand')
                ->label(_i('Brand'))
                ->filterable(\App\Models\Eyepiece::where(
                    'user_id',
                    auth()->user()->id
                )->where('brand', '!=', '')->groupBy('brand')->pluck('brand')->flatten()->toArray())
        );
        array_push(
            $toReturn,
            Column::name('type')
                ->label(_i('Type'))
                ->filterable(\App\Models\Eyepiece::where(
                    'user_id',
                    auth()->user()->id
                )->where('type', '!=', '')->groupBy('type')->pluck('type')->flatten()->toArray())
        );
        // NumberColumn::name('observations')
        //     ->label(_i('Observations')),
        if (auth()->user()->isAdmin()) {
            array_push(
                $toReturn,
                Column::name('user.name')->callback(['user.name', 'user.slug'], function ($user_name, $user_slug) {
                    return '<a href="/users/' . $user_slug . '">' . $user_name . '</a>';
                })->label(_i('User name'))->searchable('user.name')
            );
        } else {
            array_push(
                $toReturn,
                BooleanColumn::name('active')->callback(['active', 'id'], function ($active, $id) {
                    $toReturn = '<form>
            <input type="checkbox" wire:click="$emit(\'activate\',' . $id . ')" name="active"';
                    if ($active) {
                        $toReturn .= ' checked';
                    }
                    return $toReturn . '></form>';
                })->label(_i('Active'))
            );
        }
        array_push(
            $toReturn,
            Column::callback(['type', 'id'], function ($type, $id) {
                // TODO: Check for the number of observations with this eyepiece
                $observations = 0;
                if (!$observations) {
                    return '<form>
            <button type="button" class="btn btn-sm btn-link" wire:click="$emit(\'delete\', ' . $id . ')">
             <svg width="1.3em" height="1.3em" viewBox="0 0 16 16" class="bi bi-trash icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                 <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                 <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
             </svg>
         </button>
        </form>';
                }
            })
                ->label(_i('Delete'))
        );
        return $toReturn;
    }
}
