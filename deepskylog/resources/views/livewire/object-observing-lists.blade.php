<div>
    @if ($own->isNotEmpty() || $public->isNotEmpty())
        <div class="bg-gray-800 p-3 rounded shadow text-gray-100">
            <h4 class="font-semibold mb-2 text-white">{{ __('In observing lists') }}</h4>

            @if ($own->isNotEmpty())
                <div class="text-xs text-gray-400 mb-1">{{ __('My lists') }}</div>
                <ul class="space-y-1 text-sm mb-3">
                    @foreach ($own as $list)
                        <li class="flex items-center justify-between gap-2">
                            <a href="{{ route('observing-list.show', $list) }}" class="text-gray-300 hover:underline truncate">
                                {{ trim(html_entity_decode((string) $list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?: __('(no name)') }}
                            </a>
                            <span class="flex items-center gap-2 flex-shrink-0 text-xs text-gray-400">
                                @unless ($list->public)
                                    <span title="{{ __('Private') }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <span class="sr-only">{{ __('Private') }}</span>
                                    </span>
                                @endunless
                                {{ $list->items_count }} {{ __('objects') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
                @if ($ownTotal > $own->count())
                    <a href="{{ route('observing-lists.index', ['q' => $objectName]) }}"
                       class="inline-block -mt-2 mb-3 text-xs text-gray-400 hover:text-white hover:underline">
                        {{ __('All :count of my lists', ['count' => $ownTotal]) }}
                    </a>
                @endif
            @endif

            @if ($public->isNotEmpty())
                @if ($own->isNotEmpty())
                    <div class="text-xs text-gray-400 mb-1">{{ __('Public lists') }}</div>
                @endif
                <ul class="space-y-1 text-sm">
                    @foreach ($public as $list)
                        <li class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <a href="{{ route('observing-list.show', $list) }}" class="text-gray-300 hover:underline">
                                    {{ trim(html_entity_decode((string) $list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?: __('(no name)') }}
                                </a>
                                @if ($list->owner)
                                    <span class="text-xs text-gray-400">
                                        {{ __('by') }}
                                        <a href="{{ route('observer.show', $list->owner->slug) }}" class="hover:underline">{{ html_entity_decode($list->owner->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                                    </span>
                                @endif
                            </div>
                            <span class="flex items-center gap-2 flex-shrink-0 text-xs text-gray-400">
                                <span>{{ $list->items_count }} {{ __('objects') }}</span>
                                @if ($list->likes_count > 0)
                                    <span class="flex items-center gap-0.5" title="{{ __('Likes') }}">
                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                        {{ $list->likes_count }}
                                    </span>
                                @endif
                                @if (($list->subscriptions_count ?? 0) > 0)
                                    <span class="flex items-center gap-0.5" title="{{ __('Subscribers') }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        {{ $list->subscriptions_count }}
                                    </span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>

                @if ($publicTotal > $public->count())
                    <a href="{{ route('observing-lists.discover', ['q' => $objectName, 'sort' => 'popular']) }}"
                       class="inline-block mt-2 text-xs text-gray-400 hover:text-white hover:underline">
                        {{ __('All :count public lists', ['count' => $publicTotal]) }}
                    </a>
                @endif
            @endif
        </div>
    @endif
</div>
