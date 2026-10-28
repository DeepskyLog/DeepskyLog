{{--
    A select of the countries of residence (ObserverCountries::options()) that
    reloads the page with ?country=<code>, keeping the query parameters in
    $keep. Not shown when no country has enough observers.
--}}
@props(['options' => [], 'selected' => null, 'keep' => []])

@if (count($options) > 0)
    <form method="GET" action="{{ url()->current() }}" {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
        @foreach ($keep as $name => $value)
            @if ($value !== null && $value !== '')
                <input type="hidden" name="{{ $name }}" value="{{ $value }}" />
            @endif
        @endforeach
        <label for="residence-filter" class="whitespace-nowrap text-sm text-gray-300">{{ __('Observers from') }}</label>
        <select
            id="residence-filter"
            name="country"
            onchange="this.form.submit()"
            class="rounded-md border border-gray-600 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
            <option value="">{{ __('All countries') }}</option>
            @foreach ($options as $code => $label)
                <option value="{{ $code }}" @selected($selected === $code)>{{ $label }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="text-sm text-sky-400">{{ __('Show') }}</button></noscript>
    </form>
@endif
