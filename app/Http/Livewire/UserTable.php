<?php

namespace App\Http\Livewire;

use App\Models\User;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class UserTable extends LivewireDatatable
{
    public function builder()
    {
        return User::select('users.*');
    }

    public function columns()
    {
        $toReturn = [
            Column::name('username')->callback(['username', 'slug'], function ($username, $slug) {
                return '<a href="/users/' . $slug . '/edit">' . $username . '</a>';
            })->label(_i('Username'))
            ->searchable('username')->defaultSort('asc'),
            Column::name('name')->callback(['name', 'slug'], function ($name, $slug) {
                return '<a href="/users/' . $slug . '/edit">' . $name . '</a>';
            })->label(_i('Name'))
            ->searchable('name'),
            Column::name('email')->callback(['email'], function ($email) {
                return '<a href="mailto:' . $email . '">' . $email . '</a>';
            })->label(_i('Email'))
            ->searchable('email'),
            DateColumn::name('created_at')->label(_i('Date/Time Added')),
            Column::name('type')->label(_i('User Role')),
            // TODO: Observations
            // TODO: Sessions
            NumberColumn::name('instruments')->callback(['slug'], function ($slug) {
                $user = \App\Models\User::where('slug', $slug)->first();
                return count($user->instruments);
            })->label(_i('Instruments')),
            NumberColumn::name('locations')->callback(['username'], function ($username) {
                $user = \App\Models\User::where('username', $username)->first();
                return count($user->locations);
            })->label(_i('Locations')),
            NumberColumn::name('eyepieces')->callback(['username', 'overviewdsos'], function ($username) {
                $user = \App\Models\User::where('username', $username)->first();
                return count($user->eyepieces);
            })->label(_i('Eyepieces')),
            NumberColumn::name('filters')->callback(['username', 'lookupdsos'], function ($username) {
                $user = \App\Models\User::where('username', $username)->first();
                return count($user->filters);
            })->label(_i('Filters')),
            NumberColumn::name('lenses')->callback(['username', 'overviewstars'], function ($username) {
                $user = \App\Models\User::where('username', $username)->first();
                return count($user->lenses);
            })->label(_i('Lenses')),
            NumberColumn::name('sets')->callback(['username', 'detaildsos'], function ($username) {
                $user = \App\Models\User::where('username', $username)->first();
                return count($user->sets);
            })->label(_i('Equipment Sets')),
            NumberColumn::name('sets')->callback(['username', 'detaildsos', 'overviewstars'], function ($username) {
                $user = \App\Models\User::where('username', $username)->first();
                return count($user->observingLists);
            })->label(_i('Equipment Sets')),
            Column::callback(['type', 'id', 'slug'], function ($type, $id, $slug) {
                $total = 0;
                $user = \App\Models\User::where('slug', $slug)->first();
                $total += count($user->instruments);
                $total += count($user->locations);
                $total += count($user->eyepieces);
                $total += count($user->lenses);
                $total += count($user->filters);
                $total += count($user->sets);
                $total += count($user->observingLists);
                // TODO: Add number of observations
                // TODO: Add number of sessions

                if (!$total) {
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
                ->label(_i('Delete')),
        ];

        return $toReturn;
    }
}
