@extends('layouts.app')

@section('content')
<div class="p-6 max-w-2xl">
    <h1 class="text-2xl font-semibold text-gray-200">{{ __('Enter coordinates for') }}: <span class="text-white">{{ $name ?? '' }}</span></h1>

    <form action="{{ route('object.checkCoords') }}" method="POST" class="mt-6">
        @csrf
        <input type="hidden" name="name" value="{{ $name }}" />

        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-400 mb-1">{{ __('Right Ascension') }}</label>
            <input type="text" name="ra" id="raInput"
                   placeholder="e.g. 05 35 17  or  5.588"
                   class="block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-500" />
            <p class="mt-1 text-xs text-gray-500">{{ __('Format: HH MM SS.s  or decimal hours') }}</p>
        </div>

        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-400 mb-1">{{ __('Declination') }}</label>
            <input type="text" name="decl" id="declInput"
                   placeholder="e.g. -05 23 28  or  -5.391"
                   class="block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-500" />
            <p class="mt-1 text-xs text-gray-500">{{ __('Format: ±DD MM SS.s  or decimal degrees') }}</p>
        </div>

        <div class="mb-6">
            <button type="button" id="simbadBtn"
                    class="inline-flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                {{ __('Get coordinates from SIMBAD') }}
            </button>
            <span id="simbadStatus" class="ml-3 text-sm text-gray-400 hidden"></span>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="bg-green-700 text-white px-5 py-2 rounded hover:bg-green-600 font-medium">
                {{ __('Check the coordinates') }}
            </button>
            <a href="{{ route('object.create') }}"
               class="bg-gray-600 text-white px-5 py-2 rounded hover:bg-gray-500 font-medium">
                {{ __('Back') }}
            </a>
        </div>
    </form>
</div>

<script>
document.getElementById('simbadBtn').addEventListener('click', function () {
    const btn    = this;
    const status = document.getElementById('simbadStatus');
    const name   = "{{ addslashes($name ?? '') }}";
    if (!name) return alert('No name provided');

    btn.disabled = true;
    status.textContent = '{{ __('Looking up…') }}';
    status.classList.remove('hidden', 'text-red-400');
    status.classList.add('text-gray-400');

    fetch('{{ route('api.objects.simbad-lookup') }}?name=' + encodeURIComponent(name), { credentials: 'same-origin' })
        .then(r => r.json())
        .then(json => {
            if (json.success && json.data) {
                let raVal = json.data.ra;
                if (raVal !== undefined && raVal !== null) {
                    if (Math.abs(raVal) > 24) raVal = raVal / 15.0;
                    document.getElementById('raInput').value = raVal;
                }
                if (json.data.decl !== undefined && json.data.decl !== null) {
                    document.getElementById('declInput').value = json.data.decl;
                }
                status.textContent = '{{ __('Coordinates filled in from SIMBAD.') }}';
                status.classList.replace('text-gray-400', 'text-green-400');
            } else {
                status.textContent = json.error || '{{ __('No SIMBAD data found.') }}';
                status.classList.replace('text-gray-400', 'text-red-400');
            }
        })
        .catch(() => {
            status.textContent = '{{ __('SIMBAD lookup failed.') }}';
            status.classList.replace('text-gray-400', 'text-red-400');
        })
        .finally(() => { btn.disabled = false; });
});
</script>
@endsection
