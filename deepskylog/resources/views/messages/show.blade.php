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

        <div class="mt-4 prose max-w-none text-gray-200">
            {!! $message->safe_message !!}
        </div>

                <div class="mt-6">
                    <a href="{{ route('messages.index') }}" class="text-sm text-gray-400">&larr; {{ __('Back to inbox') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
