<?php

namespace App\Http\Livewire;

use App\Models\Set;
use App\Models\Lens;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class LensTable extends LivewireDatatable
{
    public $equipment;

    protected $listeners = [
        'updateLivewireDatatable' => 'updateLivewireDatatable',
    ];

    public function updateLivewireDatatable($equipment_set)
    {
        $this->equipment = $equipment_set;
        $this->refreshLivewireDatatable();
    }

    public function builder()
    {
        if (auth()->user()->isAdmin()) {
            return Lens::with('user')->select('lens.*');
        } else {
            // * 0 => all my lenses, -1 => all my active lenses, > 0 => the id of the equipment set
            if ($this->equipment == 0) {
                return Lens::where(
                    'user_id',
                    auth()->user()->id
                )->select('lens.*');
            } elseif ($this->equipment == -1) {
                return Lens::where(
                    'user_id',
                    auth()->user()->id
                )->where('active', 1)->select('lens.*');
            } else {
                return Set::where('id', $this->equipment)->first()->lenses();
            }
        }
    }

    public function columns()
    {
        $toReturn = [
            Column::name('name')->callback(['name', 'id'], function ($name, $id) {
                return '<a href="/lens/' . $id . '">' . $name . '</a>';
            })->label(_i('Name'))->defaultSort('asc')
            ->searchable(),
            Column::callback(['factor', 'id'], function ($factor, $id) {
                $lens = \App\Models\Lens::where('id', $id)->first();
                return $lens->factor . 'x';
            })->label(_i('Factor')), ];

        // NumberColumn::name('observations')
        //     ->label(_i('Observations')),
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
        }
        array_push(
            $toReturn,
            Column::callback(['factor', 'id', 'name'], function ($factor, $id, $name) {
                // TODO: Check for the number of observations with this lens
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
