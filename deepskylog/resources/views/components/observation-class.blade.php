{{-- The class an observer gave their own observation (#321): A, B or C; nothing when not graded --}}
@props(['value' => null])
@if (in_array($value, \App\Models\Observation::OBSERVER_CLASSES, true))
    @php
        $colors = [
            'A' => 'border-amber-400 text-amber-300',
            'B' => 'border-sky-500 text-sky-300',
            'C' => 'border-gray-500 text-gray-300',
        ];
    @endphp
    <span
        {{ $attributes->merge(['class' => 'inline-flex items-center rounded border px-1.5 text-xs font-semibold leading-5 ' . $colors[$value]]) }}
        title="{{ __(\App\Models\Observation::observerClassDescriptions()[$value]) }}"
    >{{ __('Class :class', ['class' => $value]) }}</span>
@endif
