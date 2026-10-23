<x-app-layout>
    <x-slot name="header">{{ $message->safe_subject ?: __('(no subject)') }}</x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-400">
                {{ __('From') }}:
                <span class="font-medium text-gray-200">
                        @php $nameColor = 'text-blue-300'; @endphp
                        {{-- A broadcast comes from DeepskyLog, whichever administrator sent it
                             (as in the inbox and the notification mail). --}}
                        @if($isBroadcast)
                            <span class="inline-block h-6 w-6 rounded-full bg-gray-700 text-xs flex items-center justify-center text-white mr-2">DSL</span>
                            <span class="text-sm {{ $nameColor }}">DeepskyLog</span>
                        @elseif(isset($senderUser))
                            {{-- If senderUser is a real user account, always show their name (even admins). --}}
                            @if(!empty($senderUser->profile_photo_url))
                                <img src="{{ $senderUser->profile_photo_url }}" alt="{{ $senderUser->name }}" class="inline-block h-6 w-6 rounded-full object-cover mr-2"/>
                            @endif
                            <a href="/observers/{{ $senderUser->slug ?? $senderUser->username }}" class="text-sm font-semibold {{ $nameColor }} hover:text-blue-500">{{ $senderUser->name }}</a>
                        @else
                            {{-- No user account found: map literal legacy senders to DeepskyLog only. --}}
                            @if(in_array(strtolower($message->sender), ['admin','deepskylog']))
                                <span class="inline-block h-6 w-6 rounded-full bg-gray-700 text-xs flex items-center justify-center text-white mr-2">DSL</span>
                                <span class="text-sm {{ $nameColor }}">DeepskyLog</span>
                            @else
                                <span class="text-sm {{ $nameColor }}">{{ html_entity_decode($message->sender, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</span>
                            @endif
                        @endif
                </span>
                <div class="mt-1">
                    {{ __('To') }}:
                    @if($isBroadcast)
                        <span class="text-sm text-gray-200">{{ __('All') }}</span>
                    @else
                        @foreach($recipients as $recipient)
                            @if($recipient['user'])
                                <a href="/observers/{{ $recipient['user']->slug ?? $recipient['user']->username }}" class="text-sm font-semibold {{ $nameColor }} hover:text-blue-500">{{ $recipient['user']->name }}</a>@if(! $loop->last), @endif
                            @else
                                <span class="text-sm text-gray-200">{{ html_entity_decode((string) $recipient['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</span>@if(! $loop->last), @endif
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="text-sm text-gray-400">{{ $message->formatted_date }}</div>
        </div>

        @if ($repliedTo)
            <p class="mt-3 text-sm text-gray-400">
                {{ __('In reply to') }}:
                <a href="{{ route('messages.show', $repliedTo->id) }}" class="text-blue-300 hover:text-blue-500">{{ $repliedTo->safe_subject ?: __('(no subject)') }}</a>
                <span class="text-xs">({{ $repliedTo->formatted_date }})</span>
            </p>
        @endif

    <div class="mt-4 prose prose-invert max-w-none text-gray-200">
            {!! $message->safe_message !!}
        </div>

        {{-- Scoped inline styles to enforce alignment and indentation for legacy messages --}}
        <style>
            /* Target only this message area */
            .prose.prose-invert.max-w-none.text-gray-200 p[align],
            .prose.prose-invert.max-w-none.text-gray-200 p[style*="text-align"],
            .prose.prose-invert.max-w-none.text-gray-200 li[style*="text-align"],
            .prose.prose-invert.max-w-none.text-gray-200 li[align] {
                /* force alignment from inline attributes */
                text-align: inherit !important;
            }

            .prose.prose-invert.max-w-none.text-gray-200 p[style*="margin-left"],
            .prose.prose-invert.max-w-none.text-gray-200 p[style*="text-indent"],
            .prose.prose-invert.max-w-none.text-gray-200 li[style*="margin-left"],
            .prose.prose-invert.max-w-none.text-gray-200 li[style*="text-indent"] {
                margin-left: inherit !important;
                text-indent: inherit !important;
            }

            /* Ensure list markers are visible inside the prose area */
            .prose.prose-invert.max-w-none.text-gray-200 ul,
            .prose.prose-invert.max-w-none.text-gray-200 ol {
                list-style-position: outside !important;
                list-style-type: disc !important;
            }
        </style>

                <div class="mt-6">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('messages.index') }}" class="text-sm bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded">&larr; {{ __('Back to inbox') }}</a>

                        {{-- A sent message is answered to its receivers, a received one to its
                             sender (unless DeepskyLog), or to the sender and the other receivers. --}}
                        @if($replyTo)
                            <a href="{{ route('messages.create', ['reply_to' => $message->id, 'to' => $replyTo]) }}" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">{{ __('Reply') }}</a>
                        @endif
                        @if($replyAllTo)
                            <a href="{{ route('messages.create', ['reply_to' => $message->id, 'to' => $replyAllTo]) }}" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">{{ __('Reply to all') }}</a>
                        @endif

                        @if ($canMarkUnread)
                            <form method="post" action="{{ route('messages.markUnread', $message->id) }}">
                                @csrf
                                <button type="submit" class="text-sm bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded">{{ __('Mark as unread') }}</button>
                            </form>
                        @endif

                        <form method="post" action="{{ route('messages.destroy', $message->id) }}" class="message-delete-form">
                            @csrf
                            <button type="submit" class="text-sm bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">{{ __('Delete message') }}</button>
                        </form>

                        @if ($canBlock)
                            @if ($isBlocked)
                                <form method="post" action="{{ route('messages.unblock', $senderUser->id) }}">
                                    @csrf
                                    <button type="submit" class="text-sm bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded">{{ __('Unblock :name', ['name' => $senderUser->name]) }}</button>
                                </form>
                            @else
                                <form method="post" action="{{ route('messages.block') }}" class="message-block-form">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $senderUser->id }}" />
                                    <button type="submit" class="text-sm bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded">{{ __('Block :name', ['name' => $senderUser->name]) }}</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

                @if ($replies->isNotEmpty())
                    <div class="mt-6 border-t border-gray-700 pt-4">
                        <h3 class="text-sm font-semibold text-gray-200">{{ __('Replies') }}</h3>
                        <ul class="mt-2 space-y-1">
                            @foreach ($replies as $reply)
                                <li class="text-sm">
                                    <a href="{{ route('messages.show', $reply->id) }}" class="text-blue-300 hover:text-blue-500">{{ $reply->safe_subject ?: __('(no subject)') }}</a>
                                    <span class="text-xs text-gray-400">{{ __('by') }} {{ $names[$reply->sender] ?? html_entity_decode($reply->sender, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}, {{ $reply->formatted_date }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <script>
        (function(){
            var deleteMsg = <?php echo json_encode(__('Are you sure you want to delete this message?')); ?>;
            document.querySelectorAll('.message-delete-form').forEach(function(f){
                f.addEventListener('submit', function(e){ if (!confirm(deleteMsg)) { e.preventDefault(); } });
            });
            var blockMsg = <?php echo json_encode(__('This observer will no longer be able to send you messages. Continue?')); ?>;
            document.querySelectorAll('.message-block-form').forEach(function(f){
                f.addEventListener('submit', function(e){ if (!confirm(blockMsg)) { e.preventDefault(); } });
            });
        })();
    </script>
</x-app-layout>
