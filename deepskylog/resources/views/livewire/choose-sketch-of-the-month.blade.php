<div>
    <div class="mb-4 max-w-sm">
        <label for="sketch-of-the-month-date" class="block text-sm font-medium text-gray-300">{{ __('Sketch of the month on') }}</label>
        <select id="sketch-of-the-month-date" wire:model.live="date"
            class="mt-1 w-full rounded border border-gray-700 bg-gray-800 px-3 py-2 text-gray-100">
            @foreach ($dates as $option)
                <option value="{{ $option->toDateString() }}">{{ $option->translatedFormat('j F Y') }}</option>
            @endforeach
        </select>
    </div>

    <h3 class="text-lg font-semibold">
        {{ __('DeepskyLog sketches of the week of :month', ['month' => $month->translatedFormat('F Y')]) }}
    </h3>

    @if ($candidates->isEmpty())
        <p class="mt-2 text-gray-400">{{ __('There are no sketches of the week in this month to choose from.') }}</p>
    @else
        @php
            \App\Services\Observations\ObservationCardData::instance()->preload($candidates->pluck('observation')->filter());
        @endphp
        <div class="flex flex-wrap">
            @foreach ($candidates as $sketch)
                <div class="flex flex-col" wire:key="candidate-{{ $sketch->id }}">
                    <x-sketch :sketch="$sketch" :use_selection_date="true" />
                    <form method="POST" action="{{ route('sketch-of-the-month.store') }}" class="mt-2 pr-3">
                        @csrf
                        <input type="hidden" name="observation_id" value="{{ $sketch->observation_id }}" />
                        <input type="hidden" name="date" value="{{ $date }}" />
                        <button type="submit" class="w-full rounded bg-amber-600 px-3 py-2 text-sm text-white hover:bg-amber-500">
                            {{ __('Choose as sketch of the month') }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
