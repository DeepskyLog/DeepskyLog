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
        ];

        return $toReturn;
    }
}
