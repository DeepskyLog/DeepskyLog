@props(['icon' => null, 'href' => '#', 'separator' => false, 'label' => null])

@if ($separator)
    <div class="border-t border-gray-700 my-1"></div>
@endif

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center px-3 py-2 text-sm text-gray-200 hover:bg-gray-700']) }}>
    @if ($icon)
        <x-icon :name="$icon" class="h-4 w-4 mr-2 text-gray-300" />
    @endif

    @if ($label)
        {!! $label !!}
    @else
        {{ $slot }}
    @endif
</a>
