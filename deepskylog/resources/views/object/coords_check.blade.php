@php use App\Models\DeepskyObject; @endphp
@extends('layouts.app')

@section('content')
<div class="p-6 max-w-4xl">
    <h1 class="text-2xl font-semibold text-gray-200">{{ __('Nearby objects') }}</h1>

    <p class="text-sm text-gray-400 mt-1">
        {{ __('Objects within 0.5° of') }}
        <span class="font-mono text-gray-200">{{ DeepskyObject::formatRa(DeepskyObject::raToDecimal($raInput) / 15.0) }}</span>,
        <span class="font-mono text-gray-200">{{ DeepskyObject::formatDec(DeepskyObject::decToDecimal($decInput)) }}</span>
        — {{ __('confirm your object is not already listed before continuing.') }}
    </p>

    <div class="mt-4 bg-gray-800 p-4 rounded">
        @if($nearby->isEmpty())
            <div class="text-gray-300">{{ __('No nearby objects found.') }}</div>
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
                    @foreach($nearby as $n)
                        <tr class="border-t border-gray-700">
                            <td class="py-2 pr-4">
                                @if(!empty($n->slug))
                                    <a href="{{ route('object.show', $n->slug) }}" class="text-blue-400 hover:underline">{{ $n->name }}</a>
                                @else
                                    {{ $n->name }}
                                @endif
                            </td>
                            <td class="py-2 pr-4 font-mono text-sm">{{ DeepskyObject::formatRa($n->ra) ?? '—' }}</td>
                            <td class="py-2 pr-4 font-mono text-sm">{{ DeepskyObject::formatDec($n->decl) ?? '—' }}</td>
                            <td class="py-2">{{ $n->type_label ?? $n->type ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $nearby->links() }}
            </div>
        @endif

        <div class="mt-5 flex gap-3">
            <a href="{{ route('object.details', ['name' => $name, 'ra' => $raInput, 'decl' => $decInput]) }}"
               class="bg-green-700 text-white px-5 py-2 rounded hover:bg-green-600 font-medium">
                {{ __('None of these — continue to details') }}
            </a>
            <a href="{{ route('object.coordsForm', ['name' => $name]) }}"
               class="bg-gray-600 text-white px-5 py-2 rounded hover:bg-gray-500 font-medium">
                {{ __('Back') }}
            </a>
        </div>
    </div>
</div>
@endsection
