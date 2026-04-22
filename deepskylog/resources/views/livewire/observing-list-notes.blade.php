<div class="mt-6 rounded-lg border border-gray-700 bg-gray-800/60 p-4">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-300">{{ __('List notes') }}</h3>
        <span class="text-xs text-gray-500">{{ __('Notes are synced to the currently visible objects in the table.') }}</span>
    </div>

    @if (!$syncedWithTable)
        <p class="mt-4 text-sm text-gray-400">{{ __('Loading notes…') }}</p>
    @elseif ($notes->isEmpty())
        <p class="mt-4 text-sm text-gray-400">{{ __('No notes for the currently visible objects.') }}</p>
    @else
        <div class="mt-4 space-y-3">
            @foreach ($notes as $item)
                <div class="rounded-lg border border-gray-700 bg-gray-900/60 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            @if ($item->object_name)
                                <a href="{{ route('object.show', ['slug' => \Illuminate\Support\Str::slug($item->object_name)]) }}"
                                    class="font-medium text-gray-100 hover:underline">{{ $item->object_name }}</a>
                            @else
                                <span class="font-medium text-gray-400">{{ __('Unknown') }}</span>
                            @endif

                            <p class="mt-2 text-sm text-gray-300 whitespace-pre-line">{{ $item->item_description ?: __('No note yet.') }}</p>
                        </div>

                        @if ($isOwner)
                            <div class="flex items-center gap-2">
                                <a href="{{ route('observing-list.items.edit', [$listSlug, $item->id]) }}"
                                    class="text-gray-500 hover:text-blue-400"
                                    title="{{ __('Edit note') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('observing-list.items.destroy', [$listSlug, $item->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-gray-500 hover:text-red-400"
                                        title="{{ __('Remove') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
