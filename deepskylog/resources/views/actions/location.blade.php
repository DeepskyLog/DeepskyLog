@if (auth()->user()->stdlocation !== $row->id)
    <a href="#" wire:click.prevent="clickToMakeDefault({{ $row->id }})">
        <div class="bg-blue-500 cursor-pointer text-gray-300 px-3 py-2 m-1 rounded-sm text-sm">
            {{__('Make default')}}
        </div>
    </a>
@endif

@if ($row->observations > 0)
    <div class="bg-gray-500 cursor-not-allowed text-gray-300 px-3 py-2 m-1 rounded-sm text-sm">
        {{__('Delete')}}
    </div>
@else
    <div class="bg-red-500 cursor-pointer text-white px-3 py-2 m-1 rounded-sm text-sm">
        <a href="#" wire:click.prevent="clickToDelete({{ $row->id }})">
            {{__('Delete') }}
        </a>
    </div>
@endif
