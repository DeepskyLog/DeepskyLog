<div class="mt-4">
    <h3 class="text-sm text-gray-300 mb-2">{{ __('Asteroid details') }}</h3>
</div>

<div class="mt-4">
    <h3 class="text-sm text-gray-300 mb-2">{{ __('Sky quality') }}</h3>
    <div class="mt-2">
        @livewire('observation-conditions', ['initialSqm' => old('sqm', ''), 'initialNelm' => old('limiting_magnitude', ''), 'initialBortle' => old('bortle', '')])
    </div>

    <h3 class="text-sm text-gray-300 mb-2 mt-4">{{ __('Asteroid details') }}</h3>
    <div>
        <label class="block text-xs text-gray-400 mb-1">{{ __('Magnitude estimate') }}</label>
        <input name="asteroid_magnitude" type="number" step="0.1" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100" value="{{ old('asteroid_magnitude') }}" />
    </div>
</div>
