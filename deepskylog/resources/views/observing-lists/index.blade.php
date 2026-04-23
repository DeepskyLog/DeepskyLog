<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-200 leading-tight">{{ __('My observing lists') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('observing-lists.discover') }}"
                   class="inline-flex items-center px-3 py-2 bg-gray-700 border border-gray-600 rounded-md font-semibold text-xs text-gray-200 uppercase tracking-widest hover:bg-gray-600">
                    {{ __('Discover public lists') }}
                </a>
                <a href="{{ route('observing-list.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                    {{ __('New list') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">

            {{-- Name search --}}
            <div class="p-4 bg-gray-800 border border-gray-700 rounded-md">
                <form method="GET" action="{{ route('observing-lists.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-center">
                    <label for="list-search" class="text-sm text-gray-300 sm:whitespace-nowrap">{{ __('Search list name') }}</label>
                    <input
                        id="list-search"
                        name="q"
                        type="text"
                        value="{{ $search ?? '' }}"
                        placeholder="{{ __('Type part of a list name...') }}"
                        class="flex-1 bg-gray-900 border border-gray-600 rounded-md text-gray-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <div class="flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                            {{ __('Search') }}
                        </button>
                        @if (!empty($search))
                            <a href="{{ route('observing-lists.index') }}" class="inline-flex items-center px-3 py-2 bg-gray-700 border border-gray-600 rounded-md font-semibold text-xs text-gray-200 uppercase tracking-widest hover:bg-gray-600">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Active list banner --}}
            @if ($activeList)
                <div class="p-4 bg-blue-900/50 border border-blue-600 rounded-md flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-blue-100 text-sm">
                            {{ __('Active list:') }}
                            <a href="{{ route('observing-list.show', $activeList) }}" class="font-bold hover:underline">{{ html_entity_decode($activeList->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                            &mdash; {{ $activeList->items()->count() }} {{ __('objects') }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('observing-list.set-active', $activeList) }}">
                        @csrf
                        <button type="submit" class="text-xs text-blue-300 hover:text-white">{{ __('Clear active') }}</button>
                    </form>
                </div>
            @else
                <div class="p-4 bg-gray-800 border border-gray-700 rounded-md text-gray-400 text-sm">
                    {{ __('No active observing list selected. Set one as active to quickly add objects from any object page.') }}
                </div>
            @endif

            {{-- Owned lists --}}
            <section>
                <h3 class="text-lg font-semibold text-gray-200 mb-4">{{ __('My lists') }}</h3>

                @if ($ownedLists->isEmpty())
                    <p class="text-gray-400 text-sm">{{ __('You have not created any observing lists yet.') }}</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($ownedLists as $list)
                            @php $isActive = $activeList && $activeList->id === $list->id; @endphp
                            <article class="bg-gray-800 p-4 rounded-lg border {{ $isActive ? 'border-blue-500' : 'border-gray-700' }} relative">
                                @if ($isActive)
                                    <span class="absolute top-2 right-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-700 text-blue-100">
                                        {{ __('Active') }}
                                    </span>
                                @endif
                                <h4 class="font-bold text-white text-base mb-1 pr-16">
                                    <a href="{{ route('observing-list.show', $list) }}" class="hover:underline">{{ html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                                    @if ($list->public)
                                        <span class="ml-1 text-xs text-green-400">&bull; {{ __('Public') }}</span>
                                    @else
                                        <span class="ml-1 text-xs text-gray-500">&bull; {{ __('Private') }}</span>
                                    @endif
                                </h4>
                                @if ($list->description)
                                    <p class="text-sm text-gray-400 mb-3">{{ Str::limit($list->description, 120) }}</p>
                                @endif
                                <div class="flex items-center gap-4 text-xs text-gray-400 mb-3">
                                    <span>{{ $list->items_count }} {{ __('objects') }}</span>
                                    <span>{{ $list->likes_count }} {{ __('likes') }}</span>
                                    <span>{{ $list->comments_count }} {{ __('comments') }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    @if (!$isActive)
                                        <form method="POST" action="{{ route('observing-list.set-active', $list) }}">
                                            @csrf
                                            <button type="submit"
                                                class="text-xs px-2 py-1 bg-blue-700 hover:bg-blue-600 text-white rounded">
                                                {{ __('Set active') }}
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('observing-list.show', $list) }}"
                                       class="text-xs px-2 py-1 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded">
                                        {{ __('View') }}
                                    </a>
                                    <a href="{{ route('observing-list.edit', $list) }}"
                                       class="text-xs px-2 py-1 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded">
                                        {{ __('Edit') }}
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $ownedLists->links() }}</div>
                @endif
            </section>

            {{-- Subscribed lists --}}
            <section>
                <h3 class="text-lg font-semibold text-gray-200 mb-4">{{ __('Subscribed lists') }}</h3>

                @if ($subscribedLists->isEmpty())
                    <p class="text-gray-400 text-sm">
                        {{ __('You have not subscribed to any lists.') }}
                        <a href="{{ route('observing-lists.discover') }}" class="text-blue-400 hover:underline">{{ __('Browse public lists') }}</a>
                    </p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($subscribedLists as $list)
                            @php $isActive = $activeList && $activeList->id === $list->id; @endphp
                            <article class="bg-gray-800 p-4 rounded-lg border {{ $isActive ? 'border-blue-500' : 'border-gray-700' }} relative">
                                @if ($isActive)
                                    <span class="absolute top-2 right-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-700 text-blue-100">
                                        {{ __('Active') }}
                                    </span>
                                @endif
                                <h4 class="font-bold text-white text-base mb-1 pr-16">
                                    <a href="{{ route('observing-list.show', $list) }}" class="hover:underline">{{ html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                                </h4>
                                <p class="text-xs text-gray-500 mb-1">
                                    {{ __('by') }}
                                    <a href="{{ route('observer.show', $list->owner->slug) }}" class="text-gray-300 hover:underline">{{ $list->owner->name }}</a>
                                </p>
                                @if ($list->description)
                                    <p class="text-sm text-gray-400 mb-3">{{ Str::limit($list->description, 120) }}</p>
                                @endif
                                <div class="flex items-center gap-4 text-xs text-gray-400 mb-3">
                                    <span>{{ $list->items_count }} {{ __('objects') }}</span>
                                    <span>{{ $list->likes_count }} {{ __('likes') }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    @if (!$isActive)
                                        <form method="POST" action="{{ route('observing-list.set-active', $list) }}">
                                            @csrf
                                            <button type="submit"
                                                class="text-xs px-2 py-1 bg-blue-700 hover:bg-blue-600 text-white rounded">
                                                {{ __('Set active') }}
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('observing-list.show', $list) }}"
                                       class="text-xs px-2 py-1 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded">
                                        {{ __('View') }}
                                    </a>
                                    <form method="POST" action="{{ route('observing-list.unsubscribe', $list) }}">
                                        @csrf
                                        <button type="submit"
                                            class="text-xs px-2 py-1 bg-gray-700 hover:bg-red-800 text-gray-300 hover:text-white rounded">
                                            {{ __('Unsubscribe') }}
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $subscribedLists->links() }}</div>
                @endif
            </section>

        </div>
    </div>
</x-app-layout>
