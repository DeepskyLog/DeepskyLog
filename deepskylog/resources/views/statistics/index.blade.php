@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-card>
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold leading-tight text-white">{{ __('Statistics') }}</h1>
                        <p class="mt-1 text-sm text-gray-400">
                            @if ($selectedCountryLabel)
                                {{ __('Country') }}: {{ $selectedCountryLabel }}
                            @else
                                {{ __('All') }}
                            @endif
                        </p>
                    </div>

                    <form method="GET" action="{{ route('statistics.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div>
                            <label for="country" class="mb-1 block text-sm font-medium text-gray-300">{{ __('Country') }}</label>
                            <select
                                id="country"
                                name="country"
                                class="w-full rounded-md border border-gray-600 bg-gray-800 px-3 py-2 text-sm text-white shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:min-w-72"
                            >
                                <option value="">{{ __('All') }}</option>
                                @foreach ($countryOptions as $country => $label)
                                    <option value="{{ $country }}" @selected($selectedCountry === $country)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-500"
                        >
                            {{ __('Show') }}
                        </button>

                        @if ($selectedCountry)
                            <a
                                href="{{ route('statistics.index') }}"
                                class="inline-flex items-center justify-center rounded-md border border-gray-600 px-4 py-2 text-sm font-medium text-gray-200 transition hover:bg-gray-800"
                            >
                                {{ __('Reset') }}
                            </a>
                        @endif
                    </form>
                </div>
            </x-card>

            <x-card>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-gray-200">
                        <thead>
                            <tr class="border-b border-gray-700 text-left text-gray-300">
                                <th class="py-3 pr-6 font-semibold"></th>
                                @foreach ($summaryColumns as $label)
                                    <th class="py-3 px-4 font-semibold">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($summaryRows as $row)
                                <tr class="border-b border-gray-800/80 last:border-b-0">
                                    <td class="py-3 pr-6 font-medium text-white">{{ $row['label'] }}</td>
                                    @foreach (array_keys($summaryColumns) as $column)
                                        <td class="py-3 px-4">{{ $row['values'][$column] }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>

            <div>
                {!! $observationsPerYearChart->container() !!}
            </div>

            <div>
                {!! $observationsPerMonthChart->container() !!}
            </div>

            <div>
                {!! $objectTypesChart->container() !!}
            </div>

            <div>
                {!! $countriesChart->container() !!}
            </div>
        </div>
    </div>

    <script src="{{ $observationsPerYearChart->cdn() }}"></script>
    <script src="{{ $observationsPerMonthChart->cdn() }}"></script>
    <script src="{{ $objectTypesChart->cdn() }}"></script>
    <script src="{{ $countriesChart->cdn() }}"></script>

    {{ $observationsPerYearChart->script() }}
    {{ $observationsPerMonthChart->script() }}
    {{ $objectTypesChart->script() }}
    {{ $countriesChart->script() }}
@endsection