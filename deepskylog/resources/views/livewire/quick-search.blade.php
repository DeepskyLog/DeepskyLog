<div class="mb-3" x-data="{ open: @entangle('showSuggestions'), selected: @entangle('selectedIndex') }" x-init="() => { if (window.Livewire && window.Livewire.on) { window.Livewire.on('quicksearchSelected', (idx) => { const el = $refs['item-' + idx]; if (el) el.scrollIntoView({block: 'nearest'}); }); } }" x-effect="$nextTick(() => { if (selected !== null && selected >= 0) { const el = $refs['item-' + selected]; if (el) el.scrollIntoView({block: 'nearest'}); } })" x-on:quicksearch-selected.window="(e) => { const idx = e.detail.index; const el = $refs['item-' + idx]; if (el) el.scrollIntoView({block: 'nearest'}); }" @keydown.window="if ($event.key === '/') { const el = $refs.quicksearch; if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') { el.focus(); $event.preventDefault(); } }">
    <label class="block text-xs text-gray-400 mb-1">Quick Search</label>
    <div class="relative">
         <input wire:model.debounce.200ms="query" wire:input="fetchSuggestions"
             x-on:keydown.enter.prevent="if (selected >= 0) { const el = $refs['item-' + selected]; if (el) { const slug = el.dataset.slug; $wire.call('selectSuggestion', slug); } } else { $wire.call('submit'); }"
             x-on:keydown.escape.prevent="$wire.call('closeSuggestions')"
             x-on:keydown.arrow-down.prevent="selected = Math.min(selected + 1, ($refs.list ? $refs.list.children.length - 1 : 0)); $nextTick(() => { const el = $refs['item-' + selected]; if (el) el.scrollIntoView({block: 'nearest'}); }); $wire.set('selectedIndex', selected)"
             x-on:keydown.arrow-up.prevent="selected = Math.max(selected - 1, 0); $nextTick(() => { const el = $refs['item-' + selected]; if (el) el.scrollIntoView({block: 'nearest'}); }); $wire.set('selectedIndex', selected)"
             x-ref="quicksearch" type="text" placeholder="Enter object name" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100 focus:outline-none focus:ring" aria-autocomplete="list" aria-expanded="false" autocomplete="off" aria-activedescendant="{{ $selectedIndex >= 0 ? 'qs-item-' . $selectedIndex : '' }}" />

        @if($showSuggestions)
            <ul x-ref="list" class="absolute z-50 left-0 right-0 mt-1 bg-gray-800 border border-gray-700 rounded max-h-60 overflow-auto text-sm">
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
        @endif
        {{-- Debug: current query (hidden visually) --}}
        <div class="sr-only">{{ $query }}</div>
    </div>
    <div class="mt-2">
        <button wire:click="submit" class="w-full bg-gray-700 text-gray-100 py-2 rounded">Search Object</button>
    </div>
</div>
