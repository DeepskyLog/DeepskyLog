<x-app-layout>
    <div>
        <div class="mx-auto max-w-5xl bg-gray-900 px-4 py-6 sm:px-6 lg:px-8">

            {{-- Header --}}
            <header class="mb-6">
                <div class="flex flex-wrap items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl font-extrabold text-white">{{ html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</h1>
                        <p class="text-sm text-gray-400 mt-1">
                            {{ __('by') }}
                            <a href="{{ route('observer.show', $list->owner->slug) }}" class="text-gray-200 hover:underline font-medium">{{ $list->owner->name }}</a>
                            @if ($list->public)
                                &bull; <span class="text-green-400 text-xs">{{ __('Public') }}</span>
                            @else
                                &bull; <span class="text-gray-500 text-xs">{{ __('Private') }}</span>
                            @endif
                            &bull; {{ __('created') }} {{ $list->created_at->translatedFormat('j M Y') }}
                        </p>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                        {{-- Active indicator / set active --}}
                        @if ($isActive)
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-700 text-blue-100 rounded text-xs font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('Active list') }}
                            </span>
                        @else
                            <form method="POST" action="{{ route('observing-list.set-active', $list) }}">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-700 hover:bg-blue-600 text-white rounded text-xs font-semibold">
                                    {{ __('Set as active') }}
                                </button>
                            </form>
                        @endif

                        {{-- Subscribe / Unsubscribe (for non-owners) --}}
                        @if (!$isOwner && $list->public)
                            @if ($isSubscribed)
                                <form method="POST" action="{{ route('observing-list.unsubscribe', $list) }}">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 bg-gray-700 hover:bg-red-800 text-gray-200 hover:text-white rounded text-xs font-semibold">
                                        {{ __('Unsubscribe') }}
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('observing-list.subscribe', $list) }}">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 bg-green-700 hover:bg-green-600 text-white rounded text-xs font-semibold">
                                        {{ __('Subscribe') }}
                                    </button>
                                </form>
                            @endif
                        @endif

                        {{-- Edit / Delete (owner) --}}
                        @if ($isOwner)
                            <a href="{{ route('observing-list.edit', $list) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded text-xs font-semibold">
                                {{ __('Edit') }}
                            </a>
                            <form method="POST" action="{{ route('observing-list.items.autofill-notes', $list) }}"
                                  onsubmit="return confirm('{{ __('Fill in the longest observation note for every object that has no note yet?') }}')">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-700 hover:bg-indigo-600 text-white rounded text-xs font-semibold">
                                    {{ __('Autofill notes') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('observing-list.destroy', $list) }}"
                                  onsubmit="return confirm('{{ __('Delete this list and all its items?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-700 hover:bg-red-600 text-white rounded text-xs font-semibold">
                                    {{ __('Delete list') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if ($list->description)
                    <div class="mt-4 text-gray-300 text-sm leading-relaxed">
                        {!! nl2br(e($list->description)) !!}
                    </div>
                @endif

                {{-- Stats row --}}
                <div class="mt-4 flex items-center gap-6 text-sm text-gray-400">
                    <span>{{ $items->total() }} {{ __('objects') }}</span>
                    <span>{{ $list->likes_count }} {{ __('likes') }}</span>
                    <span>{{ $list->comments_count }} {{ __('comments') }}</span>
                    @if ($list->public)
                        <span>{{ $list->subscriptions()->count() }} {{ __('subscribers') }}</span>
                    @endif
                </div>

                {{-- Like button (for non-owners on public lists) --}}
                @if (!$isOwner && $list->public)
                    <div class="mt-3">
                        <button id="like-btn"
                            data-liked="{{ $isLiked ? '1' : '0' }}"
                            data-url="{{ route('observing-list.toggle-like', $list) }}"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded text-xs font-semibold transition-colors
                                {{ $isLiked ? 'bg-pink-700 text-white' : 'bg-gray-700 text-gray-300 hover:bg-pink-700 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span id="like-label">{{ $isLiked ? __('Liked') : __('Like') }}</span>
                            <span id="like-count">{{ $list->likes_count }}</span>
                        </button>
                    </div>
                @endif
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Items list --}}
                <section class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold text-white">{{ __('Objects') }}</h2>
                    </div>

                    @if ($items->isEmpty())
                        <p class="text-gray-400 text-sm">{{ __('This list has no objects yet.') }}</p>
                    @else
                        <div class="bg-gray-800 rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-700">
                                        <th class="text-left px-4 py-2 text-gray-400 font-medium">{{ __('Object') }}</th>
                                        <th class="text-left px-4 py-2 text-gray-400 font-medium hidden md:table-cell">{{ __('Note') }}</th>
                                        @if ($isOwner)
                                            <th class="px-4 py-2 w-16"></th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr class="border-b border-gray-700 hover:bg-gray-750 last:border-0">
                                            <td class="px-4 py-2 text-white">
                                                @if ($item->object_name)
                                                    <a href="{{ route('object.show', ['slug' => \Illuminate\Support\Str::slug($item->object_name)]) }}"
                                                       class="hover:underline font-medium">{{ $item->object_name }}</a>
                                                @else
                                                    <span class="font-medium text-gray-400">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-gray-400 hidden md:table-cell">
                                                {{ $item->item_description ? Str::limit($item->item_description, 80) : '' }}
                                            </td>
                                            @if ($isOwner)
                                                <td class="px-4 py-2 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="{{ route('observing-list.items.edit', [$list, $item->id]) }}"
                                                           class="text-gray-500 hover:text-blue-400"
                                                           title="{{ __('Edit note') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </a>
                                                        <form method="POST"
                                                              action="{{ route('observing-list.items.destroy', [$list, $item->id]) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="text-gray-500 hover:text-red-400"
                                                                title="{{ __('Remove') }}">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $items->links() }}</div>
                    @endif
                </section>

                {{-- Comments sidebar --}}
                <section class="lg:col-span-1">
                    <h2 class="text-lg font-semibold text-white mb-3">{{ __('Comments') }}</h2>

                    @if ($comments->isEmpty())
                        <p class="text-gray-400 text-sm mb-4">{{ __('No comments yet.') }}</p>
                    @else
                        <div class="space-y-3 mb-4">
                            @foreach ($comments as $comment)
                                <div class="bg-gray-800 p-3 rounded-lg border border-gray-700">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-medium text-gray-300">{{ $comment->user->name }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                            @if ($comment->canBeDeletedBy(auth()->user()))
                                                <form method="POST"
                                                      action="{{ route('observing-list.comments.destroy', [$list, $comment->id]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-600 hover:text-red-400" title="{{ __('Delete') }}">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-300">{{ $comment->body }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Add comment form --}}
                    @auth
                        @if (auth()->user()->can('comment', $list))
                            <form method="POST" action="{{ route('observing-list.comments.store', $list) }}">
                                @csrf
                                <textarea
                                    name="body"
                                    rows="3"
                                    required
                                    maxlength="2000"
                                    placeholder="{{ __('Add a comment…') }}"
                                    class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                ></textarea>
                                @error('body')
                                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <button type="submit"
                                    class="mt-2 text-xs px-3 py-1.5 bg-blue-700 hover:bg-blue-600 text-white rounded font-semibold">
                                    {{ __('Post comment') }}
                                </button>
                            </form>
                        @endif
                    @endauth
                </section>

            </div>

            <div class="mt-6">
                <a href="{{ route('observing-lists.index') }}" class="text-gray-400 hover:text-gray-200 text-sm">
                    &larr; {{ __('Back to my lists') }}
                </a>
            </div>

        </div>
    </div>

    {{-- Like button JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('like-btn');
            if (!btn) return;

            btn.addEventListener('click', function () {
                const url = btn.dataset.url;
                const isLiked = btn.dataset.liked === '1';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                })
                .then(res => res.json())
                .then(data => {
                    btn.dataset.liked = data.liked ? '1' : '0';
                    document.getElementById('like-label').textContent = data.liked ? '{{ __('Liked') }}' : '{{ __('Like') }}';
                    document.getElementById('like-count').textContent = data.likes_count;
                    const svg = btn.querySelector('svg');
                    if (svg) svg.setAttribute('fill', data.liked ? 'currentColor' : 'none');
                    btn.classList.toggle('bg-pink-700', data.liked);
                    btn.classList.toggle('text-white', data.liked);
                    btn.classList.toggle('bg-gray-700', !data.liked);
                    btn.classList.toggle('text-gray-300', !data.liked);
                })
                .catch(() => {});
            });
        });
    </script>
</x-app-layout>
