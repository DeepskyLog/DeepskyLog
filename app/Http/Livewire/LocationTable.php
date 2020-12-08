<?php

namespace App\Http\Livewire;

use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use deepskylog\LaravelGettext\Facades\LaravelGettext;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class LocationTable extends LivewireDatatable
{
    public function builder()
    {
        if (auth()->user()->isAdmin()) {
            return Location::with('user')->select('locations.*');
        } else {
            return Location::where(
                'user_id',
                auth()->user()->id
            )->select('locations.*');
        }
    }

    public function columns()
    {
        $toReturn = [
            Column::name('name')->callback(['name', 'id'], function ($name, $id) {
                return '<a href="/location/' . $id . '">' . $name . '</a>';
            })->label(_i('Name'))->defaultSort('asc')
            ->searchable(), ];

        if (!auth()->user()->isAdmin()) {
            array_push(
                $toReturn,
                Column::name(
                    'weather'
                )->callback(
                    ['name', 'id', 'longitude'],
                    function ($name, $id, $longitude) {
                        $location = \App\Models\Location::where('id', $id)->first();
                        return '<a href="http://clearoutside.com/forecast/'
                        . round($location->latitude, 2) . '/'
                        . round($location->longitude, 2) . '">
                        <img src="http://clearoutside.com/forecast_image_small/'
                        . round($location->latitude, 2) . '/'
                        . round($location->longitude, 2) . '/forecast.png" />
                        </a>';
                    }
                )->label(_i('Weather forecast'))
            );
        }

        array_push(
            $toReturn,
            Column::name('country')->callback(['country'], function ($country) {
                return \Countries::getOne($country, LaravelGettext::getLocaleLanguage());
            })->label(_i('Country'))
        );

        array_push(
            $toReturn,
            NumberColumn::name('elevation')->callback(['elevation'], function ($elevation) {
                if (Auth::user()->showInches) {
                    return round($elevation * 3.28084) . ' ft';
                } else {
                    return $elevation . ' m';
                }
            })->label(_i('Elevation'))
        );

        array_push(
            $toReturn,
            NumberColumn::name('limitingMagnitude')->callback(['limitingMagnitude'], function ($limitingMagnitude) {
                if ($limitingMagnitude) {
                    return $limitingMagnitude - Auth::user()->fstOffset;
                } else {
                    return '';
                }
            })->label(_i('NELM'))
        );
        array_push(
            $toReturn,
            NumberColumn::name('skyBackground')->label(_i('SQM'))
        );
        array_push(
            $toReturn,
            NumberColumn::name('bortle')->label(_i('Bortle'))
        );

        // // NumberColumn::name('observations')
        // //     ->label(_i('Observations')),

        if (auth()->user()->isAdmin()) {
            array_push(
                $toReturn,
                Column::name('user.name')->callback(['user_id', 'user.name', 'user.slug'], function ($user_id, $user_name, $user_slug) {
                    return '<a href="/users/' . $user_slug . '">' . $user_name . '</a>';
                })->label(_i('User name'))->searchable()
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

            array_push(
                $toReturn,
                BooleanColumn::name('standard')->callback(['active', 'id', 'bortle'], function ($active, $id, $bortle) {
                    $location = Location::where('id', $id)->first();
                    $toReturn = '<form>
            <input type="radio" wire:click="$emit(\'standard\',' . $id . ')" name="standard"';
                    if ($id == Auth::user()->stdlocation) {
                        $toReturn .= ' checked';
                    }
                    if (!$location->active) {
                        $toReturn .= ' disabled';
                    }
                    return $toReturn . '></form>';
                })->label(_i('Default Location'))
            );
        }
        array_push(
            $toReturn,
            Column::callback(['bortle', 'id', 'skyBackground'], function ($bortle, $id, $skyBackground) {
                // TODO: Check for the number of observations with this insturment
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
            })->label(_i('Delete'))
        );

        return $toReturn;
    }
}
