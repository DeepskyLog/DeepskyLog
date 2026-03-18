<div>
    {{-- Trigger button --}}
    <button type="button"
            wire:click="openModal"
            class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium text-sm">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        {{ __('Delete Object') }}
    </button>

    {{-- Modal --}}
    <x-modal-card
        blur
        title="{{ __('Delete Object') }}"
        wire:model.live="showModal"
    >
        <div class="space-y-4">
            <p class="text-sm text-gray-300">
                {{ __('You are about to permanently delete the object') }}
                <span class="font-bold text-white">{{ $objectName }}</span>.
                {{ __('This action cannot be undone.') }}
            </p>

            @if ($observationsCount > 0)
                <div class="p-4 bg-amber-900/50 border border-amber-600 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-amber-300 font-semibold text-sm">{{ __('Warning: This object has observations') }}</p>
                            <p class="text-amber-200 text-sm mt-1">
                                {{ trans_choice(':count observation is linked to this object.|:count observations are linked to this object.', $observationsCount, ['count' => $observationsCount]) }}
                                @if ($observingListCount > 0)
                                    {{ trans_choice('It also appears in :count observing list.|It also appears in :count observing lists.', $observingListCount, ['count' => $observingListCount]) }}
                                @endif
                                {{ __('Select a target object below to move all observations and observing list entries before deleting.') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Move observations section --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-300">
                        {{ __('Move observations and observing list entries to:') }}
                        <span class="text-red-400">*</span>
                    </label>

                    @if ($moveToSlug)
                        <div class="flex items-center gap-2 p-2 bg-green-900/40 border border-green-600 rounded-lg">
                            <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-green-300 text-sm font-medium flex-1">{{ $moveToLabel }}</span>
                            <button type="button" wire:click="clearTarget"
                                    class="text-gray-400 hover:text-white text-xs px-2 py-0.5 rounded hover:bg-gray-700">
                                {{ __('Change') }}
                            </button>
                        </div>
                    @else
                        <div class="relative" x-data="{ open: false }">
                            <input
                                type="text"
                                wire:model.live="moveToSearch"
                                x-on:focus="open = true"
                                x-on:click.outside="open = false"
                                placeholder="{{ __('Search for an object...') }}"
                                class="w-full rounded-lg border-gray-600 bg-gray-800 text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2 px-4 py-2 text-sm"
                                autocomplete="off"
                            />

                            @if (count($searchResults) > 0)
                                <div class="absolute z-30 mt-1 w-full bg-gray-800 border border-gray-600 rounded-lg shadow-lg max-h-56 overflow-auto">
                                    @foreach ($searchResults as $result)
                                        <button
                                            type="button"
                                            wire:click="selectTarget('{{ $result['slug'] }}', '{{ addslashes($result['label']) }}')"
                                            class="w-full text-left px-4 py-2 hover:bg-gray-700 text-white text-sm border-b border-gray-700 last:border-0"
                                        >
                                            {{ $result['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500">{{ __('Type at least 2 characters to search for a target object.') }}</p>
                    @endif
                </div>
            @else
                @if ($observingListCount > 0)
                    <div class="p-4 bg-blue-900/50 border border-blue-600 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-blue-200 text-sm">
                                {{ trans_choice('This object appears in :count observing list. That entry will be removed.|This object appears in :count observing lists. Those entries will be removed.', $observingListCount, ['count' => $observingListCount]) }}
                            </p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-400">
                        {{ __('No observations or observing list entries are linked to this object. It can be safely deleted.') }}
                    </p>
                @endif
            @endif
        </div>

        <x-slot name="footer">
            <x-button
                type="button"
                label="{{ __('Cancel') }}"
                wire:click="closeModal"
                wire:loading.attr="disabled"
            />

            @if ($observationsCount === 0 || $moveToSlug)
                <form
                    method="POST"
                    action="{{ route('object.destroy', ['slug' => $objectSlug]) }}"
                    class="inline"
                    onsubmit="return confirm('{{ __('Are you absolutely sure you want to delete this object? This cannot be undone.') }}')"
                >
                    @csrf
                    @method('DELETE')
                    @if ($moveToSlug)
                        <input type="hidden" name="move_to_slug" value="{{ $moveToSlug }}">
                    @endif
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium text-sm ml-3"
                    >
                        {{ __('Delete Object') }}
                    </button>
                </form>
            @else
                <button
                    type="button"
                    disabled
                    class="inline-flex items-center px-4 py-2 bg-red-900/50 text-red-400 rounded-md font-medium text-sm ml-3 cursor-not-allowed"
                    title="{{ __('Select a target object to move observations first') }}"
                >
                    {{ __('Delete Object') }}
                </button>
            @endif
        </x-slot>
    </x-modal-card>
</div>
