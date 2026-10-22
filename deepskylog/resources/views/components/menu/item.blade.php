@props(['icon' => null, 'href' => '#', 'separator' => false, 'label' => null])

@if ($separator)
    <div class="border-t border-gray-700 my-1"></div>
@endif

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center min-w-0 px-3 py-2 text-sm text-gray-200 hover:bg-gray-700']) }}>
    @if ($icon)
        <span class="flex-shrink-0">
            <x-icon :name="$icon" class="h-4 w-4 mr-2 text-gray-300" />
        </span>
    @elseif (isset($svg))
        <span class="flex-shrink-0 inline-flex mr-2 text-gray-300">{{ $svg }}</span>
    @endif

    @if ($label)
        <span class="truncate">{!! $label !!}</span>
    @else
        <span class="truncate">{{ $slot }}</span>
    @endif
</a>
