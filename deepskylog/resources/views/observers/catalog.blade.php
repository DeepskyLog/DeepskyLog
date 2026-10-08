@php
    $drawnMode = $mode === 'drawn';
    $totalCount = $sourceObjects->count();
    $percentage = number_format($totalCount > 0 ? ($observedCount / $totalCount) * 100 : 0, 1);
    $openCount = $totalCount - $observedCount;
    $observerKey = $user->slug ?: $user->username;
    $modeUrl = fn(string $newMode) => route('observer.catalog', array_filter([
        'observer' => $observerKey,
        'source' => $selectedSource['key'],
        'include_subobjects' => $includeSubobjects ? 1 : null,
        'mode' => $newMode === 'drawn' ? 'drawn' : null,
    ]));
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-200 leading-tight">{{ $user->name }}</h2>
                <p class="mt-1 text-sm text-gray-400">
                    @if ($drawnMode)
                        {{ __('Drawn :source objects: :observed / :total (:percentage%)', ['source' => $selectedSource['short_label'], 'observed' => $observedCount, 'total' => $totalCount, 'percentage' => $percentage]) }}
                    @else
                        {{ __('Observed :source objects: :observed / :total (:percentage%)', ['source' => $selectedSource['short_label'], 'observed' => $observedCount, 'total' => $totalCount, 'percentage' => $percentage]) }}
                    @endif
                </p>
            </div>

            <form
                method="GET"
                action="{{ route('observer.catalog', ['observer' => $user->slug ?: $user->username]) }}"
                x-data="{
                    open: false,
                    search: '',
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
                    }
                }"
                @click.outside="open = false"
                x-ref="submitForm"
                class="flex flex-col gap-3 sm:flex-row sm:items-center"
            >
                <input type="hidden" name="source" x-ref="source" :value="selected">
                @if ($drawnMode)
                    <input type="hidden" name="mode" value="drawn">
                @endif

                <div class="flex items-center gap-3">
                    <label for="observer-catalog-source-search" class="text-sm text-gray-300 whitespace-nowrap">{{ __('Catalog or list') }}</label>
                    <div class="relative min-w-56">
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
                                    id="observer-catalog-source-search"
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

                <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                    <input
                        type="checkbox"
                        name="include_subobjects"
                        value="1"
                        onchange="this.form.submit()"
                        @checked($includeSubobjects)
                        class="rounded border-gray-600 bg-gray-800 text-green-500 focus:ring-green-500"
                    >
                    {{ __('Show with subobjects') }}
                </label>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto w-full max-w-[140rem] space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-gray-700 bg-gray-900 shadow-xl">
                <div class="border-b border-gray-800 px-6 py-6">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <img
                            src="{{ $user->profile_photo_url }}"
                            alt="{{ $user->name }}"
                            class="h-28 w-28 rounded-xl object-cover ring-1 ring-gray-700"
                        >
                        <div class="flex-1 space-y-4">
                            <div class="space-y-2">
                                <div class="text-lg font-semibold text-white">{{ $user->name }}</div>
                                <div class="text-sm text-gray-400">{{ __('Observer progress for :source', ['source' => $selectedSource['short_label']]) }}</div>
                                <div class="text-sm text-gray-300">
                                    @if ($drawnMode)
                                        {{ __('Drawn objects: :observed / :total (:percentage%)', ['observed' => $observedCount, 'total' => $totalCount, 'percentage' => $percentage]) }}
                                    @else
                                        {{ __('Observed objects: :observed / :total (:percentage%)', ['observed' => $observedCount, 'total' => $totalCount, 'percentage' => $percentage]) }}
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <div class="inline-flex overflow-hidden rounded-md border border-gray-600 text-sm" role="group" aria-label="{{ __('Show the status of') }}">
                                    <a
                                        href="{{ $modeUrl('observed') }}"
                                        @if (!$drawnMode) aria-current="page" @endif
                                        class="px-3 py-1.5 {{ $drawnMode ? 'bg-gray-800 text-gray-300 hover:bg-gray-700' : 'bg-blue-600 text-white' }}"
                                    >{{ __('Observations') }}</a>
                                    <a
                                        href="{{ $modeUrl('drawn') }}"
                                        @if ($drawnMode) aria-current="page" @endif
                                        class="border-l border-gray-600 px-3 py-1.5 {{ $drawnMode ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}"
                                    >{{ __('Drawings') }}</a>
                                </div>

                                @auth
                                    @if ($openCount > 0)
                                        @can('create', \App\Models\ObservingList::class)
                                            <form method="POST" action="{{ route('observer.catalog.list', ['observer' => $observerKey]) }}">
                                                @csrf
                                                <input type="hidden" name="source" value="{{ $selectedSource['key'] }}">
                                                @if ($includeSubobjects)
                                                    <input type="hidden" name="include_subobjects" value="1">
                                                @endif
                                                @if ($drawnMode)
                                                    <input type="hidden" name="mode" value="drawn">
                                                @endif
                                                <button type="submit" class="rounded-md border border-emerald-500/60 bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                                    {{ $drawnMode ? __('Create observing list with the :count objects not drawn yet', ['count' => $openCount]) : __('Create observing list with the :count objects not seen yet', ['count' => $openCount]) }}
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                @endauth
                            </div>

                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                <span class="rounded-full border border-emerald-400/40 bg-emerald-500/15 px-3 py-1.5 font-medium text-emerald-200">
                                    {{ $drawnMode ? __('Drawn') : __('Observed') }}
                                </span>
                                <span class="rounded-full border border-rose-400/40 bg-rose-500/15 px-3 py-1.5 font-medium text-rose-200">
                                    {{ $drawnMode ? __('Not drawn yet') : __('Not observed yet') }}
                                </span>
                                <span class="text-gray-400">{{ __('Green tiles open this observer\'s observations. Red tiles open the object page.') }}</span>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-700 bg-gray-950/70 px-5 py-4 xl:min-w-80">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">{{ $drawnMode ? __('Drawn :source objects', ['source' => $selectedSource['short_label']]) : __('Observed :source objects', ['source' => $selectedSource['short_label']]) }}</div>
                            <div class="mt-2 text-2xl font-semibold text-white">{{ $observedCount }} / {{ $totalCount }}</div>
                            <div class="mt-1 text-sm text-gray-400">{{ __('Completion: :percentage%', ['percentage' => $percentage]) }}</div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto px-4 py-6 sm:px-6">
                    @php
                        $rows = $sourceObjects->chunk(10);
                    @endphp

                    @forelse ($rows as $row)
                        @if ($loop->first)
                            <div class="overflow-hidden rounded-2xl border border-gray-700/80 bg-gray-950/70 shadow-[0_20px_45px_rgba(3,7,18,0.35)]">
                                <div class="grid min-w-[70rem] grid-cols-[6.5rem_repeat(10,minmax(6rem,1fr))] gap-[2px] bg-gray-800/90 p-[2px]">
                                    <div class="flex items-center justify-center rounded-lg bg-gray-950 px-2 py-3 text-center text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-gray-500"></div>
                                    @for ($column = 1; $column <= 10; $column++)
                                        <div class="flex items-center justify-center rounded-lg bg-gray-950 px-2 py-3 text-center text-sm font-semibold text-gray-200">{{ $column }}</div>
                                    @endfor
                        @endif

                                <div class="flex items-center justify-center rounded-lg bg-slate-200 px-2 py-4 text-center text-sm font-semibold text-slate-800 shadow-inner shadow-white/40">
                                    {{ $row->first()['position'] }}-{{ $row->last()['position'] }}
                                </div>

                                @foreach ($row as $object)
                                    <a
                                        href="{{ $object['target_url'] }}"
                                        class="group relative flex min-h-20 flex-col items-center justify-center rounded-lg border px-2 py-4 text-center text-sm font-semibold transition duration-150 hover:-translate-y-0.5 hover:shadow-lg {{ $object['observed'] ? 'border-emerald-300/70 bg-emerald-500 text-emerald-950 shadow-[inset_0_1px_0_rgba(255,255,255,0.2)] hover:bg-emerald-400' : 'border-rose-300/60 bg-rose-700 text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.12)] hover:bg-rose-600' }}"
                                        title="{{ $object['display_name'] }}"
                                    >
                                        <span class="text-base font-bold tracking-tight">{{ $object['display_name'] }}</span>
                                        <span class="mt-1 text-[0.65rem] font-semibold uppercase tracking-[0.18em] {{ $object['observed'] ? 'text-emerald-950/75' : 'text-white/80' }}">
                                            {{ $object['observed'] ? ($drawnMode ? __('Drawn') : __('Seen')) : __('Open object') }}
                                        </span>
                                    </a>
                                @endforeach

                                @for ($pad = $row->count(); $pad < 10; $pad++)
                                    <div class="rounded-lg bg-gray-900/80 px-2 py-4"></div>
                                @endfor

                        @if ($loop->last)
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-700 px-6 py-12 text-center text-sm text-gray-400">
                            {{ __('This catalog or list has no objects yet.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>