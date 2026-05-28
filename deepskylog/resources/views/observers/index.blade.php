<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-200 leading-tight">{{ __('Observers') }}</h2>
                <p class="mt-1 text-sm text-gray-400">{{ __('Deep-sky observer statistics with catalog and observing-list progress.') }}</p>
            </div>

            <form
                method="GET"
                action="{{ route('observers.index') }}"
                x-data="{
                    open: false,
                    search: '',
                    observerSearch: @js($observerSearch),
                    observerSearchTimer: null,
                    selected: @js($selectedSource['key']),
                    options: @js(collect($sourceOptions)->map(fn($option) => ['key' => $option['key'], 'label' => $option['label']])->values()),
                    get selectedLabel() {
                        return this.options.find(option => option.key === this.selected)?.label ?? '';
                    },
                    get filteredOptions() {
                        const term = this.search.toLowerCase().trim();
                        return this.options.filter(option => !term || option.label.toLowerCase().includes(term));
                    },
                    choose(option) {
                        this.selected = option.key;
                        this.search = '';
                        this.open = false;
                        this.$refs.source.value = option.key;
                        this.$nextTick(() => this.$refs.submitForm.submit());
                    },
                    submitObserverSearch() {
                        clearTimeout(this.observerSearchTimer);
                        this.observerSearchTimer = setTimeout(() => {
                            this.$refs.submitForm.submit();
                        }, 300);
                    }
                }"
                @click.outside="open = false"
                x-ref="submitForm"
                class="flex flex-col gap-3 md:items-end"
            >
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">
                <input type="hidden" name="source" x-ref="source" :value="selected">

                <div class="grid gap-3 md:grid-cols-[minmax(18rem,24rem)_minmax(14rem,18rem)] md:items-end">
                    <div class="grid gap-2">
                        <label for="observer-source-search" class="text-sm text-gray-300 whitespace-nowrap">{{ __('Catalog or list') }}</label>
                        <div class="relative">
                            <button
                                type="button"
                                @click="open = !open; if (open) { $nextTick(() => $refs.search.focus()); }"
                                class="flex w-full items-center justify-between rounded-md border border-gray-600 bg-gray-800 px-3 py-2 text-left text-sm text-gray-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <span class="truncate" x-text="selectedLabel"></span>
                                <span class="ml-2 text-gray-400">&#9662;</span>
                            </button>

                            <div
                                x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1"
                                class="absolute z-50 mt-2 w-full rounded-lg border border-gray-700 bg-gray-900 shadow-xl"
                                style="display: none;"
                            >
                                <div class="border-b border-gray-800 p-3">
                                    <input
                                        id="observer-source-search"
                                        x-ref="search"
                                        x-model="search"
                                        type="text"
                                        placeholder="{{ __('Search catalog or list...') }}"
                                        class="w-full rounded-md border border-gray-600 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                </div>

                                <div class="max-h-72 overflow-y-auto py-1">
                                    <template x-if="filteredOptions.length === 0">
                                        <div class="px-3 py-2 text-sm text-gray-400">{{ __('No results found.') }}</div>
                                    </template>

                                    <template x-for="option in filteredOptions" :key="option.key">
                                        <button
                                            type="button"
                                            @click="choose(option)"
                                            class="flex w-full items-center px-3 py-2 text-left text-sm text-gray-200 hover:bg-gray-800"
                                            :class="option.key === selected ? 'bg-gray-800 text-white' : ''"
                                        >
                                            <span class="truncate" x-text="option.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <label for="observer-name-search" class="text-sm text-gray-300 whitespace-nowrap">{{ __('Observer name') }}</label>
                        <input
                            id="observer-name-search"
                            name="observer"
                            type="text"
                            x-model="observerSearch"
                            @input="submitObserverSearch()"
                            placeholder="{{ __('Start typing an observer name...') }}"
                            class="w-full rounded-md border border-gray-600 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                </div>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl border border-gray-700 bg-gray-900 shadow-xl">
                <div class="overflow-x-auto">
                    @php
                        $sortableColumns = [
                            'rank' => __('Rank'),
                            'name' => __('Observer'),
                            'observations_count' => __('Observations'),
                            'drawings_count' => __('Drawings'),
                            'observations_last_year' => __('Observations last year'),
                            'drawings_last_year' => __('Drawings last year'),
                            'different_objects_count' => __('Different objects'),
                            'selected_source_count' => $sourceColumnLabel,
                        ];
                        $defaultDirections = [
                            'rank' => 'asc',
                            'name' => 'asc',
                            'observations_count' => 'desc',
                            'drawings_count' => 'desc',
                            'observations_last_year' => 'desc',
                            'drawings_last_year' => 'desc',
                            'different_objects_count' => 'desc',
                            'selected_source_count' => 'desc',
                        ];
                    @endphp
                    <table class="min-w-full divide-y divide-gray-700 text-sm">
                        <thead class="bg-gray-800/90 text-left text-xs uppercase tracking-wider text-gray-300">
                            <tr>
                                @foreach ($sortableColumns as $column => $label)
                                    @php
                                        $isActiveSort = $currentSort === $column;
                                        $nextDirection = $isActiveSort
                                            ? ($currentDirection === 'asc' ? 'desc' : 'asc')
                                            : $defaultDirections[$column];
                                        $sortIndicator = $isActiveSort ? ($currentDirection === 'asc' ? '↑' : '↓') : '↕';
                                        $textAlign = $column === 'name' ? 'text-left' : 'text-right';
                                        $query = array_merge(request()->query(), ['sort' => $column, 'direction' => $nextDirection]);
                                    @endphp
                                    <th class="px-4 py-3 {{ $textAlign }}">
                                        <a
                                            href="{{ route('observers.index', $query) }}"
                                            class="inline-flex items-center gap-1 hover:text-white {{ $textAlign === 'text-right' ? 'justify-end' : '' }}"
                                        >
                                            <span>{{ $label }}</span>
                                            <span class="text-[10px] text-gray-500">{{ $sortIndicator }}</span>
                                        </a>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 bg-gray-950/70 text-gray-100">
                            @forelse ($observerRows as $row)
                                <tr class="hover:bg-gray-900/80">
                                    <td class="px-4 py-3 text-gray-400">{{ $row['rank'] }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('observer.show', ['observer' => $row['slug']]) }}" class="font-medium text-blue-300 hover:text-blue-200 hover:underline">
                                            {{ $row['name'] }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['observations_count']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['drawings_count']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['observations_last_year']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['drawings_last_year']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['different_objects_count']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">
                                        <a
                                            href="{{ route('observer.catalog', ['observer' => $row['slug'], 'source' => $selectedSource['key']]) }}"
                                            class="font-semibold text-lime-400 hover:text-lime-300 hover:underline"
                                        >
                                            {{ number_format($row['selected_source_count']) }}
                                        </a>
                                        <span class="ml-1 text-xs text-gray-400">({{ number_format($row['selected_source_percentage'], 1) }}%)</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-400">{{ __('No observer statistics are available yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($observerRows->hasPages())
                    <div class="border-t border-gray-800 px-4 py-4 sm:px-6">
                        {{ $observerRows->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>