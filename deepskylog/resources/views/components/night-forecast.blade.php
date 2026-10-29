@props (["nights", "location"])

{{-- The cloud forecast of the coming nights at the observer's standard location, a box of the home page --}}
<x-home-section :title="__('Coming nights')" {{ $attributes }}>
    <div class="space-y-3 px-2">
        <p class="text-sm text-gray-400">{{ html_entity_decode($location->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}</p>
        <x-night-forecast-nights :nights="$nights" />
    </div>
    @auth
        <x-slot name="footer">
            <a href="{{ route("location.best-site") }}" class="text-blue-400 hover:underline">{{ __("Which of my sites tonight?") }}</a>
            @if (! auth()->user()->clear_night_alert)
                &middot;
                <a href="{{ route("profile.show") }}" class="text-blue-400 hover:underline">{{ __("Get a message when a clear, dark night is coming") }}</a>
            @endif
        </x-slot>
    @endauth
</x-home-section>
