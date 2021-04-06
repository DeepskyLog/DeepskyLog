<div>
    <div>
        @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
        @endif
    </div>

    <h4>
        <!-- We have to check if admin is part of request, because we can not
                add a variable to the view, because we are using YarJa dataTables. -->
        @php if (strpos(Request::url(), 'admin') !== false) {
        echo _i("All filters");
        } else {
        echo _i("Filters of %s", Auth::user()->name);
        }
        @endphp
    </h4>
    <hr />
    <a class="btn btn-success float-right" href="/filter/create">
        {{ _i("Add filter") }}
    </a>
    <br /><br />
    {{ _i('Show equipment') }}
    @php
    $equipment = '<option value="0">' . _i('All my equipment') . '</option>';
    $equipment .= '<option value="-1">' . _i('All my active equipment') . '</option>';
    foreach(\App\Models\User::where('id', Auth::id())->first()->sets()->get() as $set) {
    $equipment .= '<option value="' . $set->id . '">' . _i('Equipment set') . ': ' . $set->name . '</option>';
    }
    @endphp
    <div x-data='' wire:ignore>
        <x-input.select-live-wire wire:model='equipment' prettyname='myequipment' :options='$equipment'
            selected="('equipment')" />
    </div>
    <br />

    @if (strpos(Request::url(), 'admin') === false)
    <livewire:filter-table hideable="select" exportable />
    @else
    <livewire:filter-table hideable="select" hide="active" exportable />
    @endif
</div>
