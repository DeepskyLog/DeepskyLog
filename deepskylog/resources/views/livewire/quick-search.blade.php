<div class="mb-3" x-on:click.outside="if (open) $wire.call('closeSuggestions')" x-data="{ open: @entangle('showSuggestions'), selected: @entangle('selectedIndex') }" x-init="() => { if (window.Livewire && window.Livewire.on) { window.Livewire.on('quicksearchSelected', (idx) => { const el = $refs['item-' + idx]; if (el) el.scrollIntoView({block: 'nearest'}); }); } }" x-effect="$nextTick(() => { if (selected !== null && selected >= 0) { const el = $refs['item-' + selected]; if (el) el.scrollIntoView({block: 'nearest'}); } })" x-on:quicksearch-selected.window="(e) => { const idx = e.detail.index; const el = $refs['item-' + idx]; if (el) el.scrollIntoView({block: 'nearest'}); }" @keydown.window="if ($event.key === '/') { const el = $refs.quicksearch; if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') { el.focus(); $event.preventDefault(); } }">
    <label class="block text-xs text-gray-400 mb-1">Quick Search</label>
    <div class="relative">
         <input wire:model.debounce.200ms="query" wire:input="fetchSuggestions"
             x-on:keydown.enter.prevent="if (selected >= 0) { const el = $refs['item-' + selected]; if (el) { const slug = el.dataset.slug; $wire.call('selectSuggestion', slug); } } else { $wire.call('submit'); }"
             x-on:keydown.escape.prevent="$wire.call('closeSuggestions')"
             x-on:keydown.tab="if (open && !{{ $observationActions ? 'true' : 'false' }}) $wire.call('closeSuggestions')"
             x-on:keydown.arrow-down.prevent="selected = Math.min(selected + 1, ($refs.list ? $refs.list.children.length - 1 : 0)); $nextTick(() => { const el = $refs['item-' + selected]; if (el) el.scrollIntoView({block: 'nearest'}); }); $wire.set('selectedIndex', selected)"
             x-on:keydown.arrow-up.prevent="selected = Math.max(selected - 1, 0); $nextTick(() => { const el = $refs['item-' + selected]; if (el) el.scrollIntoView({block: 'nearest'}); }); $wire.set('selectedIndex', selected)"
             x-ref="quicksearch" type="text" placeholder="Enter object name" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100 focus:outline-none focus:ring" aria-autocomplete="list" aria-expanded="false" autocomplete="off" aria-activedescendant="{{ $selectedIndex >= 0 ? 'qs-item-' . $selectedIndex : '' }}" />

        @if($showSuggestions)
            <div class="absolute z-50 left-0 right-0 mt-1 bg-gray-800 border border-gray-700 rounded text-sm">
            <ul x-ref="list" class="max-h-60 overflow-auto">
                @foreach($suggestions as $s)
                    @php $idx = $loop->index; $isSelected = ($selectedIndex === $idx); @endphp
                    <li id="qs-item-{{ $idx }}" x-ref="item-{{ $idx }}" data-slug="{{ $s['slug'] }}" class="px-3 py-2 cursor-pointer {{ $isSelected ? 'bg-gray-700' : 'hover:bg-gray-700' }}"
                        x-on:click="$wire.call('selectSuggestion', '{{ $s['slug'] }}')"
                        x-on:mouseenter="selected = {{ $idx }}; $wire.set('selectedIndex', selected)">
                        <div class="font-medium text-gray-100">{{ $s['title'] }}</div>
                        <div class="text-xs text-gray-400">{{ ucfirst($s['type']) }}</div>
                    </li>
                @endforeach
            </ul>
            @if($observationActions)
                {{-- The list covers the buttons below the input, so offer them here too. --}}
                <div class="flex flex-col gap-1 border-t border-gray-700 p-2">
                    <button type="button" wire:click="searchObservations" class="w-full rounded bg-gray-700 px-3 py-1.5 text-left text-gray-100 hover:bg-gray-600">{{ __('Search observations') }}</button>
                    @auth
                        <button type="button" wire:click="newObservation" class="w-full rounded bg-gray-700 px-3 py-1.5 text-left text-gray-100 hover:bg-gray-600">{{ __('New observation') }}</button>
                    @endauth
                </div>
            @endif
            </div>
        @endif
        {{-- Debug: current query (hidden visually) --}}
        <div class="sr-only">{{ $query }}</div>
    </div>
    <div class="mt-2">
        @php $btnLabel = ($mode === 'add_observation') ? __('Add observation') : __('Search Object'); @endphp
        @if(!empty($redirectToAdvancedWhenEmpty))
            <button type="button" class="w-full bg-gray-700 text-gray-100 py-2 rounded" x-on:click="if (!$refs.quicksearch || ($refs.quicksearch && $refs.quicksearch.value.trim() === '')) { window.location.href='{{ route('search.advanced') }}'; } else { $wire.call('submit'); }">{{ $btnLabel }}</button>
        @else
            <button wire:click="submit" class="w-full bg-gray-700 text-gray-100 py-2 rounded">{{ $btnLabel }}</button>
        @endif
    </div>
    @if($observationActions)
        <div class="mt-2">
            <button type="button" wire:click="searchObservations" class="block w-full text-center bg-gray-700 hover:bg-gray-600 text-white px-3 py-2 rounded text-sm">
                {{ __('Search observations') }}
            </button>
        </div>
        @auth
            <div class="mt-2">
                <button type="button" wire:click="newObservation" class="block w-full text-center bg-gray-700 hover:bg-gray-600 text-white px-3 py-2 rounded text-sm">
                    {{ __('New observation') }}
                </button>
            </div>
        @endauth
    @endif
</div>
