<?php

namespace App\Http\Livewire;

use App\Models\Set;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class EquipmentSetTable extends LivewireDatatable
{
    public $model = Set::class;

    public function builder()
    {
        if (auth()->user()->isAdmin()) {
            return Set::with('user')->select('set.*');
        } else {
            return Set::where(
                'user_id',
                auth()->user()->id
            )->select('set.*');
        }
    }

    public function columns()
    {
        $toReturn = [
            Column::name('name')->callback(['name', 'id'], function ($name, $id) {
                return '<a href="/set/' . $id . '">' . $name . '</a>';
            })->label(_i('Name'))
            ->searchable(),
            Column::name('description')->callback(['description'], function ($description) {
                return '<div class="trix-content">' . $description . '</div>';
            })->label(_i('Description'))
            ->searchable(),
            Column::name('instruments')->callback(['description', 'id', 'name'], function ($description, $id, $name) {
                $set = Set::where('id', $id)->first()->instruments()->get();
                if ($set->count() == 0) {
                    return '0';
                } else {
                    $toReturn = '<div class="trix-content"><ul>';
                    foreach ($set as $instrument) {
                        $toReturn .= '<li><a href="/instrument/' . $instrument->id . '">' . $instrument->name . '</a></li>';
                    }
                    $toReturn .= '</ul></div>';
                    return $toReturn;
                }
            })->label(_i('Instruments')),
        ];

        if (auth()->user()->isAdmin()) {
            array_push(
                $toReturn,
                Column::name('user.name')->callback(['user.name', 'user.slug'], function ($user_name, $user_slug) {
                    return '<a href="/users/' . $user_slug . '">' . $user_name . '</a>';
                })->label(_i('User name'))->searchable('user.name')
            );
        }

        array_push(
            $toReturn,
            Column::callback(['description', 'id'], function ($description, $id) {
                return '<form>
            <button type="button" class="btn btn-sm btn-link" wire:click="$emit(\'delete\', ' . $id . ')">
             <svg width="1.3em" height="1.3em" viewBox="0 0 16 16" class="bi bi-trash icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                 <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                 <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
             </svg>
         </button>
        </form>';
            })
                ->label(_i('Delete'))
        );

        return $toReturn;
    }
}
