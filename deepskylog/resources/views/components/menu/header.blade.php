@props(['label' => null])

<div class="border-t border-gray-700">
    @if ($label)
        <div class="px-3 py-2 text-xs font-semibold text-gray-400 tracking-wide">
            {!! $label !!}
        </div>
    @endif

    <div class="px-0 py-1">
        {{ $slot }}
    </div>
</div>
