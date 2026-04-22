<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-200 leading-tight">{{ __('Discover observing lists') }}</h2>
            @auth
                <a href="{{ route('observing-lists.index') }}"
                   class="inline-flex items-center px-3 py-2 bg-gray-700 border border-gray-600 rounded-md font-semibold text-xs text-gray-200 uppercase tracking-widest hover:bg-gray-600">
                    {{ __('My lists') }}
                </a>
            @endauth
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Sort tabs --}}
            <div class="flex items-center gap-1 mb-6 border-b border-gray-700 pb-0">
                <a href="{{ route('observing-lists.discover', ['sort' => 'newest']) }}"
                   class="px-4 py-2 text-sm font-medium border-b-2 -mb-px
                       {{ $sortBy === 'newest' ? 'border-blue-500 text-blue-400' : 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500' }}">
                    {{ __('Newest') }}
                </a>
                <a href="{{ route('observing-lists.discover', ['sort' => 'popular']) }}"
                   class="px-4 py-2 text-sm font-medium border-b-2 -mb-px
                       {{ $sortBy === 'popular' ? 'border-blue-500 text-blue-400' : 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500' }}">
                    {{ __('Most popular') }}
                </a>
            </div>

            @if ($publicLists->isEmpty())
                <div class="text-gray-400 text-sm py-8 text-center">
                    {{ __('No public observing lists found.') }}
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($publicLists as $list)
                        @php $subscribed = in_array($list->id, $subscribedIds); @endphp
                        <article class="bg-gray-800 p-4 rounded-lg border border-gray-700 flex flex-col">
                            <div class="flex-1">
                                <h4 class="font-bold text-white text-base mb-1">
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
