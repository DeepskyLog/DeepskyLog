<div>
    @auth
        @if ($showToggle)
            @if ($activeList)
                <li>
                    <button
                        wire:click="toggle"
                        wire:loading.attr="disabled"
                        class="flex items-center gap-2 text-sm w-full text-left
                            {{ $inList ? 'text-green-400 hover:text-red-400' : 'text-gray-300 hover:text-green-400' }}">
                        @if ($inList)
                            <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span wire:loading.remove>{{ __('In active list') }}: <span class="font-medium">{{ $activeList->name }}</span></span>
                            <span wire:loading>{{ __('Updating…') }}</span>
                        @else
                            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span wire:loading.remove>{{ __('Add to') }}: <span class="font-medium">{{ $activeList->name }}</span></span>
                            <span wire:loading>{{ __('Updating…') }}</span>
                        @endif
                    </button>
                </li>
            @else
                <li>
                    <a href="{{ route('observing-lists.index') }}"
                       class="flex items-center gap-2 text-gray-400 hover:text-gray-200 text-sm">
                        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <span>{{ __('Set active observing list') }}</span>
                    </a>
                </li>
            @endif
        @endif

        @if ($showNote && $inList && $this->normalizedItemDescription !== '')
            <div class="mt-2 text-sm text-gray-300 leading-relaxed whitespace-pre-line">
                <div class="text-xs uppercase tracking-wide text-gray-400 mb-1">{{ __('Active list note') }}</div>
                {{ $this->normalizedItemDescription }}
            </div>
        @endif
    @endauth
</div>
