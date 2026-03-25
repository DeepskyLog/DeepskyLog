@extends('layouts.app')

@section('content')
<div class="p-6 max-w-2xl">
    <h1 class="text-2xl font-semibold text-gray-200">{{ __('Add new object') }}</h1>
    <p class="mt-1 text-sm text-gray-400">{{ __('Step 1 of 3: Enter the object name') }}</p>

    @if ($errors->any())
        <div class="mt-4 p-3 rounded border border-red-600 bg-red-900/40 text-sm text-red-300">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('object.checkName') }}" method="POST" class="mt-6">
        @csrf

        {{-- Catalog --}}
        <div class="mb-5">
            @if(Auth::user()->isAdministrator() || Auth::user()->isDatabaseExpert())
                <label class="block text-sm font-medium text-gray-400 mb-1">
                    {{ __('Catalog') }}
                    <span class="text-gray-500 font-normal">({{ __('optional') }})</span>
                </label>
            @else
                <label class="block text-sm font-medium text-gray-400 mb-1">{{ __('Catalog') }}</label>
            @endif
            <select name="catalog"
                    class="block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- {{ __('Select catalog') }} --</option>
                @foreach($catalogs as $code)
                    <option value="{{ $code }}" {{ old('catalog') === $code ? 'selected' : '' }}>{{ $code }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">{{ __('e.g. NGC, IC, M, …') }}</p>
        </div>

        {{-- Catalog number --}}
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-400 mb-1">{{ __('Catalog number') }}</label>
            <input type="text" name="number" value="{{ old('number') }}"
                   placeholder="{{ __('e.g. 1976') }}"
                   class="block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-500" />
            <p class="mt-1 text-xs text-gray-500">{{ __('The number or identifier within the selected catalog') }}</p>
            @error('number') <div class="mt-1 text-sm text-red-400">{{ $message }}</div> @enderror
        </div>

        {{-- Full object name (admins / DB experts only) --}}
        @if(Auth::user()->isAdministrator() || Auth::user()->isDatabaseExpert())
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-400 mb-1">{{ __('Or full object name') }}</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   placeholder="{{ __('e.g. NGC 1976  or  Orion Nebula') }}"
                   class="block w-full rounded border border-gray-600 bg-gray-900 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-500" />
            <p class="mt-1 text-xs text-gray-500">{{ __('Use this instead of catalog + number when the name does not follow the standard pattern') }}</p>
            @error('name') <div class="mt-1 text-sm text-red-400">{{ $message }}</div> @enderror
        </div>
        @endif

        <div class="flex gap-3 mt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-green-700 text-white px-5 py-2 rounded hover:bg-green-600 font-medium">
                {{ __('Check the name') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center bg-gray-600 text-white px-5 py-2 rounded hover:bg-gray-500 font-medium">
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
