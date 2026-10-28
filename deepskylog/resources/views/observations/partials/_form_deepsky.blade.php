<div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <h3 class="text-sm text-gray-300 mb-2">{{ __('Sky quality') }}</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">{{ __('Seeing') }}</label>
                <select name="seeing" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">
                    <option value="">{{ __('Select seeing') }}</option>
                    @for($i=1;$i<=5;$i++)
                        <option value="{{ $i }}" @if(old('seeing') == $i) selected @endif>{{ __('observations.seeing.' . $i) }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-400 mb-1">{{ __('Transparency') }}</label>
                <select name="transparency" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">
                    <option value="">{{ __('Select transparency') }}</option>
                    @for($i=1;$i<=5;$i++)
                        <option value="{{ $i }}" @if(old('transparency') == $i) selected @endif>{{ __('observations.transparency.' . $i) }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="mt-4">
            @livewire('observation-conditions', ['initialSqm' => old('sqm', ''), 'initialNelm' => old('limiting_magnitude', ''), 'initialBortle' => old('bortle', '')])
        </div>

        <div class="mt-3">
            <label class="block text-xs text-gray-400 mb-1">{{ __('Sketch / Drawing') }}</label>
            <div class="flex items-start gap-4">
                <div>
                    <input id="sketch_input" type="file" name="sketch" accept="image/*" class="block w-full text-sm text-gray-400 file:rounded file:px-3 file:py-2 file:bg-gray-700 file:text-white" />
                </div>
                <div>
                    <img id="sketch_preview" src="" alt="{{ __('Sketch preview') }}" class="hidden w-48 rounded border border-gray-700" />
                </div>
            </div>
            <script>
                (function(){
                    const input = document.getElementById('sketch_input');
                    const preview = document.getElementById('sketch_preview');
                    if (!input) return;
                    input.addEventListener('change', function(e){
                        const f = this.files && this.files[0];
                        if (!f) { preview.src = ''; preview.classList.add('hidden'); return; }
                        try {
                            const url = URL.createObjectURL(f);
                            preview.src = url;
                            preview.classList.remove('hidden');
                        } catch (err) {
                            // ignore preview errors
                        }
                    });
                })();
            </script>
        </div>

        <div class="mt-6">
            <h3 class="text-sm text-gray-300 mb-2">{{ __('Object details') }}</h3>

            @php
                $typeRaw = strtoupper(trim((string) ($objectSummary['type_raw'] ?? '')));
                $resolvedTypeRaw = strtoupper(trim((string) ($resolved['record']->type ?? '')));
                $isOpenCluster = in_array($typeRaw, ['OPNCL', 'LMCOC', 'SMCOC']);
                $isGlobular = in_array($typeRaw, ['GLOCL', 'GXAGC', 'LMCGC', 'SMCGC']);
                $isPlanetary = in_array($typeRaw, ['PLNNB']);
                $isDark = in_array($typeRaw, ['DRKNB']);
                // Detect doubles from the summary `type_raw` or from the resolved object's `type` field
                $isDouble = in_array($typeRaw, ['DS']) || ($resolvedTypeRaw === 'DS');
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">{{ __('Object visibility') }}</label>
                    <select name="visibility" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">
                        <option value="">{{ __('Select visibility') }}</option>
                        @if($isOpenCluster)
                            @for($i=1;$i<=7;$i++)
                                <option value="{{ $i }}" @if(old('visibility') == $i) selected @endif>{{ __('observations.visibility_oc.' . $i) }}</option>
                            @endfor
                        @elseif($isDouble)
                            @for($i=1;$i<=3;$i++)
                                <option value="{{ $i }}" @if(old('visibility') == $i) selected @endif>{{ __('observations.visibility_ds.' . $i) }}</option>
                            @endfor
                        @else
                            @for($i=1;$i<=7;$i++)
                                <option value="{{ $i }}" @if(old('visibility') == $i) selected @endif>{{ __('observations.visibility.' . $i) }}</option>
                            @endfor
                        @endif
                    </select>
                </div>

                <div></div>
            </div>

            @unless($isDouble)
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">{{ __("Estimated large diameter (')") }}</label>
                    <input name="large_diameter" step="0.1" type="number" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100" value="{{ old('large_diameter', '') }}" />
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">{{ __("Estimated small diameter (')") }}</label>
                    <input name="small_diameter" step="0.1" type="number" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100" value="{{ old('small_diameter', '') }}" />
                </div>
            </div>
            @endunless

            @if(!empty($isOpenCluster))
                <div class="mt-3">
                    <label class="block text-xs text-gray-400 mb-1">{{ __('Cluster type') }}</label>
                    <select name="cluster_type" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">
                        <option value="">{{ __('Select cluster type') }}</option>
                        @foreach(__('observations.cluster_types') as $code => $label)
                            <option value="{{ $code }}" @if(old('cluster_type') == $code) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="mt-4">
                <label class="block text-xs text-gray-400 mb-2">{{ __('Characteristics') }}</label>

                {{-- Stellar / Extended should be mutually exclusive: render as radios and map to boolean fields expected by backend --}}
                @unless($isDouble)
                <div class="flex items-center gap-6 mb-3">
                    @php
                        $stellarChecked = (int) old('stellar', 0) === 1;
                        $extendedChecked = (int) old('extended', 0) === 1;
                    @endphp

                    <label class="inline-flex items-center text-sm text-gray-300">
                        <input type="radio" name="structure_choice" value="stellar" class="mr-2" @if($stellarChecked) checked @endif />
                        {{ __('Stellar') }}
                    </label>

                    <label class="inline-flex items-center text-sm text-gray-300">
                        <input type="radio" name="structure_choice" value="extended" class="mr-2" @if($extendedChecked) checked @endif />
                        {{ __('Extended') }}
                    </label>

                    <label class="inline-flex items-center text-sm text-gray-300">
                        <input type="radio" name="structure_choice" value="" class="mr-2" @if(!($stellarChecked || $extendedChecked)) checked @endif />
                        {{ __('None') }}
                    </label>
                </div>

                <div id="structure-hidden-fields">
                    @if($stellarChecked)
                        <input type="hidden" name="stellar" value="1" />
                    @elseif($extendedChecked)
                        <input type="hidden" name="extended" value="1" />
                    @endif
                </div>
                @endunless

                <div class="flex flex-wrap gap-4">
                    @php
                        if ($isOpenCluster || $isGlobular) {
                            $flags = [
                                'resolved' => __('Resolved'),
                                'mottled' => __('Mottled'),
                                'unusual_shape' => __('Unusual shape'),
                                'partly_unresolved' => __('Partly unresolved'),
                                'color_contrasts' => __('Color contrasts'),
                            ];
                        } elseif ($isDouble) {
                            $flags = [
                                'equal_brightness' => __('Equal brightness'),
                                'nice_field' => __('Nice field'),
                            ];
                        } else {
                            $flags = [
                                'mottled' => __('Mottled'),
                            ];
                        }
                    @endphp

                    @foreach($flags as $key => $label)
                        <label class="inline-flex items-center text-sm text-gray-300">
                            <input type="checkbox" name="{{ $key }}" value="1" @if(old($key)) checked @endif class="mr-2" />
                            {{ $label }}
                        </label>
                    @endforeach

                    @if($isDouble)
                        <div class="w-full mt-2 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">{{ __('Component 1') }}</label>
                                <select name="component1" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">
                                    <option value="">{{ __('Select color') }}</option>
                                    @foreach(['1' => 'white', '2' => 'red', '3' => 'orange', '4' => 'yellow', '5' => 'green', '6' => 'blue'] as $val => $color)
                                        <option value="{{ $val }}" @if(old('component1') == $val) selected @endif>{{ __(ucfirst($color)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">{{ __('Component 2') }}</label>
                                <select name="component2" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">
                                    <option value="">{{ __('Select color') }}</option>
                                    @foreach(['1' => 'white', '2' => 'red', '3' => 'orange', '4' => 'yellow', '5' => 'green', '6' => 'blue'] as $val => $color)
                                        <option value="{{ $val }}" @if(old('component2') == $val) selected @endif>{{ __(ucfirst($color)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </div>

                <script>
                    (function() {
                        var container = document.getElementById('structure-hidden-fields');
                        if (!container) return;
                        var radios = document.querySelectorAll('input[name="structure_choice"]');
                        function updateHidden() {
                            container.innerHTML = '';
                            var sel = document.querySelector('input[name="structure_choice"]:checked');
                            if (!sel) return;
                            if (sel.value === 'stellar') {
                                var i = document.createElement('input'); i.type = 'hidden'; i.name = 'stellar'; i.value = '1'; container.appendChild(i);
                            } else if (sel.value === 'extended') {
                                var j = document.createElement('input'); j.type = 'hidden'; j.name = 'extended'; j.value = '1'; container.appendChild(j);
                            }
                        }
                        radios.forEach(function(r) { r.addEventListener('change', updateHidden); });
                        // initialize
                        updateHidden();
                    })();
                </script>
            </div>
        </div>

        <div></div>
    </div>
</div>
