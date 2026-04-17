<div class="p-4 w-full bg-gray-800 rounded-lg dsl-search-card">
    <h2 class="text-lg font-semibold text-gray-200 mb-4">{{ __('Advanced object search') }}</h2>

    {{-- Error / info message --}}
    @if($errorMessage)
        <div class="mb-4 p-3 bg-red-900/50 border border-red-600 rounded text-red-200 text-sm">
            {{ $errorMessage }}
        </div>
    @endif

    {{-- ── Active filter rows ─────────────────────────────────────────── --}}
    <div class="space-y-3 mb-4">
        @foreach($activeFilters as $filterType)
            {{-- wire:key ensures Livewire matches existing rows correctly during morphing --}}
            <div wire:key="filter-row-{{ $filterType }}" class="flex items-start gap-3 p-3 bg-gray-700 rounded-lg">
                {{-- Remove button --}}
                <button wire:click="removeFilter('{{ $filterType }}')"
                    type="button"
                    title="{{ __('Remove filter') }}"
                    class="mt-2 text-gray-400 hover:text-red-400 transition flex-shrink-0">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>

                <div class="flex-1 min-w-0">
                    @if($filterType === 'constellations')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Constellations') }}</label>
                        <x-searchable-multiselect
                            wire-model="constellations"
                            :options="$allConstellations"
                            :placeholder="__('Search constellations…')"
                        />

                    @elseif($filterType === 'objectTypes')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Object type') }}</label>
                        <x-searchable-multiselect
                            wire-model="objectTypes"
                            :options="$allObjectTypes"
                            :placeholder="__('Search object types…')"
                        />

                    @elseif($filterType === 'objectCategories')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Object categories') }}</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($allCategories as $key => $label)
                                <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                    <input type="checkbox"
                                        wire:model="objectCategories"
                                        value="{{ $key }}"
                                        class="rounded bg-gray-900 border-gray-600 text-indigo-500 focus:ring-indigo-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>

                    @elseif($filterType === 'catalogsInclude')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Include catalog(s)') }}</label>
                        <x-searchable-multiselect
                            wire-model="catalogsInclude"
                            :options="$allCatalogs"
                            :placeholder="__('Search catalogs…')"
                        />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Only show objects listed in any of these catalogs') }}</p>

                    @elseif($filterType === 'catalogsExclude')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Exclude catalog(s)') }}</label>
                        <x-searchable-multiselect
                            wire-model="catalogsExclude"
                            :options="$allCatalogs"
                            :placeholder="__('Search catalogs…')"
                        />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Exclude objects that appear in any of these catalogs') }}</p>

                    @elseif($filterType === 'magnitude')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Magnitude (min / max)') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.blur="magMin" step="0.1" placeholder="{{ __('Min') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-gray-500">—</span>
                            <input type="number" wire:model.blur="magMax" step="0.1" placeholder="{{ __('Max') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>

                    @elseif($filterType === 'surfaceBrightness')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Surface brightness (min / max)') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.blur="subrMin" step="0.1" placeholder="{{ __('Min') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-gray-500">—</span>
                            <input type="number" wire:model.blur="subrMax" step="0.1" placeholder="{{ __('Max') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>

                    @elseif($filterType === 'diam1')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Diameter 1 (arcmin) — min / max') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.blur="diam1Min" step="0.1" min="0" placeholder="{{ __('Min') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-gray-500">—</span>
                            <input type="number" wire:model.blur="diam1Max" step="0.1" min="0" placeholder="{{ __('Max') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-xs text-gray-500">{{ __('arcmin') }}</span>
                        </div>

                    @elseif($filterType === 'diam2')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Diameter 2 (arcmin) — min / max') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.blur="diam2Min" step="0.1" min="0" placeholder="{{ __('Min') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-gray-500">—</span>
                            <input type="number" wire:model.blur="diam2Max" step="0.1" min="0" placeholder="{{ __('Max') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-xs text-gray-500">{{ __('arcmin') }}</span>
                        </div>

                    @elseif($filterType === 'ratio')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Diam1 / Diam2 ratio — min / max') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.blur="ratioMin" step="0.01" min="0" placeholder="{{ __('Min') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-gray-500">—</span>
                            <input type="number" wire:model.blur="ratioMax" step="0.01" min="0" placeholder="{{ __('Max') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('e.g. 1.0 for perfectly round, 2.0 means Diam1 is twice Diam2') }}</p>

                    @elseif($filterType === 'contrastReserve')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Contrast reserve (min / max)') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.blur="contrastReserveMin" step="0.01" placeholder="{{ __('Min') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-gray-500">—</span>
                            <input type="number" wire:model.blur="contrastReserveMax" step="0.01" placeholder="{{ __('Max') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Requires your default location and telescope, because CR is user-specific.') }}</p>
                        @if(!$isContrastReserveConfigured)
                            <div class="mt-2 p-2 rounded border border-amber-700 bg-amber-950/40 text-amber-200 text-xs">
                                {{ __('Contrast reserve is not available yet. Set your default location and telescope in your profile to use this filter.') }}
                            </div>
                        @endif

                    @elseif($filterType === 'ra')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Right ascension (hours, 0 – 24)') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.blur="raMin" step="0.1" min="0" max="24" placeholder="{{ __('Min') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-gray-500">—</span>
                            <input type="number" wire:model.blur="raMax" step="0.1" min="0" max="24" placeholder="{{ __('Max') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-xs text-gray-500">h</span>
                        </div>

                    @elseif($filterType === 'decl')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Declination (degrees, -90 to +90)') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.blur="declMin" step="0.5" min="-90" max="90" placeholder="{{ __('Min') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-gray-500">—</span>
                            <input type="number" wire:model.blur="declMax" step="0.5" min="-90" max="90" placeholder="{{ __('Max') }}"
                                class="w-28 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <span class="text-xs text-gray-500">°</span>
                        </div>

                    @elseif($filterType === 'observingStatus')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Observing status') }}</label>
                        <select wire:model="observingStatus"
                            class="w-full max-w-xl bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            @foreach($observingStatusOptions as $value => $label)
                                <option value="{{ $value }}">{{ __($label) }}</option>
                            @endforeach
                        </select>

                    @elseif($filterType === 'observingLists')
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Observing lists') }}</label>
                        <div class="flex items-start gap-2">
                            <div class="w-72">
                                <select wire:model="observingListsMode"
                                    class="w-full bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    @foreach($observingListsModeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1 min-w-0">
                                <x-searchable-multiselect
                                    wire-model="observingLists"
                                    :options="$allObservingLists"
                                    :placeholder="__('Search observing lists...')"
                                />
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Includes your own lists and all public lists from legacy data.') }}</p>

                                        @elseif($filterType === 'nameSearch')
                                            <label class="block text-xs text-gray-400 mb-1">{{ __('Object name (catalog + number)') }}</label>
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1">
                                                    <x-searchable-multiselect
                                                        wire-model="nameSearchCatalogs"
                                                        :options="$allCatalogs"
                                                        :placeholder="__('Search catalogs...')"
                                                    />
                                                </div>
                                                <input type="text" wire:model="nameSearchNumber" placeholder="{{ __('Enter number') }}"
                                                    class="flex-1 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">{{ __('Select one or more catalogs. Leave catalogs blank to match number in any catalog (e.g. 1 → M 1, IC 1, NGC 1)') }}</p>

                                        @elseif($filterType === 'nameText')
                                            <label class="block text-xs text-gray-400 mb-1">{{ __('Name contains text') }}</label>
                                            <input type="text" wire:model.blur="nameText" placeholder="{{ __('Enter full or partial object name (e.g. copernicus)') }}"
                                                class="w-full max-w-xl bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <p class="mt-1 text-xs text-gray-500">{{ __('Matches full and partial names across object categories.') }}</p>

                                        @elseif($filterType === 'descriptionText')
                                            <label class="block text-xs text-gray-400 mb-1">{{ __('Description contains text') }}</label>
                                            <div class="flex items-center gap-2">
                                                <input type="text" wire:model.blur="descriptionText" placeholder="{{ __('Enter words to find in object description') }}"
                                                    class="flex-1 max-w-xl bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                <select wire:model="descriptionMode"
                                                    class="w-56 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                    @foreach($descriptionModeOptions as $value => $label)
                                                        <option value="{{ $value }}">{{ __($label) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">{{ __('Searches descriptions of deep-sky objects only. Words are combined with AND or OR mode.') }}</p>

                                        @elseif($filterType === 'atlasPage')
                                            <label class="block text-xs text-gray-400 mb-1">{{ __('Atlas page (deep-sky objects only)') }}</label>
                                            <div class="flex items-center gap-2">
                                                <select wire:model="atlasCode"
                                                    class="flex-1 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                    <option value="">{{ __('Select atlas') }}</option>
                                                    @foreach($allAtlases as $code => $name)
                                                        <option value="{{ $code }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" wire:model.blur="atlasPageSpec" placeholder="{{ __('Pages (e.g. 1, 3, 5-9)') }}"
                                                    class="flex-1 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">{{ __('Examples: 1, 1,3,4, 1-3, 1-3,6-9. Leave empty to include all pages from selected atlas.') }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Add filter dropdown ────────────────────────────────────────── --}}
    @if(!empty($availableFilterTypes))
        <div
            class="mb-4"
            x-data="{
                open: false,
                search: '',
                options: {{ Js::from($availableFilterTypes) }},
                get filtered() {
                    const term = this.search.toLowerCase().trim();
                    return Object.entries(this.options)
                        .filter(([, label]) => !term || label.toLowerCase().includes(term));
                }
            }"
        >
            <button type="button"
                @click="open = !open"
                @keydown.escape="open = false"
                class="inline-flex items-center gap-2 text-sm font-medium px-3 py-1.5 rounded-md bg-gray-700 text-gray-200 hover:bg-gray-600 border border-gray-600 transition focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ __('Add filter') }}
            </button>

            <div x-show="open" x-transition @click.outside="open = false"
                class="mt-1 py-1 w-72 bg-gray-900 border border-gray-700 rounded-lg shadow-lg z-50"
                style="display:none;">
                <div class="px-3 pt-3 pb-2 border-b border-gray-700">
                    <input
                        x-model="search"
                        x-ref="searchInput"
                        @click.stop
                        @keydown.escape.stop="open = false; search = ''"
                        type="text"
                        placeholder="{{ __('Search filters...') }}"
                        class="w-full bg-gray-800 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                </div>

                <div class="max-h-72 overflow-y-auto py-1">
                    <template x-if="filtered.length === 0">
                        <div class="px-4 py-3 text-sm text-gray-500">{{ __('No filters found') }}</div>
                    </template>

                    <template x-for="[type, label] in filtered" :key="type">
                        <button type="button"
                            x-on:click="$wire.addFilter(type); open = false; search = ''"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 transition"
                            x-text="label">
                        </button>
                    </template>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Search / save row ──────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 flex-wrap">
        <button wire:click="search"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md bg-indigo-600 text-white hover:bg-indigo-700 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            {{ __('Search') }}
        </button>

        @auth
            <div class="flex items-center gap-2 ml-auto">
                <input type="text" wire:model="saveName" placeholder="{{ __('Save as…') }}"
                    class="w-44 bg-gray-900 text-gray-100 text-sm border border-gray-600 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <button wire:click="saveSearch"
                    type="button"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium rounded-md bg-gray-700 text-gray-200 hover:bg-gray-600 border border-gray-600 transition focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    {{ __('Save') }}
                </button>
            </div>
        @endauth
    </div>

    {{-- ── Saved searches ─────────────────────────────────────────────── --}}
    @auth
        @if(!empty($savedSearches))
            <div class="mt-5 border-t border-gray-700 pt-4">
                <h3 class="text-sm font-medium text-gray-400 mb-2">{{ __('Saved searches') }}</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($savedSearches as $saved)
                        <div class="inline-flex items-center gap-1 rounded-full bg-gray-700 pl-3 pr-1 py-1 text-sm text-gray-200">
                            <button wire:click="loadSearch({{ $saved['id'] }})"
                                type="button"
                                class="hover:text-white transition {{ $loadedId === $saved['id'] ? 'font-semibold text-indigo-300' : '' }}">
                                {{ $saved['name'] }}
                            </button>
                            <button wire:click="deleteSearch({{ $saved['id'] }})"
                                type="button"
                                title="{{ __('Delete saved search') }}"
                                class="ml-1 p-0.5 text-gray-500 hover:text-red-400 transition rounded-full">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endauth
</div>
