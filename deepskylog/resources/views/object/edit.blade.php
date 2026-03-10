<x-app-layout>
    <div>
        <div>
            <div class="max-w-screen-xl mx-auto bg-gray-900 px-6 py-10 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-3xl font-bold text-white">
                        {{ __('Edit Object') }}: {{ $object->name }}
                    </h2>
                    <a href="{{ route('object.show', ['slug' => $object->slug ?? $object->name]) }}"
                       class="text-blue-400 hover:text-blue-300">
                        {{ __('Back to Object') }}
                    </a>
                </div>

                @if (session('success'))
                    <div class="mb-4 rounded-md bg-green-900 p-4">
                        <p class="text-sm text-green-300">{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-md bg-red-900 p-4">
                        <p class="text-sm text-red-300">{{ session('error') }}</p>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-4 rounded-md bg-yellow-900 p-4">
                        <p class="text-sm text-yellow-300">{{ session('warning') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-900 p-4">
                        <ul class="list-disc list-inside text-sm text-red-300">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-4">
                    <x-card>
                        <form role="form" action="{{ route('object.update', ['slug' => $object->slug ?? $object->name]) }}"
                              method="POST">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Basic Information --}}
                                <div class="space-y-4">
                                    <h3 class="text-xl font-semibold text-white mb-4">{{ __('Basic Information') }}</h3>

                                    <x-input name="name" label="{{ __('Object Name') }} *" type="text"
                                             class="mt-1 block w-full" value="{{ old('name', $object->name) }}"
                                             id="name" required />

                                    <x-textarea name="alternative_names" label="{{ __('Alternative Names') }}"
                                                class="mt-1 block w-full"
                                                placeholder="{{ __('Enter alternative names separated by commas') }}"
                                                id="alternative_names"
                                                rows="3">{{ old('alternative_names', $alternativeNames->implode(', ')) }}</x-textarea>
                                    <p class="text-sm text-gray-400">{{ __('Separate multiple names with commas') }}</p>

                                    <div>
                                        <label for="type" class="block text-sm font-medium text-gray-300 mb-1">{{ __('Object Type') }} <span class="text-red-500">*</span></label>
                                        <select name="type" id="type" required
                                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-800 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 px-4 py-2 transition-all duration-200 hover:border-gray-500">
                                            <option value="">{{ __('Select Type') }}</option>
                                            @foreach ($types as $type)
                                                <option value="{{ $type->code }}"
                                                    {{ old('type', $object->type) == $type->code ? 'selected' : '' }}>
                                                    {{ $type->code }} - {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="con" class="block text-sm font-medium text-gray-300 mb-1">
                                            {{ __('Constellation') }}
                                            <span class="text-xs text-gray-500 ml-2">({{ __('Auto-calculated from coordinates') }})</span>
                                        </label>
                                        <select name="con" id="con" disabled
                                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-700 text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 px-4 py-2 transition-all duration-200 cursor-not-allowed">
                                            <option value="">{{ __('Select Constellation') }}</option>
                                            @foreach ($constellations as $constellation)
                                                <option value="{{ $constellation->id }}"
                                                    {{ old('con', $object->con) == $constellation->id ? 'selected' : '' }}>
                                                    {{ $constellation->id }} - {{ $constellation->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="con" id="con_hidden" value="{{ old('con', $object->con) }}" />
                                    </div>

                                    <x-textarea name="description" label="{{ __('Description') }}"
                                                class="mt-1 block w-full" id="description" rows="5"
                                                maxlength="1024">{{ old('description', $object->description) }}</x-textarea>

                                    <x-input name="datasource" label="{{ __('Data Source') }}" type="text"
                                             class="mt-1 block w-full" value="{{ old('datasource', $object->datasource) }}"
                                             id="datasource" maxlength="50" />

                                    <div x-data="objectSearch(@js($partOfObjects), 'part_of')" class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-300">{{ __('Part Of (Parent Objects)') }}</label>
                                        
                                        {{-- Selected objects display --}}
                                        <div x-show="selected.length > 0" class="flex flex-wrap gap-2 p-2 bg-gray-800 rounded-lg border border-gray-600">
                                            <template x-for="(item, index) in selected" :key="index">
                                                <div class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white rounded-md text-sm">
                                                    <span x-text="item"></span>
                                                    <button type="button" @click="removeItem(index)" class="hover:text-red-300">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                    <input type="hidden" :name="'part_of[' + index + ']'" :value="item" />
                                                </div>
                                            </template>
                                        </div>
                                        
                                        {{-- Search input --}}
                                        <div class="relative">
                                            <input type="text" x-model="search" @input="searchObjects" @focus="showDropdown = true"
                                                   placeholder="{{ __('Search for objects...') }}"
                                                   class="w-full rounded-lg border-gray-600 bg-gray-800 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 px-4 py-2" />
                                            
                                            {{-- Dropdown results --}}
                                            <div x-show="showDropdown && results.length > 0" @click.outside="showDropdown = false"
                                                 class="absolute z-10 mt-1 w-full bg-gray-800 border border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto">
                                                <template x-for="result in results" :key="result.value">
                                                    <button type="button" @click="addItem(result.value); search = ''; results = []"
                                                            class="w-full text-left px-4 py-2 hover:bg-gray-700 text-white text-sm border-b border-gray-700 last:border-0"
                                                            x-text="result.label"></button>
                                                </template>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-400 mt-1">{{ __('Objects this object is part of (e.g., M31 for NGC 221)') }}</p>
                                    </div>

                                    <div x-data="objectSearch(@js($containsObjects), 'contains')" class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-300">{{ __('Contains (Child Objects)') }}</label>
                                        
                                        {{-- Selected objects display --}}
                                        <div x-show="selected.length > 0" class="flex flex-wrap gap-2 p-2 bg-gray-800 rounded-lg border border-gray-600">
                                            <template x-for="(item, index) in selected" :key="index">
                                                <div class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white rounded-md text-sm">
                                                    <span x-text="item"></span>
                                                    <button type="button" @click="removeItem(index)" class="hover:text-red-300">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                    <input type="hidden" :name="'contains[' + index + ']'" :value="item" />
                                                </div>
                                            </template>
                                        </div>
                                        
                                        {{-- Search input --}}
                                        <div class="relative">
                                            <input type="text" x-model="search" @input="searchObjects" @focus="showDropdown = true"
                                                   placeholder="{{ __('Search for objects...') }}"
                                                   class="w-full rounded-lg border-gray-600 bg-gray-800 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 px-4 py-2" />
                                            
                                            {{-- Dropdown results --}}
                                            <div x-show="showDropdown && results.length > 0" @click.outside="showDropdown = false"
                                                 class="absolute z-10 mt-1 w-full bg-gray-800 border border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto">
                                                <template x-for="result in results" :key="result.value">
                                                    <button type="button" @click="addItem(result.value); search = ''; results = []"
                                                            class="w-full text-left px-4 py-2 hover:bg-gray-700 text-white text-sm border-b border-gray-700 last:border-0"
                                                            x-text="result.label"></button>
                                                </template>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-400 mt-1">{{ __('Objects that are part of this object (e.g., NGC 224, NGC 221 for M31)') }}</p>
                                    </div>
                                </div>

                                {{-- Physical Properties --}}
                                <div class="space-y-4">
                                    <h3 class="text-xl font-semibold text-white mb-4">{{ __('Physical Properties') }}</h3>

                                    <div class="grid grid-cols-2 gap-4">
                                        @php
                                            // Convert RA from degrees to hours for display
                                            $raValue = old('ra', $object->ra);
                                            if ($raValue !== null && $raValue !== '' && floatval($raValue) > 24) {
                                                $raValue = floatval($raValue) / 15.0;
                                            }
                                        @endphp
                                        <div>
                                            <label for="ra" class="block text-sm font-medium text-gray-300 mb-1">{{ __('Right Ascension (hours)') }} <span class="text-red-500">*</span></label>
                                            <x-input name="ra" label=""
                                                     type="text" class="mt-1 block w-full"
                                                     value="{{ $raValue }}" id="ra"
                                                     placeholder="11.31553 or 11 18 55.9" required />
                                            <p class="text-xs text-gray-400 mt-1">{{ __('Format: decimal (0-24) or hh mm ss.sss') }}</p>
                                        </div>

                                        <div>
                                            <label for="decl" class="block text-sm font-medium text-gray-300 mb-1">{{ __('Declination (deg)') }} <span class="text-red-500">*</span></label>
                                            <x-input name="decl" label="" type="text"
                                                     class="mt-1 block w-full"
                                                     value="{{ old('decl', $object->decl) }}" id="decl"
                                                     placeholder="-90 to +90 or -12 32 43.3" required />
                                            <p class="text-xs text-gray-400 mt-1">{{ __('Format: decimal or dd mm ss.sss') }}</p>
                                        </div>
                                    </div>

                                    @php
                                        // Convert sentinel values to empty strings for display
                                        $magValue = old('mag', $object->mag);
                                        if ($magValue == 99.9) {
                                            $magValue = '';
                                        }
                                        
                                        $subrValue = old('subr', $object->subr);
                                        if ($subrValue == 99.9) {
                                            $subrValue = '';
                                        }
                                    @endphp
                                    <div class="grid grid-cols-2 gap-4">
                                        <x-input name="mag" label="{{ __('Magnitude') }}" type="number" step="0.01"
                                                 class="mt-1 block w-full" value="{{ $magValue }}"
                                                 id="mag" placeholder="{{ __('Leave empty to remove') }}" />

                                        <x-input name="subr" label="{{ __('Surface Brightness') }}" type="number"
                                                 step="0.01" class="mt-1 block w-full"
                                                 value="{{ $subrValue }}" id="subr" 
                                                 placeholder="{{ __('Leave empty to remove') }}" />
                                    </div>
                                    <p class="text-xs text-gray-400 -mt-2">{{ __('Optional fields - clear to remove value') }}</p>

                                    {{-- SB Object is calculated automatically, not editable --}}
                                    @php
                                        $sbObjDisplay = $object->SBObj;
                                        if ($sbObjDisplay == -999) {
                                            $sbObjDisplay = 'N/A';
                                        }
                                    @endphp
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('SB Object (Calculated)') }}</label>
                                        <input type="text" value="{{ $sbObjDisplay }}" disabled
                                               class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-400 cursor-not-allowed" />
                                        <p class="text-xs text-gray-500 mt-1">{{ __('This value is calculated automatically based on magnitude and size') }}</p>
                                    </div>

                                    @php
                                        // Convert sentinel values to empty strings for display
                                        // Database stores in arcseconds, display in arcminutes
                                        // Important: old() contains user's input (already in arcminutes), 
                                        // but object values are in arcseconds
                                        if (old('diam1') !== null) {
                                            $diam1Value = old('diam1'); // User's input, already in arcminutes
                                        } else {
                                            $diam1Value = $object->diam1;
                                            if ($diam1Value == 0) {
                                                $diam1Value = '';
                                            } elseif ($diam1Value) {
                                                $diam1Value = $diam1Value / 60.0; // Convert arcseconds to arcminutes
                                            }
                                        }
                                        
                                        if (old('diam2') !== null) {
                                            $diam2Value = old('diam2'); // User's input, already in arcminutes
                                        } else {
                                            $diam2Value = $object->diam2;
                                            if ($diam2Value == 0) {
                                                $diam2Value = '';
                                            } elseif ($diam2Value) {
                                                $diam2Value = $diam2Value / 60.0; // Convert arcseconds to arcminutes
                                            }
                                        }
                                        
                                        $paValue = old('pa', $object->pa);
                                        if ($paValue == 999) {
                                            $paValue = '';
                                        }
                                    @endphp
                                    <div class="grid grid-cols-3 gap-4">
                                        <x-input name="diam1" label="{{ __('Diameter 1 (arcmin)') }}" type="number"
                                                 step="any" class="mt-1 block w-full"
                                                 value="{{ $diam1Value }}" id="diam1" 
                                                 placeholder="{{ __('Leave empty to remove') }}" />

                                        <x-input name="diam2" label="{{ __('Diameter 2 (arcmin)') }}" type="number"
                                                 step="any" class="mt-1 block w-full"
                                                 value="{{ $diam2Value }}" id="diam2" 
                                                 placeholder="{{ __('Leave empty to remove') }}" />

                                        <x-input name="pa" label="{{ __('Position Angle (deg)') }}" type="number"
                                                 class="mt-1 block w-full" value="{{ $paValue }}"
                                                 id="pa" placeholder="{{ __('Leave empty to remove') }}" />
                                    </div>
                                    <p class="text-xs text-gray-400 -mt-2">{{ __('Optional fields - clear to remove value') }}</p>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-700">
                                <div class="flex space-x-4">
                                    <x-button class="bg-blue-600 hover:bg-blue-700" type="submit" name="update"
                                              label="{{ __('Update Object') }}" />

                                    <a href="{{ route('object.show', ['slug' => $object->slug ?? $object->name]) }}"
                                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md">
                                        {{ __('Cancel') }}
                                    </a>
                                </div>
                            </div>
                        </form>

                        {{-- SIMBAD Update Section --}}
                        <div class="mt-8 pt-6 border-t border-gray-700">
                            <h3 class="text-xl font-semibold text-white mb-4">{{ __('Update from SIMBAD') }}</h3>
                            <p class="text-sm text-gray-400 mb-4">
                                {{ __('Fetch data from SIMBAD and selectively apply values to the form fields above.') }}
                            </p>

                            <div class="flex gap-3">
                                <button type="button" id="fetch-simbad" 
                                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                                    {{ __('Fetch from SIMBAD') }}
                                </button>
                                
                                <a href="https://simbad.cds.unistra.fr/simbad/sim-id?Ident={{ urlencode($simbadName) }}" 
                                   target="_blank" 
                                   class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors inline-flex items-center gap-2">
                                    {{ __('View in SIMBAD') }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            </div>

                            <div id="simbad-loading" class="hidden mt-4">
                                <div class="flex items-center text-blue-400">
                                    <svg class="animate-spin h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('Fetching data from SIMBAD...') }}
                                </div>
                            </div>

                            <div id="simbad-error" class="hidden mt-4 p-4 bg-red-900/50 border border-red-700 rounded-lg text-red-200">
                            </div>

                            <div id="simbad-results" class="hidden mt-4 p-4 bg-gray-800 border border-gray-700 rounded-lg">
                                <h4 class="text-lg font-semibold text-white mb-3">{{ __('SIMBAD Data') }}</h4>
                                <div class="space-y-3">
                                    <div id="simbad-ra" class="hidden flex items-center justify-between py-2 border-b border-gray-700">
                                        <div>
                                            <span class="text-gray-400">{{ __('Right Ascension:') }}</span>
                                            <span class="text-white ml-2" id="simbad-ra-value"></span>
                                            <span class="text-gray-500 text-sm ml-2">({{ __('degrees') }})</span>
                                        </div>
                                        <button type="button" onclick="applySimbadValue('ra')" 
                                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded transition-colors">
                                            {{ __('Apply') }}
                                        </button>
                                    </div>

                                    <div id="simbad-decl" class="hidden flex items-center justify-between py-2 border-b border-gray-700">
                                        <div>
                                            <span class="text-gray-400">{{ __('Declination:') }}</span>
                                            <span class="text-white ml-2" id="simbad-decl-value"></span>
                                            <span class="text-gray-500 text-sm ml-2">({{ __('degrees') }})</span>
                                        </div>
                                        <button type="button" onclick="applySimbadValue('decl')" 
                                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded transition-colors">
                                            {{ __('Apply') }}
                                        </button>
                                    </div>

                                    <div id="simbad-mag" class="hidden flex items-center justify-between py-2 border-b border-gray-700">
                                        <div>
                                            <span class="text-gray-400">{{ __('Magnitude:') }}</span>
                                            <span class="text-white ml-2" id="simbad-mag-value"></span>
                                        </div>
                                        <button type="button" onclick="applySimbadValue('mag')" 
                                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded transition-colors">
                                            {{ __('Apply') }}
                                        </button>
                                    </div>

                                    <div id="simbad-diam1" class="hidden flex items-center justify-between py-2 border-b border-gray-700">
                                        <div>
                                            <span class="text-gray-400">{{ __('Diameter 1:') }}</span>
                                            <span class="text-white ml-2" id="simbad-diam1-value"></span>
                                            <span class="text-gray-500 text-sm ml-2">({{ __('arcminutes') }})</span>
                                        </div>
                                        <button type="button" onclick="applySimbadValue('diam1')" 
                                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded transition-colors">
                                            {{ __('Apply') }}
                                        </button>
                                    </div>

                                    <div id="simbad-diam2" class="hidden flex items-center justify-between py-2 border-b border-gray-700">
                                        <div>
                                            <span class="text-gray-400">{{ __('Diameter 2:') }}</span>
                                            <span class="text-white ml-2" id="simbad-diam2-value"></span>
                                            <span class="text-gray-500 text-sm ml-2">({{ __('arcminutes') }})</span>
                                        </div>
                                        <button type="button" onclick="applySimbadValue('diam2')" 
                                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded transition-colors">
                                            {{ __('Apply') }}
                                        </button>
                                    </div>

                                    <div id="simbad-pa" class="hidden flex items-center justify-between py-2">
                                        <div>
                                            <span class="text-gray-400">{{ __('Position Angle:') }}</span>
                                            <span class="text-white ml-2" id="simbad-pa-value"></span>
                                            <span class="text-gray-500 text-sm ml-2">({{ __('degrees') }})</span>
                                        </div>
                                        <button type="button" onclick="applySimbadValue('pa')" 
                                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded transition-colors">
                                            {{ __('Apply') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('objectSearch', (initialSelected, fieldName) => ({
                selected: Array.isArray(initialSelected) ? [...initialSelected] : [],
                search: '',
                results: [],
                showDropdown: false,
                searchTimeout: null,
                
                addItem(value) {
                    if (!this.selected.includes(value)) {
                        this.selected.push(value);
                    }
                    this.showDropdown = false;
                },
                
                removeItem(index) {
                    this.selected.splice(index, 1);
                },
                
                async searchObjects() {
                    if (this.searchTimeout) {
                        clearTimeout(this.searchTimeout);
                    }
                    
                    if (this.search.length < 2) {
                        this.results = [];
                        this.showDropdown = false;
                        return;
                    }
                    
                    this.searchTimeout = setTimeout(async () => {
                        try {
                            const response = await fetch(`{{ route('api.objects.search') }}?search=${encodeURIComponent(this.search)}`);
                            const data = await response.json();
                            
                            // Filter out already selected items
                            this.results = data.filter(item => !this.selected.includes(item.value));
                            this.showDropdown = this.results.length > 0;
                        } catch (error) {
                            console.error('Search error:', error);
                            this.results = [];
                        }
                    }, 300);
                }
            }));
        });
        
        // Auto-calculate constellation from coordinates
        document.addEventListener('DOMContentLoaded', function() {
            const raInput = document.getElementById('ra');
            const declInput = document.getElementById('decl');
            const conSelect = document.getElementById('con');
            let updateTimeout = null;
            
            // Parse sexagesimal format (hh mm ss.sss) to decimal hours
            function parseSexagesimalRA(input) {
                const match = input.trim().match(/^(\d+)\s+(\d+)\s+([\d.]+)$/);
                if (match) {
                    const hours = parseFloat(match[1]);
                    const minutes = parseFloat(match[2]);
                    const seconds = parseFloat(match[3]);
                    return hours + (minutes / 60.0) + (seconds / 3600.0);
                }
                return null;
            }
            
            // Parse sexagesimal format (dd mm ss.sss) to decimal degrees
            function parseSexagesimalDecl(input) {
                const match = input.trim().match(/^([+-]?\d+)\s+(\d+)\s+([\d.]+)$/);
                if (match) {
                    const degrees = parseFloat(match[1]);
                    const minutes = parseFloat(match[2]);
                    const seconds = parseFloat(match[3]);
                    const sign = degrees < 0 ? -1 : 1;
                    const absDegrees = Math.abs(degrees);
                    return sign * (absDegrees + (minutes / 60.0) + (seconds / 3600.0));
                }
                return null;
            }
            
            async function updateConstellation() {
                let ra = raInput.value;
                let decl = declInput.value;
                
                // Only update if both values are present
                if (!ra || !decl) {
                    return;
                }
                
                // Parse RA (could be decimal or sexagesimal)
                let raDecimal = parseSexagesimalRA(ra);
                if (raDecimal === null) {
                    raDecimal = parseFloat(ra);
                }
                
                // Parse Decl (could be decimal or sexagesimal)
                let declDecimal = parseSexagesimalDecl(decl);
                if (declDecimal === null) {
                    declDecimal = parseFloat(decl);
                }
                
                // Validate ranges
                if (isNaN(raDecimal) || isNaN(declDecimal)) {
                    return;
                }
                
                if (raDecimal < 0 || raDecimal > 24 || declDecimal < -90 || declDecimal > 90) {
                    return;
                }
                
                try {
                    const response = await fetch(`{{ route('api.constellation-from-coords') }}?ra=${encodeURIComponent(raDecimal)}&decl=${encodeURIComponent(declDecimal)}`);
                    const data = await response.json();
                    
                    if (data.constellation && conSelect) {
                        // Find the option with this constellation
                        const options = conSelect.options;
                        const hiddenInput = document.getElementById('con_hidden');
                        
                        for (let i = 0; i < options.length; i++) {
                            if (options[i].value === data.constellation) {
                                conSelect.selectedIndex = i;
                                // Update hidden input as well
                                if (hiddenInput) {
                                    hiddenInput.value = data.constellation;
                                }
                                // Add visual feedback
                                conSelect.style.borderColor = '#10b981'; // green
                                setTimeout(() => {
                                    conSelect.style.borderColor = '';
                                }, 1000);
                                break;
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error calculating constellation:', error);
                }
            }
            
            function scheduleUpdate() {
                if (updateTimeout) {
                    clearTimeout(updateTimeout);
                }
                updateTimeout = setTimeout(updateConstellation, 800);
            }
            
            if (raInput && declInput) {
                raInput.addEventListener('input', scheduleUpdate);
                declInput.addEventListener('input', scheduleUpdate);
                raInput.addEventListener('change', updateConstellation);
                declInput.addEventListener('change', updateConstellation);
            }
        });

        // SIMBAD Data Fetching and Applying
        let simbadData = {};

        document.getElementById('fetch-simbad').addEventListener('click', async function() {
            const loadingEl = document.getElementById('simbad-loading');
            const errorEl = document.getElementById('simbad-error');
            const resultsEl = document.getElementById('simbad-results');
            
            // Hide previous results/errors
            errorEl.classList.add('hidden');
            resultsEl.classList.add('hidden');
            loadingEl.classList.remove('hidden');

            try {
                const response = await fetch("{{ route('api.objects.simbad-data', ['slug' => $object->slug ?? $object->name]) }}");
                const data = await response.json();

                loadingEl.classList.add('hidden');

                if (!response.ok || data.error) {
                    errorEl.textContent = data.error || 'An error occurred';
                    errorEl.classList.remove('hidden');
                    return;
                }

                simbadData = data.data;
                displaySimbadResults(data.data);
                resultsEl.classList.remove('hidden');

            } catch (error) {
                loadingEl.classList.add('hidden');
                errorEl.textContent = 'Failed to fetch SIMBAD data: ' + error.message;
                errorEl.classList.remove('hidden');
            }
        });

        function displaySimbadResults(data) {
            // RA (convert degrees to hours for display)
            if (data.ra !== undefined) {
                const raHours = data.ra / 15.0;
                document.getElementById('simbad-ra-value').textContent = raHours.toFixed(6) + ' hours';
                document.getElementById('simbad-ra').classList.remove('hidden');
            } else {
                document.getElementById('simbad-ra').classList.add('hidden');
            }

            // Declination
            if (data.decl !== undefined) {
                document.getElementById('simbad-decl-value').textContent = data.decl.toFixed(6) + '°';
                document.getElementById('simbad-decl').classList.remove('hidden');
            } else {
                document.getElementById('simbad-decl').classList.add('hidden');
            }

            // Magnitude
            if (data.mag !== undefined) {
                document.getElementById('simbad-mag-value').textContent = data.mag.toFixed(2);
                document.getElementById('simbad-mag').classList.remove('hidden');
            } else {
                document.getElementById('simbad-mag').classList.add('hidden');
            }

            // Diameter 1 (already in arcminutes from SIMBAD)
            if (data.diam1 !== undefined) {
                document.getElementById('simbad-diam1-value').textContent = data.diam1.toFixed(2) + "'";
                document.getElementById('simbad-diam1').classList.remove('hidden');
            } else {
                document.getElementById('simbad-diam1').classList.add('hidden');
            }

            // Diameter 2 (already in arcminutes from SIMBAD)
            if (data.diam2 !== undefined) {
                document.getElementById('simbad-diam2-value').textContent = data.diam2.toFixed(2) + "'";
                document.getElementById('simbad-diam2').classList.remove('hidden');
            } else {
                document.getElementById('simbad-diam2').classList.add('hidden');
            }

            // Position Angle
            if (data.pa !== undefined) {
                document.getElementById('simbad-pa-value').textContent = data.pa + '°';
                document.getElementById('simbad-pa').classList.remove('hidden');
            } else {
                document.getElementById('simbad-pa').classList.add('hidden');
            }
        }

        function applySimbadValue(field) {
            if (simbadData[field] === undefined) return;

            const value = simbadData[field];

            switch(field) {
                case 'ra':
                    // Convert degrees to hours for display
                    const raHours = value / 15.0;
                    document.getElementById('ra').value = raHours.toFixed(6);
                    // Trigger constellation update
                    document.getElementById('ra').dispatchEvent(new Event('input', { bubbles: true }));
                    break;
                    
                case 'decl':
                    document.getElementById('decl').value = value.toFixed(6);
                    // Trigger constellation update
                    document.getElementById('decl').dispatchEvent(new Event('input', { bubbles: true }));
                    break;
                    
                case 'mag':
                    document.getElementById('mag').value = value.toFixed(2);
                    break;
                    
                case 'diam1':
                    // Value from SIMBAD is already in arcminutes, use directly
                    document.getElementById('diam1').value = value.toFixed(2);
                    break;
                    
                case 'diam2':
                    // Value from SIMBAD is already in arcminutes, use directly
                    document.getElementById('diam2').value = value.toFixed(2);
                    break;
                    
                case 'pa':
                    document.getElementById('pa').value = value;
                    break;
            }

            // Visual feedback
            const inputEl = document.getElementById(field) || document.getElementById(field === 'decl' ? 'decl' : field);
            if (inputEl) {
                inputEl.style.backgroundColor = '#10b981';
                setTimeout(() => {
                    inputEl.style.backgroundColor = '';
                }, 1000);
            }
        }
    </script>
    @endpush
</x-app-layout>
