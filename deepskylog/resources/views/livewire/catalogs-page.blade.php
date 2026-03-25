<div class="p-6 max-w-6xl">
    <h1 class="text-2xl font-semibold text-gray-200">{{ __('Catalogs') }}</h1>
    <p class="mt-1 text-sm text-gray-400">{{ __('Select a catalog to view object counts by constellation, types and objects list.') }}</p>

    <div class="mt-6">
        <div x-data="{ open: @entangle('open'), active: -1, query: @entangle('search') }" @keydown.escape="open=false" class="relative">
            <label class="block text-sm font-medium text-gray-400 mb-1">{{ __('Catalog') }}</label>
            <input
                x-ref="input"
                x-on:focus="open=true"
                x-on:click="open=true"
                x-on:input="open = true; active = -1"
                x-on:keydown.arrow-down.prevent="if($refs.list && $refs.list.children.length) active = Math.min(active + 1, $refs.list.children.length - 1)"
                x-on:keydown.arrow-up.prevent="if($refs.list && $refs.list.children.length) active = Math.max(active - 1, 0)"
                x-on:keydown.enter.prevent="if(active >= 0 && $refs.list) $wire.selectCatalog($refs.list.children[active].dataset.value); open=false"
                wire:model.debounce.250ms="search"
                type="text"
                placeholder="e.g. NGC, IC, M"
                class="block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <div x-show="open" x-cloak @click.away="open = false" class="absolute z-20 mt-1 w-full bg-gray-900 border border-gray-700 rounded max-h-64 overflow-auto">
                <ul x-ref="list" role="listbox" class="py-1">
                    <div wire:loading wire:target="search" class="px-3 py-2 text-sm text-gray-400">{{ __('Searching...') }}</div>

                    @if($catalogs->isEmpty())
                        <li class="px-3 py-2 text-sm text-gray-400">{{ __('No catalogs found') }}</li>
                    @else
                        @foreach($catalogs as $i => $cat)
                            <li
                                role="option"
                                aria-selected="false"
                                data-value="{{ $cat }}"
                                @click.prevent="$wire.selectCatalog('{{ $cat }}'); open=false"
                                @mouseenter="active = {{ $i }}"
                                x-show="query === '' || ($el.dataset.value && $el.dataset.value.toLowerCase().includes(query.toLowerCase()))"
                                x-bind:aria-hidden="!(query === '' || ($el.dataset.value && $el.dataset.value.toLowerCase().includes(query.toLowerCase())))"
                                class="px-3 py-2 cursor-pointer text-sm hover:bg-gray-800 hover:text-white"
                                :class="{'bg-gray-800 text-white': active === {{ $i }}}"
                            >
                                {{ $cat }}
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>

            <div class="mt-3 flex gap-3">
                <button @click.prevent="$wire.selectCatalog($refs.input.value); open=false" class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-600">{{ __('Show') }}</button>
                <button wire:click.prevent="clearSelection" class="inline-flex items-center bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-500">{{ __('Reset') }}</button>
            </div>
        </div>
    </div>

    @if($selected)
        <div class="mt-8 space-y-6">
            <!-- Objects table: full-width at the top -->
            <div class="bg-gray-900 rounded border border-gray-700 p-4">
                <h2 class="font-semibold text-gray-200 mb-2">{{ __('Objects in') }} {{ $selected }}</h2>
                @if(!$objects || $objects->isEmpty())
                    <div class="text-sm text-gray-400">{{ __('No objects to show.') }}</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-300">
                            <thead>
                            <tr>
                                <th class="pb-2">{{ __('Name') }}</th>
                                <th class="pb-2">{{ __('Type') }}</th>
                                <th class="pb-2">{{ __('Constellation') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($objects as $obj)
                                <tr class="border-t border-gray-800">
                                    <td class="py-2">
                                        <a href="{{ url('/object/' . $obj->slug) }}" class="text-blue-400 hover:underline">{{ $displayNames[$obj->slug] ?? $obj->name }}</a>
                                    </td>
                                    <td class="py-2">{{ $obj->type_name ?? '' }}</td>
                                    <td class="py-2">{{ $obj->constellation ?? '' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $objects->links() }}</div>
                @endif
            </div>

            <!-- Below: constellations and types side-by-side on medium+, stacked on small -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-900 rounded border border-gray-700 p-4">
                    <h2 class="font-semibold text-gray-200 mb-2">{{ __('Objects per constellation') }}</h2>
                    @if($constellationCounts->isEmpty())
                        <div class="text-sm text-gray-400">{{ __('No objects found in this catalog.') }}</div>
                    @else
                        <table class="w-full text-sm text-left text-gray-300">
                            <thead>
                            <tr>
                                <th class="pb-2">{{ __('Constellation') }}</th>
                                <th class="pb-2">{{ __('Count') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($constellationCounts as $row)
                                <tr class="border-t border-gray-800">
                                    <td class="py-2">{{ $row->constellation }}</td>
                                    <td class="py-2">{{ $row->total }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <div class="bg-gray-900 rounded border border-gray-700 p-4">
                    <h2 class="font-semibold text-gray-200 mb-2">{{ __('Types') }}</h2>
                    @if($typeCounts->isEmpty())
                        <div class="text-sm text-gray-400">{{ __('No type information available.') }}</div>
                    @else
                        <table class="w-full text-sm text-left text-gray-300">
                            <thead>
                            <tr>
                                <th class="pb-2">{{ __('Type') }}</th>
                                <th class="pb-2">{{ __('Count') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($typeCounts as $row)
                                <tr class="border-t border-gray-800">
                                    <td class="py-2">{{ $row->type_name ?? __('Unknown') }}</td>
                                    <td class="py-2">{{ $row->total }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
