@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-semibold text-gray-200">{{ __('Object details') }}: <span class="text-blue-400">{{ $name }}</span></h1>
    <p class="text-sm text-gray-500 mt-1">{{ __('Step 3 of 3 — Fill in the fields below, or fetch them from SIMBAD.') }}</p>

    <form action="{{ route('object.store') }}" method="POST" class="mt-6 max-w-3xl">
        @csrf
        <input type="hidden" name="name" value="{{ $name }}" />

        @if ($errors->any())
            <div class="mb-4 rounded bg-red-900 p-3 text-red-100">
                <strong>{{ __('Please fix the following errors:') }}</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded bg-red-900 p-3 text-red-100">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400">{{ __('RA') }}</label>
                <div class="mt-1 px-3 py-2 rounded border border-gray-700 bg-gray-800 text-gray-200 text-sm">
                    {{ $ra ?? '—' }}
                </div>
                <input type="hidden" name="ra" value="{{ old('ra', $ra ?? '') }}" />
            </div>
            <div>
                <label class="block text-sm text-gray-400">{{ __('Decl') }}</label>
                <div class="mt-1 px-3 py-2 rounded border border-gray-700 bg-gray-800 text-gray-200 text-sm">
                    {{ $decl ?? '—' }}
                </div>
                <input type="hidden" name="decl" value="{{ old('decl', $decl ?? '') }}" />
            </div>
            <div>
                <label class="block text-sm text-gray-400">{{ __('Constellation') }}</label>
                @if($constellation)
                    <div class="mt-1 px-3 py-2 rounded border border-gray-700 bg-gray-800 text-gray-200 text-sm">
                        {{ $constellation }} — {{ $constellationName }}
                    </div>
                @else
                    <div class="mt-1 px-3 py-2 rounded border border-gray-700 bg-gray-800 text-gray-500 text-sm italic">
                        {{ __('Will be calculated automatically from RA/Dec') }}
                    </div>
                @endif
                @error('con') <div class="text-sm text-red-400 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400">{{ __('Type') }}</label>
                <select name="type"
                    class="mt-1 block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">--</option>
                    @foreach($types as $t)
                        <option value="{{ $t->code }}" @if(old('type') == $t->code) selected @endif>{{ $t->name }}</option>
                    @endforeach
                </select>
                @error('type') <div class="text-sm text-red-400 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400">{{ __('Magnitude') }}</label>
                <input type="text" name="mag" value="{{ old('mag') }}"
                    placeholder="e.g. 8.4"
                    class="mt-1 block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-500" />
                @error('mag') <div class="text-sm text-red-400 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400">{{ __('Surface brightness') }}</label>
                <input type="text" name="subr" value="{{ old('subr') }}"
                    placeholder="e.g. 11.9"
                    class="mt-1 block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-500" />
                @error('subr') <div class="text-sm text-red-400 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400">{{ __('Size (major, arcminutes)') }}</label>
                <input type="text" name="diam1" value="{{ old('diam1') }}"
                    placeholder="e.g. 7.0"
                    class="mt-1 block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-500" />
                @error('diam1') <div class="text-sm text-red-400 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400">{{ __('Size (minor, arcminutes)') }}</label>
                <input type="text" name="diam2" value="{{ old('diam2') }}"
                    placeholder="e.g. 5.0"
                    class="mt-1 block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-500" />
                @error('diam2') <div class="text-sm text-red-400 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400">{{ __('Position angle (°)') }}</label>
                <input type="text" name="pa" value="{{ old('pa') }}"
                    placeholder="e.g. 135"
                    class="mt-1 block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-500" />
                @error('pa') <div class="text-sm text-red-400 mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-6">
            <div class="flex items-center gap-4 flex-wrap">
                <button type="button" id="simbadFill"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2.5 rounded shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 8l-3-3m3 3l3-3" />
                    </svg>
                    {{ __('Fetch details from SIMBAD') }}
                </button>
                <span id="simbadStatus" class="text-sm text-gray-400"></span>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3 flex-wrap">
            <div class="flex-1"></div>
            <a href="{{ url()->previous() }}"
                class="bg-gray-600 text-white px-5 py-2 rounded hover:bg-gray-500 font-medium">
                {{ __('Back') }}
            </a>
            <button type="submit"
                class="bg-green-700 text-white px-5 py-2 rounded hover:bg-green-600 font-medium">
                {{ __('Add object') }}
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('simbadFill').addEventListener('click', function() {
    const name = @json($name ?? '');
    if (!name) {
        const status = document.getElementById('simbadStatus');
        status.textContent = '{{ __('No object name available.') }}';
        status.className = 'text-sm text-red-400';
        return;
    }
    const btn = this;
    const status = document.getElementById('simbadStatus');
    btn.disabled = true;
    status.textContent = '{{ __('Looking up SIMBAD…') }}';
    status.className = 'text-sm text-yellow-400';

    fetch('{{ route('api.objects.simbad-lookup') }}?name=' + encodeURIComponent(name), {credentials: 'same-origin'})
        .then(r => r.json())
        .then(json => {
            btn.disabled = false;
            if (json.success && json.data) {
                const d = json.data;
                const filled = [];

                // Always update hidden RA/Dec inputs (used on form submit)
                if (d.ra != null) document.querySelector('input[name="ra"]').value = d.ra;
                if (d.decl != null) document.querySelector('input[name="decl"]').value = d.decl;

                // Fill visible inputs
                if (d.mag != null) {
                    document.querySelector('input[name="mag"]').value = d.mag;
                    filled.push('{{ __('Magnitude') }}');
                }
                if (d.type_code) {
                    const sel = document.querySelector('select[name="type"]');
                    if (sel) {
                        sel.value = d.type_code;
                        if (sel.value === d.type_code) filled.push('{{ __('Type') }}');
                    }
                }
                if (d.diam1 != null) {
                    document.querySelector('input[name="diam1"]').value = d.diam1;
                    filled.push('{{ __('Size (major)') }}');
                }
                if (d.diam2 != null) {
                    document.querySelector('input[name="diam2"]').value = d.diam2;
                    filled.push('{{ __('Size (minor)') }}');
                }
                if (d.pa != null) {
                    document.querySelector('input[name="pa"]').value = d.pa;
                    filled.push('{{ __('Position angle') }}');
                }
                if (d.subr != null) {
                    document.querySelector('input[name="subr"]').value = d.subr;
                    filled.push('{{ __('Surface brightness') }}');
                }

                if (filled.length > 0) {
                    status.textContent = '{{ __('Filled from SIMBAD') }}: ' + filled.join(', ') + '. {{ __('Constellation will be recalculated on save.') }}';
                } else {
                    status.textContent = '{{ __('SIMBAD data applied. Note: constellation will be recalculated on save.') }}';
                }
                status.className = 'text-sm text-green-400';
            } else {
                status.textContent = json.error || '{{ __('No SIMBAD data found.') }}';
                status.className = 'text-sm text-red-400';
            }
        })
        .catch(() => {
            btn.disabled = false;
            status.textContent = '{{ __('SIMBAD lookup failed.') }}';
            status.className = 'text-sm text-red-400';
        });
});


</script>
@endsection

