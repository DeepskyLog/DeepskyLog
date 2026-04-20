@props([
    'wireModel',
    'options' => [],
    'placeholder' => '',
])
@php $ph = $placeholder ?: __('Search…'); @endphp

{{--
    Searchable multi-select component.
    Uses $wire.entangle() so programmatic Livewire updates (e.g. loadSearch)
    are correctly reflected without needing Livewire to re-morph the DOM.
    wire:ignore prevents Livewire's DOM diffing from fighting Alpine state.
--}}
<div
    wire:ignore
    x-data="{
        search: '',
        open: false,
        options: {{ Js::from($options) }},
        selected: $wire.entangle('{{ $wireModel }}'),
        get filtered() {
            const s = this.search.toLowerCase().trim();
            return Object.entries(this.options)
                .filter(([, label]) => !s || label.toLowerCase().includes(s));
        },
        isSelected(id) {
            return (this.selected ?? []).includes(String(id));
        },
        toggle(id) {
            const s = String(id);
            const arr = [...(this.selected ?? [])];
            const idx = arr.indexOf(s);
            if (idx === -1) arr.push(s); else arr.splice(idx, 1);
            this.selected = arr;
        },
        remove(id) {
            const s = String(id);
            this.selected = (this.selected ?? []).filter(v => v !== s);
        },
        labelFor(id) {
            return this.options[String(id)] ?? String(id);
        }
    }"
    @click.outside="open = false; search = ''"
    class="relative"
>
    {{-- Trigger: shows selected chips + inline search input --}}
    <div
        @click="open = !open"
        class="min-h-[2.5rem] flex flex-wrap gap-1 items-center px-2 py-1.5 bg-gray-900 border border-gray-600 rounded cursor-pointer transition focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500"
    >
        <template x-for="id in (selected ?? [])" :key="id">
            <span class="inline-flex items-center gap-1 bg-indigo-700 text-white text-xs rounded-full px-2 py-0.5 max-w-[16rem]">
                <span class="truncate" x-text="labelFor(id)"></span>
                <button
                    type="button"
                    @click.stop="remove(id)"
                    class="ml-0.5 text-indigo-200 hover:text-white flex-shrink-0"
                >
                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </span>
        </template>
        <input
            x-model="search"
            @click.stop="open = true"
            @focus="open = true"
            @keydown.escape.stop="open = false; search = ''"
            type="text"
            placeholder="{{ $ph }}"
            class="flex-1 min-w-[6rem] bg-transparent text-gray-100 text-sm outline-none placeholder-gray-500 py-0.5"
        >
    </div>

    {{-- Dropdown option list --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute z-50 mt-1 w-full min-w-[14rem] max-h-64 overflow-y-auto bg-gray-900 border border-gray-700 rounded-lg shadow-xl"
        style="display:none;"
    >
        <template x-if="filtered.length === 0">
            <div class="px-3 py-2 text-sm text-gray-500">{{ __('No results') }}</div>
        </template>
        <template x-for="[id, label] in filtered" :key="id">
            <div
                @click.stop="toggle(id)"
                :class="isSelected(id) ? 'bg-indigo-700/30' : 'hover:bg-gray-700'"
                class="flex items-center gap-2.5 px-3 py-1.5 cursor-pointer text-sm text-gray-200 transition select-none"
            >
                <span
                    :class="isSelected(id) ? 'bg-indigo-500 border-indigo-500' : 'border-gray-500'"
                    class="flex-shrink-0 h-4 w-4 rounded border-2 flex items-center justify-center transition"
                >
                    <svg x-show="isSelected(id)" class="h-2.5 w-2.5 text-white" viewBox="0 0 24 24" fill="none">
                        <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span x-text="label" class="truncate"></span>
            </div>
        </template>
    </div>
</div>
