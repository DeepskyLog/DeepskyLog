@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-semibold text-gray-200">{{ __('Add asteroid') }}</h1>
    <p class="mt-1 text-sm text-gray-400">{{ __('Search the numbered asteroids by number, name or designation (e.g. 4, Vesta or 1982 UC3) and add the one you observed.') }}</p>

    @if (session('error'))
        <div class="mt-4 p-3 bg-red-900/50 border border-red-600 rounded text-red-200 text-sm">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('object.create.asteroid') }}" class="mt-4 flex gap-2">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="{{ __('Number, name or designation') }}" autofocus
               class="block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none" />
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">{{ __('Search') }}</button>
        <a href="{{ route('object.create') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-500">{{ __('Back') }}</a>
    </form>

    <div class="mt-6">
        @if ($q === '')
        @elseif ($asteroids->isEmpty())
            <div class="text-gray-300">{{ __('No asteroids found.') }}</div>
        @else
            <form method="POST" action="{{ route('object.store.asteroid') }}">
                @csrf
                <table class="w-full text-left text-gray-200 mt-2">
                    <thead>
                        <tr class="text-sm text-gray-400 border-b border-gray-600">
                            <th class="pb-1 pr-4">{{ __('Name') }}</th>
                            <th class="pb-1 pr-4">{{ __('Absolute magnitude (H)') }}</th>
                            <th class="pb-1">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($asteroids as $asteroid)
                            <tr class="border-t border-gray-700">
                                <td class="py-2 pr-4">{{ \App\Services\AsteroidCoordinates::displayName($asteroid) }}</td>
                                <td class="py-2 pr-4">{{ number_format((float) $asteroid->H, 1) }}</td>
                                <td class="py-2">
                                    @if ($existing->has($asteroid->number))
                                        <a href="{{ route('object.show', ['slug' => $existing[$asteroid->number]->slug]) }}" class="text-blue-400 hover:underline">{{ __('Already in DeepskyLog') }}</a>
                                    @else
                                        <button type="submit" name="number" value="{{ $asteroid->number }}" class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-600">{{ __('Add') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        @endif
    </div>
</div>
@endsection
