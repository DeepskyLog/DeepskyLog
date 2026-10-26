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

                                    {{-- A new name of a catalog DeepskyLog does not have yet: a new catalog, or a name without a catalog (#526) --}}
                                    <div x-data="newCatalogs(@js($knownCatalogs), @js($alternativeNames->concat([$object->name])->values()), @js(collect(old('name_catalogs', []))->pluck('choice', 'name')))"
                                         class="space-y-2">
                                        <template x-for="(item, index) in unknown" :key="item.name">
                                            <div class="rounded-lg border border-yellow-700 bg-yellow-900/30 p-3 text-sm">
                                                <p class="text-yellow-200">
                                                    <span class="font-semibold" x-text="item.name"></span>:
                                                    <span x-text="@js(__(':catalog is not a catalog in DeepskyLog yet.')).replace(':catalog', item.catalog)"></span>
                                                </p>
                                                <input type="hidden" :name="`name_catalogs[${index}][name]`" :value="item.name" />
                                                <label class="mt-2 flex items-center gap-2 text-gray-200">
                                                    <input type="radio" value="new" required x-model="choices[item.name]"
                                                           :name="`name_catalogs[${index}][choice]`" />
                                                    <span x-text="@js(__('Add the new catalog :catalog')).replace(':catalog', item.catalog)"></span>
                                                </label>
                                                <label class="mt-1 flex items-center gap-2 text-gray-200">
                                                    <input type="radio" value="none" required x-model="choices[item.name]"
                                                           :name="`name_catalogs[${index}][choice]`" />
                                                    <span>{{ __('Not a catalog name (a name such as Leo T or Coma Berenices Dwarf)') }}</span>
                                                </label>
                                            </div>
                                        </template>
                                    </div>

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
                                    @if ($object->hasEstimatedMagnitude())
                                        <p class="text-xs text-amber-300">{{ __('The magnitude is estimated from the brightness class of :source. A magnitude you enter replaces the estimate.', ['source' => $object->mag_source]) }}</p>
                                    @endif

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

                            {{-- Review (#526): listed on /admin/object-reviews --}}
                            <div class="mt-8 pt-6 border-t border-gray-700">
                                <h3 class="text-xl font-semibold text-white mb-1">{{ __('Review') }}</h3>
                                <p class="text-sm text-gray-400 mb-4">
                                    @if ($object->review_status !== null && $object->reviewed_at !== null)
                                        {{ __('Set to :status by :observer on :date.', [
                                            'status' => \App\Models\DeepskyObject::reviewStatusLabel($object->review_status),
                                            'observer' => html_entity_decode($object->reviewer?->name ?? __('an unknown observer')),
                                            'date' => $object->reviewed_at->isoFormat('LL'),
                                        ]) }}
                                    @elseif ($object->review_status !== null)
                                        {{ __('Added to DeepskyLog and not checked yet.') }}
                                    @else
                                        {{ __('Nobody has reviewed this object yet.') }}
                                    @endif
                                    <a href="{{ route('admin.object-reviews') }}" class="text-blue-400 hover:text-blue-300">{{ __('All reviews') }}</a>
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="review_status" class="block text-sm font-medium text-gray-300 mb-1">{{ __('Review status') }}</label>
                                        <select name="review_status" id="review_status"
                                                class="mt-1 block w-full rounded-lg border-gray-600 bg-gray-800 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 px-4 py-2">
                                            @foreach (array_merge([''], \App\Models\DeepskyObject::REVIEW_STATUSES) as $status)
                                                <option value="{{ $status }}" @selected(old('review_status', $object->review_status ?? '') === $status)>
                                                    {{ \App\Models\DeepskyObject::reviewStatusLabel($status === '' ? null : $status) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <x-input name="review_note" label="{{ __('Review note') }}" type="text"
                                                 class="mt-1 block w-full" maxlength="255" id="review_note"
                                                 value="{{ old('review_note', $object->review_note) }}"
                                                 placeholder="{{ __('What needs attention, or what was checked') }}" />
                                    </div>
                                </div>
                            </div>

                            {{-- Preview (#526): the chart and the DSS image of the values in the form --}}
                            @php
                                $savedPreview = [
                                    'ra' => is_numeric($object->ra) ? ((float) $object->ra > 24 ? (float) $object->ra / 15.0 : (float) $object->ra) : null,
                                    'decl' => is_numeric($object->decl) ? (float) $object->decl : null,
                                    'diam1' => (float) $object->diam1 / 60.0,
                                    'diam2' => (float) $object->diam2 / 60.0,
                                    'pa' => $object->pa !== null && (int) $object->pa !== 999 ? (float) $object->pa : null,
                                ];
                            @endphp
                            <div class="mt-8 pt-6 border-t border-gray-700">
                                <h3 class="text-xl font-semibold text-white mb-1">{{ __('Preview') }}</h3>
                                <p class="text-sm text-gray-400 mb-4">
                                    {{ __('The object as the form describes it, updated while you type. On the photo the form is drawn in red and the saved position and size in yellow.') }}
                                </p>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div>
                                        <div class="relative rounded bg-white overflow-hidden" style="aspect-ratio: 1120 / 780;">
                                            <img id="preview-chart" alt="{{ __('Star chart') }}" class="w-full h-full"
                                                 data-url="{{ route('object.edit.preview-chart', ['slug' => $object->slug ?? $object->name]) }}" />
                                            <div id="preview-chart-loading"
                                                 class="hidden absolute top-2 left-2 rounded bg-gray-900 px-2 py-1 text-xs text-gray-200">
                                                {{ __('Loading...') }}
                                            </div>
                                        </div>
                                        <p id="preview-chart-error" class="hidden mt-1 text-sm text-red-300">
                                            {{ __('No chart: check the right ascension and declination.') }}
                                        </p>
                                    </div>
                                    <div>
                                        <div id="preview-aladin" class="w-full rounded bg-black" style="aspect-ratio: 1120 / 780;"
                                             data-saved='@json($savedPreview)'></div>
                                        <p class="mt-1 text-xs text-gray-400">{{ __('DSS2 colour image, from Aladin Lite (CDS).') }}</p>
                                    </div>
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
    <link rel="stylesheet" href="https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/aladin.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script defer src="https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/aladin.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            // New names whose catalog DeepskyLog does not have yet, from the name and
            // the alternative names; the names the object already has keep their catalog.
            Alpine.data('newCatalogs', (known, savedNames, initialChoices) => ({
                unknown: [],
                choices: Object.assign({}, initialChoices),

                init() {
                    ['name', 'alternative_names'].forEach((id) => {
                        document.getElementById(id)?.addEventListener('input', () => this.update());
                    });
                    this.update();
                },

                // The catalog of a name, as ObjectName::splitCatalogDesignation() takes it
                catalogOf(name) {
                    const match = name.match(/^(\S*[A-Za-z]\S*)\s+(\S.*)$/u);
                    return match ? match[1] : '';
                },

                update() {
                    const names = (document.getElementById('alternative_names')?.value || '').split(',')
                        .map((name) => name.trim());
                    names.push((document.getElementById('name')?.value || '').trim());
                    const seen = new Set();
                    this.unknown = names.filter((name) => {
                        if (name === '' || seen.has(name) || savedNames.includes(name)) {
                            return false;
                        }
                        seen.add(name);
                        const catalog = this.catalogOf(name);
                        return catalog !== '' && !(catalog.toLowerCase() in known);
                    }).map((name) => ({ name: name, catalog: this.catalogOf(name) }));
                },
            }));

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
                // Also -00 30 00
                const sign = match[1].startsWith('-') ? -1 : 1;
                const absDegrees = Math.abs(degrees);
                return sign * (absDegrees + (minutes / 60.0) + (seconds / 3600.0));
            }
            return null;
        }

        // Auto-calculate constellation from coordinates
        document.addEventListener('DOMContentLoaded', function() {
            const raInput = document.getElementById('ra');
            const declInput = document.getElementById('decl');
            const conSelect = document.getElementById('con');
            let updateTimeout = null;
            
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

        // Preview (#526): the star chart and the DSS image of the values in the
        // form. The chart is drawn by the server with the form's values; the
        // photo gets the outline of the form (red) and of the saved object (yellow).
        document.addEventListener('DOMContentLoaded', function() {
            const chartImg = document.getElementById('preview-chart');
            const aladinDiv = document.getElementById('preview-aladin');
            if (!chartImg || !aladinDiv) {
                return;
            }
            const loadingEl = document.getElementById('preview-chart-loading');
            const errorEl = document.getElementById('preview-chart-error');
            const saved = JSON.parse(aladinDiv.dataset.saved || '{}');
            const fields = ['ra', 'decl', 'type', 'mag', 'diam1', 'diam2', 'pa'];
            let aladin = null;
            let savedOverlay = null;
            let formOverlay = null;
            let timer = null;
            let lastQuery = null;

            function formValue(id) {
                const el = document.getElementById(id);
                return el ? el.value.trim() : '';
            }

            // RA in hours and declination in degrees, as the server parses them
            function formPosition() {
                const raText = formValue('ra');
                const declText = formValue('decl');
                let ra = parseSexagesimalRA(raText);
                if (ra === null && raText !== '' && !isNaN(Number(raText))) {
                    ra = Number(raText) > 24 ? Number(raText) / 15.0 : Number(raText);
                }
                let decl = parseSexagesimalDecl(declText);
                if (decl === null && declText !== '' && !isNaN(Number(declText))) {
                    decl = Number(declText);
                }
                if (ra === null || decl === null || ra < 0 || ra >= 24 || Math.abs(decl) > 90) {
                    return null;
                }
                return { ra: ra, decl: decl };
            }

            function number(id) {
                const v = formValue(id);
                return v !== '' && !isNaN(Number(v)) ? Number(v) : null;
            }

            // Same field as the chart: radius 1.5 times the major axis, 10' to 180'
            function radiusArcMin(diam1, diam2) {
                return Math.min(180, Math.max(10, 1.5 * Math.max(diam1 || 0, diam2 || 0)));
            }

            // Outline of an object of diam1 x diam2 arcminutes at position angle pa
            // (north through east), as RA/Dec points in degrees; a cross without a size.
            function outline(raHours, decl, diam1, diam2, pa) {
                const ra = raHours * 15.0;
                const cosDec = Math.max(0.01, Math.cos(decl * Math.PI / 180));
                const point = (north, east) => [ra + east / cosDec, decl + north];
                const a = (diam1 || 0) / 120.0;
                const b = (diam2 || diam1 || 0) / 120.0;
                if (a <= 0) {
                    const c = 0.5 / 60.0;
                    return [[point(-c, 0), point(c, 0)], [point(0, -c), point(0, c)]];
                }
                const theta = (pa === null ? 0 : pa) * Math.PI / 180;
                const points = [];
                for (let i = 0; i <= 72; i++) {
                    const t = i * 2 * Math.PI / 72;
                    const major = a * Math.cos(t);
                    const minor = b * Math.sin(t);
                    points.push(point(major * Math.cos(theta) - minor * Math.sin(theta),
                        major * Math.sin(theta) + minor * Math.cos(theta)));
                }
                return [points];
            }

            // Aladin Lite v2 strokes all lines of an overlay at once, in the
            // colour of the last one (white unless given), at the overlay's width
            function drawOutline(overlay, lines, color) {
                lines.forEach(function(line) {
                    overlay.add(A.polyline(line, { color: color }));
                });
            }

            function updateAladin(pos) {
                if (typeof A === 'undefined' || typeof A.aladin !== 'function') {
                    return;
                }
                const diam1 = number('diam1');
                const diam2 = number('diam2');
                const fov = 2 * radiusArcMin(diam1, diam2) / 60.0 * 1120 / 780;
                if (!aladin) {
                    aladin = A.aladin('#preview-aladin', {
                        survey: 'P/DSS2/color',
                        fov: fov,
                        target: (pos.ra * 15).toFixed(6) + ' ' + pos.decl.toFixed(6),
                        showReticle: false,
                        showLayersControl: false,
                        showGotoControl: false,
                        showFullscreenControl: true,
                    });
                    // Wider, so it stays visible under an unchanged red outline
                    savedOverlay = A.graphicOverlay({ name: 'saved', color: '#facc15', lineWidth: 5 });
                    formOverlay = A.graphicOverlay({ name: 'form', color: '#ef4444', lineWidth: 2 });
                    aladin.addOverlay(savedOverlay);
                    aladin.addOverlay(formOverlay);
                } else {
                    aladin.gotoRaDec(pos.ra * 15, pos.decl);
                    aladin.setFoV(fov);
                }
                savedOverlay.removeAll();
                formOverlay.removeAll();
                if (saved.ra !== null && saved.decl !== null) {
                    drawOutline(savedOverlay, outline(saved.ra, saved.decl, saved.diam1, saved.diam2, saved.pa), '#facc15');
                }
                drawOutline(formOverlay, outline(pos.ra, pos.decl, diam1, diam2, number('pa')), '#ef4444');
            }

            function updateChart() {
                const query = new URLSearchParams();
                fields.forEach(function(id) {
                    query.set(id, formValue(id));
                });
                const text = query.toString();
                if (text === lastQuery) {
                    return;
                }
                lastQuery = text;
                loadingEl.classList.remove('hidden');
                chartImg.src = chartImg.dataset.url + '?' + text;
            }

            function update() {
                const pos = formPosition();
                if (!pos) {
                    errorEl.classList.remove('hidden');
                    return;
                }
                errorEl.classList.add('hidden');
                updateChart();
                try {
                    updateAladin(pos);
                } catch (e) {
                    console.warn('Aladin preview failed', e);
                }
            }

            chartImg.addEventListener('load', function() {
                loadingEl.classList.add('hidden');
            });
            chartImg.addEventListener('error', function() {
                loadingEl.classList.add('hidden');
                errorEl.classList.remove('hidden');
            });

            fields.forEach(function(id) {
                const el = document.getElementById(id);
                if (!el) {
                    return;
                }
                ['input', 'change'].forEach(function(event) {
                    el.addEventListener(event, function() {
                        clearTimeout(timer);
                        timer = setTimeout(update, 700);
                    });
                });
            });

            // Aladin Lite is loaded with defer: wait for it before the first draw
            (function start(attempts) {
                if ((typeof A === 'undefined' || typeof A.aladin !== 'function') && attempts < 100) {
                    setTimeout(function() { start(attempts + 1); }, 100);
                    return;
                }
                update();
            })(0);
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
                // Let the preview follow the new value
                inputEl.dispatchEvent(new Event('change', { bubbles: true }));
                inputEl.style.backgroundColor = '#10b981';
                setTimeout(() => {
                    inputEl.style.backgroundColor = '';
                }, 1000);
            }
        }
    </script>
    @endpush
</x-app-layout>
