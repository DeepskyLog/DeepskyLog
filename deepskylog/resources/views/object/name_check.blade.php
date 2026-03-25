@php use App\Models\DeepskyObject; @endphp
@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-semibold text-gray-200">{{ __('Possible candidates') }}</h1>

    <p class="text-sm text-gray-400 mt-2">{{ __('Please confirm that the object is not listed below') }}</p>

    <div class="mt-4 bg-gray-800 p-4 rounded">
        @if($candidates->isEmpty())
            <div class="text-gray-300">{{ __('No similar names found.') }}</div>
            <div class="mt-4">
                <a href="{{ route('object.coordsForm', ['name' => $name]) }}"
                   class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-600">
                    {{ __('Continue entering coordinates') }}
                </a>
            </div>
        @else
            <table class="w-full text-left text-gray-200">
                <thead>
                    <tr class="text-sm text-gray-400 border-b border-gray-600">
                        <th class="pb-1 pr-4">{{ __('Name') }}</th>
                        <th class="pb-1 pr-4">{{ __('RA') }}</th>
                        <th class="pb-1 pr-4">{{ __('Decl') }}</th>
                        <th class="pb-1">{{ __('Type') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($candidates as $cand)
                        <tr class="border-t border-gray-700">
                            <td class="py-2 pr-4">
                                @if(!empty($cand->slug))
                                    <a href="{{ route('object.show', $cand->slug) }}" class="text-blue-400 hover:underline">{{ $cand->objectname }}</a>
                                @else
                                    {{ $cand->objectname }}
                                @endif
                            </td>
                            <td class="py-2 pr-4 font-mono text-sm">{{ DeepskyObject::formatRa($cand->ra) ?? '—' }}</td>
                            <td class="py-2 pr-4 font-mono text-sm">{{ DeepskyObject::formatDec($cand->decl) ?? '—' }}</td>
                            <td class="py-2">{{ $cand->type_label ?? $cand->type ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $candidates->links() }}
            </div>

            <div class="mt-4 flex gap-2">
                <a href="{{ route('object.coordsForm', ['name' => $name]) }}"
                   class="bg-green-700 text-white px-4 py-2 rounded hover:bg-green-600">
                    {{ __('None of these — continue adding') }}
                </a>
                <a href="{{ route('object.create') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-500">
                    {{ __('Cancel') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
