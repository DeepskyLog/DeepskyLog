@props (["nights", "location"])

{{-- The cloud forecast of the coming nights at the observer's standard location, a box of the home page --}}
<x-home-section :title="__('Coming nights')" {{ $attributes }}>
    <div class="space-y-3 px-2">
        <p class="text-sm text-gray-400">{{ html_entity_decode($location->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}</p>
        <x-night-forecast-nights :nights="$nights" />
    </div>
    @if (auth()->check() && ! auth()->user()->clear_night_alert)
        <x-slot name="footer">
            <a href="{{ route("profile.show") }}" class="text-blue-400 hover:underline">{{ __("Get a message when a clear, dark night is coming") }}</a>
        </x-slot>
    @endif
</x-home-section>
