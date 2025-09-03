<x-app-layout>
    <x-slot name="header">{{ $message->subject ?? __('(no subject)') }}</x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-400">
                {{ __('From') }}:
                <span class="font-medium text-gray-200">
                    @if(isset($senderUser))
                        @php $isDsl = false; @endphp
                        @if(isset($senderUser) && ((($senderUser->name ?? '') === 'DeepskyLog') || strtolower($senderUser->username ?? '') === 'admin'))
                            @php $isDsl = true; @endphp
                        @endif
                        @if(!isset($senderUser) && in_array(strtolower($message->sender), ['admin','deepskylog']))
                            @php $isDsl = true; @endphp
                        @endif

                        @php $nameColor = 'text-blue-300'; @endphp
                        @if($isDsl)
                            <span class="inline-block h-6 w-6 rounded-full bg-gray-700 text-xs flex items-center justify-center text-white mr-2">DSL</span>
                            <span class="text-sm {{ $nameColor }}">DeepskyLog</span>
                        @else
                            @if(!empty($senderUser->profile_photo_url))
                                <img src="{{ $senderUser->profile_photo_url }}" alt="{{ $senderUser->name }}" class="inline-block h-6 w-6 rounded-full object-cover mr-2"/>
                            @endif
                            <a href="/observers/{{ $senderUser->slug ?? $senderUser->username }}" class="text-sm font-semibold {{ $nameColor }} hover:text-blue-500">{{ $senderUser->name }}</a>
                        @endif
                    @else
                        {{ strtolower($message->sender) === 'admin' ? 'DeepskyLog' : $message->sender }}
                    @endif
                </span>
            </div>
            <div class="text-sm text-gray-400">{{ $message->formatted_date }}</div>
        </div>

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
                        <button type="button" onclick="window.location='{{ route('messages.index') }}'" class="text-sm bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded">&larr; {{ __('Back to inbox') }}</button>

                        @php
                            $isDsl = false;
                            if (isset($senderUser)) {
                                if ((($senderUser->name ?? '') === 'DeepskyLog') || strtolower($senderUser->username ?? '') === 'admin') {
                                    $isDsl = true;
                                }
                            } else {
                                if (in_array(strtolower($message->sender), ['admin','deepskylog'])) {
                                    $isDsl = true;
                                }
                            }
                        @endphp

                        @if(! $isDsl)
                            <a href="{{ route('messages.create', ['reply_to' => $message->id, 'to' => $message->sender]) }}" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">{{ __('Reply') }}</a>
                        @endif

                        <form method="post" action="{{ route('messages.destroy', $message->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this message?') }}');">
                            @csrf
                            <button type="submit" class="text-sm bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">{{ __('Delete message') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
