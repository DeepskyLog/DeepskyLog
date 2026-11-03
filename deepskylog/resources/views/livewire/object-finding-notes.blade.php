<div id="finding-notes" class="mt-6">
    <h2 class="text-xl font-semibold text-white">{{ __('Finding notes') }}</h2>
    <div class="mt-2 bg-gray-800 p-3 rounded shadow text-gray-100">
        <p class="text-xs text-gray-400 mb-2">{{ __('Short tips of observers on how to find this object: a star hop, a filter, what to look for.') }}</p>

        @if ($notes->isEmpty())
            <p class="text-sm text-gray-400">{{ __('No finding notes yet.') }}</p>
        @else
            <ul class="space-y-2">
                @foreach ($notes as $note)
                    <li class="rounded border border-gray-700 bg-gray-900/60 p-2" wire:key="finding-note-{{ $note->id }}">
                        @if ($editingId === $note->id)
                            <form wire:submit="saveEdit" class="space-y-2">
                                <input type="text" wire:model="editText" maxlength="{{ \App\Models\ObjectFindingNote::MAX_LENGTH }}"
                                    aria-label="{{ __('Finding note') }}"
                                    class="w-full rounded border border-gray-600 bg-gray-900 px-2 py-1 text-sm text-gray-100" />
                                @error('editText')
                                    <p class="text-xs text-red-400">{{ $message }}</p>
                                @enderror
                                <div class="flex gap-2">
                                    <button type="submit" class="px-2 py-1 text-xs font-medium rounded bg-gray-700 hover:bg-gray-600 text-white">{{ __('Save') }}</button>
                                    <button type="button" wire:click="cancelEdit" class="px-2 py-1 text-xs font-medium rounded bg-gray-700 hover:bg-gray-600 text-white">{{ __('Cancel') }}</button>
                                </div>
                            </form>
                        @else
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-100 break-words">{{ $note->text }}</p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        @if ($note->user)
                                            <a href="{{ route('observer.show', $note->user->slug) }}" class="hover:underline">{{ html_entity_decode($note->user->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                                            ·
                                        @endif
                                        {{ $note->created_at?->translatedFormat('j M Y') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0 text-xs text-gray-400">
                                    @if ($note->canBeLikedBy($user))
                                        <button type="button" wire:click="toggleLike({{ $note->id }})"
                                            class="flex items-center gap-0.5 {{ $note->liked ? 'text-red-400' : 'hover:text-white' }}"
                                            title="{{ $note->liked ? __('Unlike') : __('Like') }}" aria-pressed="{{ $note->liked ? 'true' : 'false' }}">
                                            <svg class="h-3.5 w-3.5" fill="{{ $note->liked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                            </svg>
                                            <span>{{ $note->likes_count }}</span>
                                        </button>
                                    @else
                                        <span class="flex items-center gap-0.5" title="{{ __('Likes') }}">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                            </svg>
                                            <span>{{ $note->likes_count }}</span>
                                        </span>
                                    @endif
                                    @if ($note->canBeEditedBy($user))
                                        <button type="button" wire:click="startEdit({{ $note->id }})" class="hover:text-blue-400" title="{{ __('Edit') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span class="sr-only">{{ __('Edit') }}</span>
                                        </button>
                                    @endif
                                    @if ($note->canBeDeletedBy($user))
                                        <button type="button" wire:click="delete({{ $note->id }})" wire:confirm="{{ __('Delete this finding note?') }}" class="hover:text-red-400" title="{{ __('Delete') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span class="sr-only">{{ __('Delete') }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
            @if (! $showAll && $total > $notes->count())
                <button type="button" wire:click="$set('showAll', true)" class="mt-2 text-xs text-gray-400 hover:text-white hover:underline">
                    {{ __('All :count finding notes', ['count' => $total]) }}
                </button>
            @endif
        @endif

        @auth
            @if ($canAdd)
                <form wire:submit="add" class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-start">
                    <div class="flex-1">
                        <input type="text" wire:model="text" maxlength="{{ \App\Models\ObjectFindingNote::MAX_LENGTH }}"
                            placeholder="{{ __('E.g. hop from ζ Ori, use the UHC filter') }}" aria-label="{{ __('Finding note') }}"
                            class="w-full rounded border border-gray-600 bg-gray-900 px-2 py-1 text-sm text-gray-100" />
                        @error('text')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="px-2 py-1 text-xs font-medium rounded bg-gray-700 hover:bg-gray-600 text-white">{{ __('Add finding note') }}</button>
                </form>
            @endif
        @else
            <p class="mt-2 text-xs text-gray-400">
                <a href="{{ route('login') }}" class="hover:underline">{{ __('Log in to add a finding note or like one.') }}</a>
            </p>
        @endauth
    </div>
</div>
