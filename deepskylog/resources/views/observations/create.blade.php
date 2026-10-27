<x-app-layout>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold text-white mb-4">{{ old('edit_unified_id') ? __('Edit observation') : __('Add observation') }}</h1>

        <div class="bg-gray-900 p-4 rounded shadow text-gray-100">
            @if (empty($resolved['type']))
                <div class="text-yellow-200 mb-4">{{ __('No object found for') }} '{{ $slug }}'.</div>
                @if (old('edit_url'))
                    {{-- Editing an observation of an object that is not in the database: move it to another one --}}
                    <p class="mb-3 text-sm text-gray-300">{{ __('Choose the object this observation belongs to.') }}</p>
                    @if (! empty($objectChoices))
                        <div class="mb-3 flex flex-wrap gap-2">
                            @foreach ($objectChoices as $choiceName => $choiceSlug)
                                <a href="{{ old('edit_url') }}?object={{ urlencode($choiceSlug) }}" class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">{{ $choiceName }}</a>
                            @endforeach
                        </div>
                    @endif
                    <form method="GET" action="{{ old('edit_url') }}" class="flex flex-wrap items-center gap-2">
                        <input type="text" name="object" required placeholder="{{ __('Object name') }}" class="rounded border border-gray-600 bg-gray-800 px-3 py-2 text-sm text-gray-100">
                        <button type="submit" class="rounded bg-gray-700 px-3 py-2 text-sm hover:bg-gray-600">{{ __('Use this object') }}</button>
                    </form>
                @else
                    <a href="{{ route('observation.add.select') }}" class="bg-gray-700 px-3 py-2 rounded">{{ __('New observation') }}</a>
                @endif
            @else
                <div class="mb-4">
                    <strong>{{ __('Object') }}:</strong> {{ $resolved['record']->name ?? $slug }}
                    @if (old('edit_previous_object'))
                        <span class="ml-2 text-sm text-amber-300">{{ __('(was :name)', ['name' => old('edit_previous_object')]) }}</span>
                    @endif
                </div>
                @php
                    $isSun = false;
                    if (!empty($resolved['record'])) {
                        $rec = $resolved['record'];
                        $slugOrName = strtolower(trim((string) ($rec->slug ?? $rec->name ?? '')));
                        $isSun = $slugOrName === 'sun';
                    }
                @endphp
                @if (! $isSun)
                    <div class="mb-4">
                        <strong>{{ __('Type') }}:</strong> {{ data_get($objectSummary, 'type_label') ?? ucfirst($resolved['type']) }}
                    </div>
                @endif

                @if(!empty($objectSummary))
                    <div class="mb-3 text-sm text-gray-300">
                        @if(!empty($objectSummary['type_label']))
                            <div><strong>{{ __('Subtype') }}:</strong> {{ $objectSummary['type_label'] }}</div>
                        @endif
                        @if(!empty($objectSummary['constellation']))
                            <div><strong>{{ __('Constellation') }}:</strong> {{ $objectSummary['constellation'] }}</div>
                        @endif

                        <div class="mt-2 grid grid-cols-1 sm:inline-grid sm:grid-flow-col sm:auto-cols-max gap-y-0 sm:gap-x-6">
                            <div>
                                <div class="flex items-center gap-0">
                                    <span class="text-gray-400">{{ __('Observations') }}</span>
                                    <a href="{{ url(($resolved['type'] === 'comet' ? '/cometobservations/' : '/observations/') . ($objectSummary['object_slug'] ?? $slug)) }}" class="text-white font-medium ml-2 hover:underline">{{ $objectSummary['total_observations'] ?? 0 }}</a>
                                </div>
                                @auth
                                    <div class="flex items-center gap-0 mt-1">
                                        <span class="text-gray-400">{{ __('Your observations') }}</span>
                                        <a href="{{ url(($resolved['type'] === 'comet' ? '/cometobservations/' . auth()->user()->slug . '/' : '/observations/' . auth()->user()->slug . '/') . ($objectSummary['object_slug'] ?? $slug)) }}" class="text-white font-medium ml-2 hover:underline">{{ $objectSummary['your_observations'] ?? 0 }}</a>
                                    </div>
                                @endauth
                            </div>

                            <div>
                                <div class="flex items-center gap-0">
                                    <span class="text-gray-400">{{ __('Drawings') }}</span>
                                    <a href="{{ url(($resolved['type'] === 'comet' ? '/cometdrawings/' : '/observations/drawings/') . ($objectSummary['object_slug'] ?? $slug)) }}" class="text-white font-medium ml-2 hover:underline">{{ $objectSummary['total_drawings'] ?? 0 }}</a>
                                </div>
                                @auth
                                    <div class="flex items-center gap-0 mt-1">
                                        <span class="text-gray-400">{{ __('Your drawings') }}</span>
                                        <a href="{{ url(($resolved['type'] === 'comet' ? '/cometdrawings/' . auth()->user()->slug . '/' : '/observations/drawings/' . auth()->user()->slug . '/') . ($objectSummary['object_slug'] ?? $slug)) }}" class="text-white font-medium ml-2 hover:underline">{{ $objectSummary['your_drawings'] ?? 0 }}</a>
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endif

                @php
                    $showInches = optional(auth()->user())->showInches ?? false;
                    $oldObservedTime = old('observed_time', $prefill['observed_time'] ?? '');
                    $displayTime = '';
                    $displayAMPM = 'AM';
                    if (!empty($oldObservedTime)) {
                        try {
                            $dt = \Carbon\Carbon::parse($oldObservedTime);
                            $displayTime = $dt->format('g:i');
                            $displayAMPM = $dt->format('A');
                        } catch (\Throwable $_) {
                            $displayTime = $oldObservedTime;
                        }
                    }
                @endphp

                {{-- debug block removed --}}

                @if(isset($instrumentSets) && $instrumentSets->isNotEmpty())
                    {{-- Changing the set sends the form as it is filled in, so the page comes back with the entries kept --}}
                    <div class="mb-4">
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Instrument set') }}</label>
                        <select onchange="window.dslSwitchInstrumentSet(this.value)" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">
                            <option value="">{{ __('All gear') }}</option>
                            @foreach($instrumentSets as $set)
                                <option value="{{ $set->id }}" @if(isset($instrumentSet) && $instrumentSet && $instrumentSet->id == $set->id) selected @endif>{{ $set->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <script>
                        window.dslSwitchInstrumentSet = function (setId) {
                            var form = document.querySelector('form[method="POST"][enctype="multipart/form-data"]');
                            if (!form) return;
                            if (typeof tinymce !== 'undefined') tinymce.triggerSave();
                            // A chosen sketch cannot come back with the page, so it is not sent.
                            form.querySelectorAll('input[type=file]').forEach(function (input) { input.disabled = true; });
                            var field = document.createElement('input');
                            field.type = 'hidden';
                            field.name = 'switch_instrument_set';
                            field.value = setId;
                            form.appendChild(field);
                            form.submit();
                        };
                    </script>
                @endif

                <form method="POST" action="{{ route('observation.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="edit_unified_id" value="{{ old('edit_unified_id', '') }}" />

                    @if ($errors->any())
                        <div class="mb-4 text-red-300">
                            <ul class="list-disc list-inside">
                            @if($errors->any())
                                @php
                                    // Safely determine a human-readable label for the currently selected language.
                                    $rawLang = old('language', auth()->check() ? auth()->user()->observationlanguage : '');
                                    $selectedLangId = $rawLang;
                                    if (is_string($rawLang) && (str_starts_with(trim($rawLang), '{') || str_contains($rawLang, 'label:'))) {
                                        $json = preg_replace('/([a-zA-Z0-9_]+)\s*:/', '"$1":', $rawLang);
                                        $json = str_replace("'", '"', $json);
                                        $json = preg_replace('/,\s*}/', '}', $json);
                                        $data = json_decode($json, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                                            $selectedLangId = $data['value'] ?? $data['id'] ?? $selectedLangId;
                                        } elseif (preg_match('/value\s*[:=]\s*(?:"|\')?([a-zA-Z\-]+)(?:"|\')?/', $rawLang, $m)) {
                                            $selectedLangId = $m[1];
                                        }
                                    }
                                    $selectedLangLabel = $selectedLangId ? (\Languages::lookup([$selectedLangId], 'mixed')->values()[0] ?? $selectedLangId) : null;
                                @endphp
                                @if($selectedLangLabel)
                                    <div class="mb-2 text-sm text-red-200">{{ $selectedLangLabel }}</div>
                                @endif
                            @endif
                        
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <input type="hidden" name="target_table" value="{{ $resolved['table'] }}">
                    <input type="hidden" name="target_slug" value="{{ $resolved['record']->slug ?? $slug }}">
                    @php $returnTo = \App\Http\Controllers\ObservationsController::returnUrl(old('return_to', request()->query('return_to'))); @endphp
                    @if ($returnTo)
                        <input type="hidden" name="return_to" value="{{ $returnTo }}">
                    @endif

                    @if(isset($php_upload_max_filesize) || isset($php_post_max_size))
                        <div class="mb-3 p-3 rounded bg-yellow-900 text-yellow-100 text-sm">
                            <strong>{{ __('Upload limits') }}:</strong>
                            {{ __('Maximum file upload: :upload; Maximum POST size: :post.', ['upload' => $php_upload_max_filesize ?? 'N/A', 'post' => $php_post_max_size ?? 'N/A']) }}
                            <div class="mt-1 text-xs text-yellow-200">{{ __('If your sketch is larger than these limits it will not be uploaded.') }}</div>
                        </div>
                    @endif

                    @php
                        $showInches = optional(auth()->user())->showInches ?? false;
                        $oldObservedTime = old('observed_time', $prefill['observed_time'] ?? '');
                        $displayTime = '';
                        $displayAMPM = 'AM';
                        if (!empty($oldObservedTime)) {
                            try {
                                $dt = \Carbon\Carbon::parse($oldObservedTime);
                                $displayTime = $dt->format('g:i');
                                $displayAMPM = $dt->format('A');
                            } catch (\Throwable $_) {
                                $displayTime = $oldObservedTime;
                            }
                        }

                        $oldObservedOn = old('observed_on', '');
                        $displayObservedOn = '';
                        if (!empty($oldObservedOn)) {
                            try {
                                $d = \Carbon\Carbon::parse($oldObservedOn);
                                $displayObservedOn = $showInches ? $d->format('m/d/Y') : $d->format('d/m/Y');
                            } catch (\Throwable $_) {
                                $displayObservedOn = $oldObservedOn;
                            }
                        }
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">
                                {{ __('Date') }}
                                <span class="text-xs text-gray-400">({{ $showInches ? 'mm/dd/yyyy' : 'dd/mm/yyyy' }})</span>
                            </label>
                            <x-datetime-picker without-time="true" without-timezone="true"
                                name="observed_on"
                                display-format="{{ $showInches ? 'MM/DD/YYYY' : 'DD/MM/YYYY' }}"
                                parse-format="YYYY-MM-DD"
                                value="{{ old('observed_on', $prefill['observed_on'] ?? \Carbon\Carbon::yesterday()->format('Y-m-d')) }}"
                                class="w-full" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">
                                {{ __('Time') }}
                                <span class="text-xs text-gray-400">({{ $showInches ? 'hh:mm AM/PM' : '24h' }}, {{ __('local time at the location') }})</span>
                            </label>
                            @php
                                $observedTime24 = '';
                                if (!empty($oldObservedTime)) {
                                    try {
                                        $observedTime24 = \Carbon\Carbon::parse($oldObservedTime)->format('H:i');
                                    } catch (\Throwable $_) {
                                    }
                                }
                            @endphp
                            {{-- The scroll wheel of WireUI on touch screens, a Flatpickr field to type in elsewhere; the disabled fieldset is not sent --}}
                            <fieldset id="observed_time_touch" disabled hidden>
                                @if($showInches)
                                    <x-time-picker name="observed_time" without-seconds="true" value="{{ old('observed_time', $prefill['observed_time'] ?? '') }}" class="w-full" />
                                @else
                                    <x-time-picker name="observed_time" military-time="true" without-seconds="true" value="{{ old('observed_time', $prefill['observed_time'] ?? '') }}" class="w-full" />
                                @endif
                            </fieldset>
                            <fieldset id="observed_time_desktop">
                                <input type="text" id="observed_time_input" name="observed_time" value="{{ $observedTime24 }}"
                                    placeholder="{{ $showInches ? 'hh:mm AM' : 'hh:mm' }}" autocomplete="off"
                                    class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100" />
                            </fieldset>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    var touch = document.getElementById('observed_time_touch');
                                    var desktop = document.getElementById('observed_time_desktop');
                                    if (window.matchMedia('(pointer: coarse)').matches || typeof window.flatpickr !== 'function') {
                                        desktop.disabled = true;
                                        desktop.hidden = true;
                                        touch.disabled = false;
                                        touch.hidden = false;
                                        return;
                                    }
                                    var showInches = @json((bool) $showInches);
                                    window.flatpickr(document.getElementById('observed_time_input'), {
                                        enableTime: true,
                                        noCalendar: true,
                                        allowInput: true,
                                        dateFormat: 'H:i',
                                        altInput: showInches,
                                        altFormat: 'h:i K',
                                        altInputClass: 'w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100',
                                        time_24hr: !showInches,
                                        minuteIncrement: 1,
                                    });
                                });
                            </script>
                        </div>
                    <div data-dsl-field="location">
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Location') }}</label>
                        <x-select
                            placeholder="{{ __('Select location') }}"
                            value="{{ old('location_id', $prefill['location_id'] ?? (auth()->check() ? auth()->user()->stdlocation : '')) }}"
                            class="w-full"
                               x-on:selected="document.getElementById('location_hidden').value = $event.detail.value; if (window.__dsl_emit) { window.__dsl_emit('locationSelected', $event.detail.value) } else { window.__dsl_pending_event = { name: 'locationSelected', payload: $event.detail.value } } ; if (window.updateObservationConditionsFromLocation) { window.updateObservationConditionsFromLocation($event.detail.value) } else { window.__dsl_pending_location = $event.detail.value }"
                               x-on:clear="document.getElementById('location_hidden').value = ''; if (window.__dsl_emit) { window.__dsl_emit('locationSelected', '') } else { window.__dsl_pending_event = { name: 'locationSelected', payload: '' } } ; if (window.updateObservationConditionsFromLocation) { window.updateObservationConditionsFromLocation('') } else { window.__dsl_pending_location = '' }"
                        >
                            <x-select.option label="{{ __('Select location') }}" value="" />
                            @foreach(($locations ?? collect()) as $loc)
                                <x-select.option label="{{ $loc->name }}" value="{{ $loc->id }}" />
                            @endforeach
                        </x-select>
                        <input type="hidden" id="location_hidden" name="location_id" value="{{ old('location_id', $prefill['location_id'] ?? (auth()->check() ? auth()->user()->stdlocation : '')) }}" />
                        @php
                            $__stdLocId = old('location_id', $prefill['location_id'] ?? (auth()->check() ? auth()->user()->stdlocation : ''));
                            $__stdLoc   = collect($locations ?? [])->firstWhere('id', $__stdLocId);
                        @endphp
                        @if($__stdLoc)
                            <div hidden data-dsl-label="{{ $__stdLoc->name }}" data-dsl-value="{{ $__stdLoc->id }}"></div>
                        @endif
                        @php
                            $__dsl_locations = [];
                            $__dsl_fst = auth()->check() ? (float) (auth()->user()->fstOffset ?? 0) : 0;
                            foreach (($locations ?? collect()) as $__loc) {
                                    $__dsl_locations[$__loc->id] = [
                                        'sqm' => isset($__loc->skyBackground) && $__loc->skyBackground > 0 ? (float) $__loc->skyBackground : null,
                                        // The NELM of the observer: the location stores it without the fstOffset
                                        'limmag' => isset($__loc->limitingMagnitude) && $__loc->limitingMagnitude > 0 ? (float) $__loc->getNelm($__dsl_fst) : null,
                                        'bortle' => $__loc->getBortle() !== null ? (int) $__loc->getBortle() : null,
                                    ];
                            }
                        @endphp
                        <script>
                            window.__dsl_locations = @json($__dsl_locations);
                            window.__dsl_fst_offset = {{ auth()->check() ? (float) (auth()->user()->fstOffset ?? 0) : 0 }};
                            window.__dsl_emit = function(eventName, payload) {
                                try {
                                    if (window.Livewire && typeof window.Livewire.emit === 'function') {
                                        return window.Livewire.emit(eventName, payload);
                                    }
                                    if (window.livewire && typeof window.livewire.emit === 'function') {
                                        return window.livewire.emit(eventName, payload);
                                    }
                                    if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                                        return window.Livewire.dispatch(eventName, payload);
                                    }
                                    if (window.livewire && typeof window.livewire.dispatch === 'function') {
                                        return window.livewire.dispatch(eventName, payload);
                                    }
                                } catch (e) {
                                    // ignore
                                }
                                // fallback: store pending event to be flushed later
                                window.__dsl_pending_event = { name: eventName, payload: payload };
                                return false;
                            };
                            // Ensure the location x-select shows the selected label when server-provided
                            (function preSelectLocation() {
                                var attempts = 0;
                                var maxAttempts = 50;

                                function trySet() {
                                    var wrapper = document.querySelector('[data-dsl-field="location"]');
                                    if (!wrapper) return;

                                    var hint = wrapper.querySelector('[data-dsl-label]');
                                    if (!hint) return;

                                    var label = hint.getAttribute('data-dsl-label');
                                    var value = hint.getAttribute('data-dsl-value');
                                    if (!label || !value) return;

                                    var alpineEl = wrapper.querySelector('[x-data]');
                                    if (!alpineEl) { if (++attempts < maxAttempts) setTimeout(trySet, 100); return; }

                                    var data = null;
                                    try {
                                        if (window.Alpine && typeof window.Alpine.$data === 'function') {
                                            data = window.Alpine.$data(alpineEl);
                                        }
                                    } catch (e) {}
                                    if (!data && alpineEl._x_dataStack) { data = alpineEl._x_dataStack[0]; }
                                    if (!data) { if (++attempts < maxAttempts) setTimeout(trySet, 100); return; }

                                    if (data.selected && data.selected.value != null) return;
                                    data.selected = { label: label, value: value, isSelected: true };

                                    // Notify other page scripts / Livewire about this pre-selected location
                                    try {
                                        if (window.__dsl_emit) {
                                            window.__dsl_emit('locationSelected', value);
                                        } else {
                                            window.__dsl_pending_event = { name: 'locationSelected', payload: value };
                                        }
                                    } catch (e) {
                                        // ignore
                                    }

                                    try {
                                        if (window.updateObservationConditionsFromLocation) {
                                            window.updateObservationConditionsFromLocation(value);
                                        } else {
                                            window.__dsl_pending_location = value;
                                        }
                                    } catch (e) {
                                        // ignore
                                    }
                                }

                                document.addEventListener('alpine:initialized', function() { setTimeout(trySet, 0); });
                                setTimeout(trySet, 200);
                            })();
                        </script>
                    </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">{{ __('Filter') }}</label>
                            <x-select
                                :async-data="route('filter.select.api')"
                                option-label="name"
                                option-value="id"
                                value="{{ old('filter_id', $prefill['filter_id'] ?? '') }}"
                                placeholder="{{ __('Select filter') }}"
                                class="w-full"
                                x-on:selected="document.getElementById('filter_hidden').value = $event.detail.value"
                                x-on:clear="document.getElementById('filter_hidden').value = ''"
                            />
                            <input type="hidden" id="filter_hidden" name="filter_id" value="{{ old('filter_id', $prefill['filter_id'] ?? '') }}" />
                        </div>
                        @php
                            $pickerInstrumentId = old('instrument_id', $prefill['instrument_id'] ?? (auth()->check() ? (auth()->user()->stdinstrument ?? auth()->user()->stdtelescope ?? '') : ''));
                            // An observation being copied or edited keeps its own lens (also none);
                            // a new one starts with the lens that is always used with the instrument.
                            $pickerLensId = array_key_exists('lens_id', $prefill ?? [])
                                ? ($prefill['lens_id'] ?? '')
                                : ($pickerInstrumentId ? \App\Models\Instrument::where('user_id', auth()->id())->whereKey($pickerInstrumentId)->value('lens_id') : '');
                        @endphp
                        @livewire('magnification-picker', [
                            'instrument_id' => $pickerInstrumentId,
                            'eyepiece_id' => old('eyepiece_id', $prefill['eyepiece_id'] ?? (auth()->check() ? auth()->user()->stdeyepiece : '')),
                            'lens_id' => old('lens_id', $pickerLensId ?? ''),
                            'manual_magnification' => old('magnification', $prefill['magnification'] ?? null),
                            'instrumentSetId' => $instrumentSet ? $instrumentSet->id : null
                        ])
                    </div>

                    <div class="mt-4" x-data="{ observation_language: @js(old('language', auth()->check() ? auth()->user()->observationlanguage : '')) }">
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Description') }} <span class="text-red-400">*</span></label>
                        <textarea id="description" name="description" rows="6" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">{{ old('description') }}</textarea>
                    </div>

                    {{-- The description written by the observer in other languages of the site (#321),
                         shown instead of Google's translation to those who read the site in that language --}}
                    @php
                        $mainLanguage = old('language', auth()->check() ? auth()->user()->observationlanguage : '');
                        $oldTranslations = array_filter((array) old('translations', []), fn ($text) => ! \App\Support\Html::isBlank($text));
                        $translationLanguages = [];
                        foreach (array_values(config('app.available_locales', [])) as $code) {
                            if ($code !== $mainLanguage) {
                                try {
                                    $translationLanguages[$code] = \Languages::lookup([$code], app()->getLocale())[$code] ?? $code;
                                } catch (\Throwable $e) {
                                    $translationLanguages[$code] = $code;
                                }
                            }
                        }
                    @endphp
                    <details id="observation-translations" class="mt-4 rounded border border-gray-700 px-3 py-2" @if ($oldTranslations !== []) open @endif>
                        <summary class="cursor-pointer text-sm text-gray-300">
                            {{ __('Description in other languages') }}
                            @if ($oldTranslations !== [])
                                <span class="text-xs text-gray-400">({{ implode(', ', array_intersect_key($translationLanguages, $oldTranslations)) }})</span>
                            @endif
                        </summary>
                        <p class="mt-2 text-xs text-gray-400">{{ __('Optional, for instance for your best observations. Readers of DeepskyLog in that language see your own text instead of the translation by Google. Leave a language empty to let Google translate it.') }}</p>
                        @foreach ($translationLanguages as $code => $languageName)
                            <div class="mt-3">
                                <label for="translation_{{ $code }}" class="block text-xs text-gray-400 mb-1">{{ $languageName }}</label>
                                <textarea id="translation_{{ $code }}" name="translations[{{ $code }}]" rows="4" class="observation-translation w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">{{ old('translations.' . $code) }}</textarea>
                            </div>
                        @endforeach
                        @error('translations.*')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </details>

                    {{-- The observer's own grade of the observation (#321) --}}
                    <fieldset class="mt-4">
                        <legend class="block text-xs text-gray-400 mb-1">{{ __('Class of this observation') }}</legend>
                        <div class="flex flex-col gap-1">
                            @foreach (\App\Models\Observation::observerClassDescriptions() as $observerClass => $observerClassDescription)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="observer_class" value="{{ $observerClass }}" @checked(old('observer_class') === $observerClass) />
                                    <x-observation-class :value="$observerClass" />
                                    {{ __($observerClassDescription) }}
                                </label>
                            @endforeach
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="observer_class" value="" @checked(! in_array(old('observer_class'), \App\Models\Observation::OBSERVER_CLASSES, true)) />
                                {{ __('Not graded') }}
                            </label>
                        </div>
                        @error('observer_class')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    @if ($resolved['type'] === 'deepsky' && ! old('edit_unified_id') && auth()->check())
                        {{-- The other objects seen in the same field get an observation of their own
                             with the same date, equipment, conditions and description (#831) --}}
                        @php
                            $oldAlsoSeen = array_map('mb_strtolower', (array) old('also_seen', []));
                            $oldAlsoSeenMore = json_decode((string) old('also_seen_more', ''), true);
                            $oldAlsoSeenMore = is_array($oldAlsoSeenMore) ? array_values(array_filter($oldAlsoSeenMore, 'is_string')) : [];
                        @endphp
                        <div class="mt-4">
                            <label class="block text-xs text-gray-400 mb-1">{{ __('Also seen in the same field') }}</label>
                            <p class="text-xs text-gray-400 mb-2">{{ __('Each object you choose gets an observation of its own, with the same date, equipment, conditions and description. The drawing and the details of the object stay with this observation.') }}</p>
                            @if (! empty($sameFieldObjects))
                                <div class="mb-2 flex flex-wrap gap-x-4 gap-y-1">
                                    @foreach ($sameFieldObjects as $sameFieldObject)
                                        <label class="inline-flex items-center gap-2 text-sm">
                                            <input type="checkbox" name="also_seen[]" value="{{ $sameFieldObject['name'] }}" class="rounded" @checked(in_array(mb_strtolower($sameFieldObject['name']), $oldAlsoSeen, true)) />
                                            {{ $sameFieldObject['name'] }}
                                            <span class="text-xs text-gray-400">{{ $sameFieldObject['type'] }}, {{ number_format($sameFieldObject['separation'], 1) }}'</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                            <div x-data="{ alsoSeenMore: @js($oldAlsoSeenMore) }">
                                <x-select
                                    name="also_seen_more"
                                    async-data="{{ route('api.objects.search', $oldAlsoSeenMore !== [] ? ['selected' => $oldAlsoSeenMore] : []) }}"
                                    x-model="alsoSeenMore"
                                    option-label="label"
                                    option-value="value"
                                    multiselect
                                    placeholder="{{ __('Other objects') }}"
                                    class="w-full"
                                />
                            </div>
                            @error('also_seen')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="mt-4">
                        <label class="block text-xs text-gray-400 mb-1">{{ __('Standard language for this observation') }}</label>
                        <x-select
                            x-model="observation_language"
                            :async-data="route('observation_languages.index', ['selected' => old('language', auth()->check() ? auth()->user()->observationlanguage : '')])"
                            option-label="name"
                            option-value="id"
                            value="{{ old('language', auth()->check() ? auth()->user()->observationlanguage : '') }}"
                            placeholder="{{ __('Select language') }}"
                            class="w-full"
                            x-on:selected="(function(v){try{if(typeof v === 'object'){document.getElementById('language_hidden').value = v.id ?? v.value ?? ''; } else { document.getElementById('language_hidden').value = v ?? ''; }}catch(e){ document.getElementById('language_hidden').value = v ?? ''; } })($event.detail.value)"
                            x-on:clear="document.getElementById('language_hidden').value = ''"
                        >
                            @if(auth()->check() && !empty(auth()->user()->observationlanguage))
                                <x-select.option label="{{ Languages::lookup([auth()->user()->observationlanguage], 'mixed')->values()[0] }}" value="{{ auth()->user()->observationlanguage }}" />
                            @endif
                        </x-select>
                        <input type="hidden" id="language_hidden" name="language" value="{{ old('language', auth()->check() ? auth()->user()->observationlanguage : '') }}" />
                    </div>

                    @if(!empty($resolved['type']) && view()->exists('observations.partials._form_'.$resolved['type']))
                        @include('observations.partials._form_'.$resolved['type'])
                    @endif

                    @if ($errors->has('below_horizon'))
                        <div class="mt-4 p-3 rounded bg-yellow-900 text-yellow-100 text-sm">
                            <p>{{ $errors->first('below_horizon') }}</p>
                            <label class="mt-2 inline-flex items-center gap-2">
                                <input type="checkbox" name="confirm_below_horizon" value="1" class="rounded" />
                                {{ __('The observation is correct, save it anyway') }}
                            </label>
                        </div>
                    @endif

                    <div class="mt-4">
                        <button type="submit" class="bg-indigo-600 px-4 py-2 rounded text-white">{{ __('Save observation') }}</button>
                        <a href="{{ route('observation.add.select') }}" class="ml-2 text-gray-300">{{ __('Cancel') }}</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>

{{-- Date/time pickers use WireUI components; no inline conversion scripts needed. --}}

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initializeTinyMCE() {
            if (typeof tinymce === 'undefined') return;
            var el = document.querySelector('#description');
            if (!el) return;

            if (tinymce.get('description')) {
                tinymce.get('description').remove();
            }

            tinymce.init({
                selector: '#description',
                plugins: 'lists emoticons quickbars wordcount',
                toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | emoticons | wordcount',
                menubar: false,
                license_key: 'gpl',
                quickbars_insert_toolbar: false,
                quickbars_image_toolbar: false,
                quickbars_selection_toolbar: 'bold italic',
                skin: 'oxide-dark',
                content_css: 'dark',
                setup: function (editor) {
                    editor.on('init', function () {
                        editor.save();
                    });
                    editor.on('change', function () {
                        editor.save();
                    });
                }
            });
        }

        // The descriptions in other languages get their editors when their section is first opened
        function initializeTranslationEditors() {
            var section = document.getElementById('observation-translations');
            if (typeof tinymce === 'undefined' || !section || section.dataset.editors) return;
            section.dataset.editors = '1';
            tinymce.init({
                selector: 'textarea.observation-translation',
                plugins: 'lists emoticons quickbars wordcount',
                toolbar: 'undo redo | bold italic | bullist numlist | emoticons',
                menubar: false,
                license_key: 'gpl',
                height: 220,
                quickbars_insert_toolbar: false,
                quickbars_image_toolbar: false,
                quickbars_selection_toolbar: 'bold italic',
                skin: 'oxide-dark',
                content_css: 'dark',
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });
                }
            });
        }
        var translationSection = document.getElementById('observation-translations');
        if (translationSection) {
            translationSection.addEventListener('toggle', function () {
                if (translationSection.open) initializeTranslationEditors();
            });
        }

        if (typeof tinymce !== 'undefined' && document.querySelector('#description')) {
            initializeTinyMCE();
            if (translationSection && translationSection.open) initializeTranslationEditors();
        } else {
            var retryCount = 0;
            var retryMax = 20;
            var retryInterval = setInterval(function () {
                retryCount++;
                if (typeof tinymce !== 'undefined' && document.querySelector('#description')) {
                    initializeTinyMCE();
                    if (translationSection && translationSection.open) initializeTranslationEditors();
                    clearInterval(retryInterval);
                } else if (retryCount >= retryMax) {
                    clearInterval(retryInterval);
                }
            }, 250);
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var maxBytes = {{ isset($php_upload_max_bytes) ? (int) $php_upload_max_bytes : 0 }};
        var maxLabel = '{{ $php_upload_max_filesize ?? '' }}';

        // Attach to any sketch file inputs rendered by the partials
        var inputs = document.querySelectorAll('input[type=file][name=sketch]');
        inputs.forEach(function (input) {
            input.addEventListener('change', function () {
                var file = input.files && input.files[0];
                var existing = document.getElementById('sketch_size_warning');
                if (existing) existing.remove();
                if (!file) return;
                if (maxBytes > 0 && file.size > maxBytes) {
                    var msg = '{{ __('Selected sketch is too large') }} (' + Math.round(file.size/1024) + ' KB). {{ __('Maximum allowed by server is') }} ' + maxLabel + '.';
                    var div = document.createElement('div');
                    div.id = 'sketch_size_warning';
                    div.className = 'mt-2 text-sm text-red-300';
                    div.textContent = msg;
                    input.parentNode.insertBefore(div, input.nextSibling);
                    var btn = document.querySelector('form button[type=submit]');
                    if (btn) btn.disabled = true;
                } else {
                    var btn = document.querySelector('form button[type=submit]');
                    if (btn) btn.disabled = false;
                }
            });
        });

        var form = document.querySelector('form[method="POST"][enctype="multipart/form-data"]');
        if (form) {
            form.addEventListener('submit', function (e) {
                var input = document.querySelector('input[type=file][name=sketch]');
                if (input && input.files && input.files[0]) {
                    var file = input.files[0];
                    if (maxBytes > 0 && file.size > maxBytes) {
                        e.preventDefault();
                        alert('{{ __('The selected sketch is larger than the server upload limit (:limit). Please choose a smaller file.', ['limit' => $php_upload_max_filesize ?? '']) }}');
                    }
                }
            });
        }
    });
</script>
