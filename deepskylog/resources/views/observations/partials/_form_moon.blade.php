<div class="mt-4">
    <h3 class="text-sm text-gray-300 mb-2">{{ __('Observation details') }}</h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

        <div></div>
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
    
    <div class="mt-4">
        @livewire('observation-conditions', ['initialSqm' => old('sqm', ''), 'initialNelm' => old('limiting_magnitude', ''), 'initialBortle' => old('bortle', '')])
    </div>
</div>
