<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-200 leading-tight">{{ __('Discover observing lists') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('feed.observing-lists') }}"
                   title="{{ __('Subscribe to RSS feed') }}"
                   class="inline-flex items-center px-3 py-2 rounded bg-orange-600 hover:bg-orange-500 text-white text-xs font-semibold">
                    <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19.01 7.38 20 6.18 20C4.98 20 4 19.01 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93V10.1z"/>
                    </svg>
                    RSS
                </a>
                @auth
                <a href="{{ route('observing-lists.index') }}"
                   class="inline-flex items-center px-3 py-2 bg-gray-700 border border-gray-600 rounded-md font-semibold text-xs text-gray-200 uppercase tracking-widest hover:bg-gray-600">
                    {{ __('My lists') }}
                </a>
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Search --}}
            <div class="p-4 mb-6 bg-gray-800 border border-gray-700 rounded-md">
                <form method="GET" action="{{ route('observing-lists.discover') }}" class="flex flex-col sm:flex-row gap-3 sm:items-center">
                    <input type="hidden" name="sort" value="{{ $sortBy }}">
                    <label for="discover-search" class="text-sm text-gray-300 sm:whitespace-nowrap">{{ __('Search observing lists') }}</label>
                    <input
                        id="discover-search"
                        name="q"
                        type="text"
                        value="{{ $search }}"
                        placeholder="{{ __('Search on name, observer or object') }}"
                        class="flex-1 bg-gray-900 border border-gray-600 rounded-md text-gray-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    @if (count($countryOptions) > 0)
                        <label for="discover-country" class="text-sm text-gray-300 sm:whitespace-nowrap">{{ __('Observers from') }}</label>
                        <select
                            id="discover-country"
                            name="country"
                            onchange="this.form.submit()"
                            class="bg-gray-900 border border-gray-600 rounded-md text-gray-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">{{ __('All countries') }}</option>
                            @foreach ($countryOptions as $code => $label)
                                <option value="{{ $code }}" @selected($country === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @endif
                    <div class="flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                            {{ __('Search') }}
                        </button>
                        @if ($search !== '')
                            <a href="{{ route('observing-lists.discover', array_filter(['sort' => $sortBy, 'country' => $country])) }}" class="inline-flex items-center px-3 py-2 bg-gray-700 border border-gray-600 rounded-md font-semibold text-xs text-gray-200 uppercase tracking-widest hover:bg-gray-600">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Sort tabs --}}
            <div class="flex items-center gap-1 mb-6 border-b border-gray-700 pb-0">
                <a href="{{ route('observing-lists.discover', array_filter(['sort' => 'newest', 'q' => $search, 'country' => $country])) }}"
                   class="px-4 py-2 text-sm font-medium border-b-2 -mb-px
                       {{ $sortBy === 'newest' ? 'border-blue-500 text-blue-400' : 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500' }}">
                    {{ __('Newest') }}
                </a>
                <a href="{{ route('observing-lists.discover', array_filter(['sort' => 'popular', 'q' => $search, 'country' => $country])) }}"
                   class="px-4 py-2 text-sm font-medium border-b-2 -mb-px
                       {{ $sortBy === 'popular' ? 'border-blue-500 text-blue-400' : 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500' }}">
                    {{ __('Most popular') }}
                </a>
            </div>

            @if ($publicLists->isEmpty())
                <div class="text-gray-400 text-sm py-8 text-center">
                    @if ($search !== '' || $country)
                        {{ __('No public observing lists match your search.') }}
                    @else
                        {{ __('No public observing lists found.') }}
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($publicLists as $list)
                        @php $subscribed = in_array($list->id, $subscribedIds); @endphp
                        <article class="bg-gray-800 p-4 rounded-lg border border-gray-700 flex flex-col">
                            <div class="flex-1">
                                <h4 class="font-bold text-white text-base mb-1 break-words">
                                    <a href="{{ route('observing-list.show', $list) }}" class="hover:underline">{{ html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                                </h4>
                                <p class="text-xs text-gray-500 mb-2">
                                    {{ __('by') }}
                                    <a href="{{ route('observer.show', $list->owner->slug) }}" class="text-gray-300 hover:underline">{{ $list->owner->name }}</a>
                                    &bull;
                                    {{ $list->created_at->diffForHumans() }}
                                </p>
                                @if ($list->description)
                                    <p class="text-sm text-gray-400 mb-3">{{ Str::limit($list->description, 150) }}</p>
                                @endif
                            </div>
                            <div class="mt-3">
                                <div class="flex items-center gap-4 text-xs text-gray-400 mb-3">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                        </svg>
                                        {{ $list->items_count }} {{ __('objects') }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        {{ $list->likes_count }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        {{ $list->comments_count }}
                                    </span>
                                    <span class="flex items-center gap-1" title="{{ __('Subscribers') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        {{ $list->subscriptions_count }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('observing-list.show', $list) }}"
                                       class="text-xs px-2 py-1 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded">
                                        {{ __('View') }}
                                    </a>
                                    @auth
                                        @if ($subscribed)
                                            <form method="POST" action="{{ route('observing-list.unsubscribe', $list) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="text-xs px-2 py-1 bg-gray-700 hover:bg-red-800 text-gray-300 hover:text-white rounded">
                                                    {{ __('Unsubscribe') }}
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('observing-list.subscribe', $list) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="text-xs px-2 py-1 bg-green-700 hover:bg-green-600 text-white rounded">
                                                    {{ __('Subscribe') }}
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-6">{{ $publicLists->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
