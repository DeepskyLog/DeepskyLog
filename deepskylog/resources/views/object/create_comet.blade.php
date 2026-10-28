@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-semibold text-gray-200">{{ __('Add comet') }}</h1>
    <p class="mt-1 text-sm text-gray-400">{{ __('Select a comet from the orbital elements table and add it to the cometobjects list.') }}</p>

    <form method="GET" action="{{ route('object.create.comet') }}" class="mt-4 flex gap-2">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="{{ __('Search comets by name') }}"
               class="block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none" />
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">{{ __('Search') }}</button>
        <a href="{{ route('object.create') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-500">{{ __('Back') }}</a>
    </form>

    <div class="mt-6">
        @if($comets->isEmpty())
            <div class="text-gray-300">{{ __('No comets found.') }}</div>
        @else
            <form method="POST" action="{{ route('object.store.comet') }}">
                @csrf
                <table class="w-full text-left text-gray-200 mt-2">
                    <thead>
                        <tr class="text-sm text-gray-400 border-b border-gray-600">
                            <th class="pb-1 pr-4">{{ __('Name') }}</th>
                            <th class="pb-1">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comets as $comet)
                            <tr class="border-t border-gray-700">
                                <td class="py-2 pr-4">{{ $comet->name }}</td>
                                <td class="py-2">
                                    <button type="submit" name="comet_name" value="{{ $comet->name }}" class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-600">{{ __('Add') }}</button>
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
