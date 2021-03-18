<?php

namespace App\Http\Livewire;

use App\Models\ObservationList;
use Illuminate\Support\Facades\Auth;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class ObservationListTable extends LivewireDatatable
{
    public $model = ObservationList::class;

    public function builder()
    {
        if (auth()->user()->isAdmin()) {
            return ObservationList::with('user')->select('observation_list.*');
        } else {
            return ObservationList::where(
                'user_id',
                auth()->user()->id
            )->select('observation_list.*');
        }
    }

    public function columns()
    {
        $toReturn = [
            Column::name('name')->callback(['name', 'slug'], function ($name, $slug) {
                return '<a href="/observationList/' . $slug . '">' . $name . '</a>';
            })->label(_i('Name'))->defaultSort('asc')
            ->searchable(),
            Column::name('description')->callback(['description'], function ($description) {
                return '<div class="trix-content">' . $description . '</div>';
            })->label(_i('Description'))
            ->searchable(),
        ];

        if (auth()->user()->isAdmin()) {
            array_push(
                $toReturn,
                Column::name('user.name')->callback(['id', 'user_id'], function ($id, $user_id) {
                    // dd(\App\Models\User::where('id', $user_id)->first());
                    return '<a href="/users/' . \App\Models\User::where('id', $user_id)->first()->slug . '">' . \App\Models\User::where('id', $user_id)->first()->name . '</a>';
                })->label(_i('User name'))
            );
        } else {
            array_push(
                $toReturn,
                BooleanColumn::name('discoverable')->callback(['discoverable', 'id'], function ($discoverable, $id) {
                    $toReturn = '<form>
            <input type="checkbox" wire:click="$emit(\'discoverable\',' . $id . ')" name="discoverable"';
                    if ($discoverable) {
                        $toReturn .= ' checked';
                    }
                    return $toReturn . '></form>';
                })->label(_i('Discoverable'))
            );
            array_push(
                $toReturn,
                BooleanColumn::name('standard')->callback(['slug', 'id', 'user_id'], function ($slug, $id, $user_id) {
                    $toReturn = '<form>
            <input type="radio" wire:click="$emit(\'activate\',' . $id . ')" name="activate"';
                    if ($slug == Auth::user()->activeList) {
                        $toReturn .= ' checked';
                    }
                    return $toReturn . '></form>';
                })->label(_i('Active List'))
            );
        }
        array_push(
            $toReturn,
            Column::callback(['id', 'slug'], function ($id, $slug) {
                return '<form>
            <button type="button" class="btn btn-sm btn-link" wire:click="$emit(\'delete\', ' . $id . ')">
             <svg width="1.3em" height="1.3em" viewBox="0 0 16 16" class="bi bi-trash icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                 <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                 <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
             </svg>
         </button>
        </form>';
            })->label(_i('Delete'))
        );

        // TODO: Make clicking discoverable work
        // TODO: Add number of subscribers
        // TODO: Show number of objects in the list
        // TODO: Show tags
        return $toReturn;
    }
}
