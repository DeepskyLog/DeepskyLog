<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">{{ __('My sessions') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-gray-900 shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($sessions as $session)
                        @php
                            $observerSlug = $session->observer->slug ?? $session->observerid;
                        @endphp
                        <article class="bg-gray-800 p-4 rounded">
                            @if(! empty($session->preview))
                                <div class="mb-3">
                                    <a href="{{ route('session.show', [$observerSlug, $session->slug ?? $session->id]) }}">
                                        <img src="{{ $session->preview }}" alt="{{ $session->name ?? __('Session') }}" class="w-full h-40 object-cover rounded" />
                                    </a>
                                </div>
                            @endif
                            <h3 class="text-lg font-bold text-white mb-2">
                                <a href="{{ route('session.show', [$observerSlug, $session->slug ?? $session->id]) }}" class="hover:underline">{{ html_entity_decode($session->name ?? __('Session :id', ['id' => $session->id]), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                            </h3>
                            <div class="text-sm text-gray-400 mb-3">
                                <span>{{ $session->begindate ? \Carbon\Carbon::parse($session->begindate)->translatedFormat('j M Y') : __('Unknown') }}</span>
                                <span class="mx-2">&ndash;</span>
                                <span>{{ $session->enddate ? \Carbon\Carbon::parse($session->enddate)->translatedFormat('j M Y') : __('Unknown') }}</span>
                            </div>
                            @if(isset($session->observation_count))
                                <div class="text-sm text-gray-300 mb-2">{{ __('Observations') }}: <strong class="text-white">{{ $session->observation_count }}</strong></div>
                            @endif
                            <p class="text-sm text-gray-300 mb-3">{{ Str::limit(strip_tags(html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')), 180) }}</p>
                            <div class="flex items-center justify-between text-sm">
                                <div class="text-gray-400">{{ __('Observers') }}: {{ $session->otherObserversCount() ?? 1 }}</div>
                                <a href="{{ route('session.show', [$observerSlug, $session->slug ?? $session->id]) }}" class="text-blue-500 hover:underline">{{ __('Read more') }}</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $sessions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
