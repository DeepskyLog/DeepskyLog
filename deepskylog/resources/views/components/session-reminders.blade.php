@props (["sessions"])

{{-- The observer's own sessions of the last weeks without any observation of theirs (SessionObservationReminders) on the home page --}}
<x-home-section :title="__('Sessions without observations')" {{ $attributes }}>
    <p class="px-2 text-sm text-gray-400">{{ __("You have not added any observations to these sessions yet.") }}</p>
    <ul class="divide-y divide-gray-700 px-2">
        @foreach ($sessions as $session)
            <li class="flex flex-wrap items-baseline justify-between gap-x-4 py-2">
                <span class="min-w-0">
                    <a href="{{ route("session.show", [auth()->user()->slug, $session->slug ?: $session->id]) }}" class="text-sm font-semibold break-words text-white hover:underline">{{ html_entity_decode($session->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}</a>
                    @if ($date = $session->begindate ?? $session->enddate)
                        <span class="text-sm text-gray-400">&middot; {{ $date->isoFormat("LL") }}</span>
                    @endif
                </span>
                <a href="{{ \App\Services\SessionObservationReminders::addObservationUrl($session) }}" class="text-sm text-blue-400 hover:underline">{{ __("Add observations") }}</a>
            </li>
        @endforeach
    </ul>
    <x-slot name="footer">
        <a href="{{ route("session.user", [auth()->user()->slug]) }}" class="text-blue-400 hover:underline">{{ __("My sessions") }} &rarr;</a>
    </x-slot>
</x-home-section>
