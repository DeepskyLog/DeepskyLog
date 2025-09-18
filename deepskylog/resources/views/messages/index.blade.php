<x-app-layout>
    <x-slot name="header">{{ __('Messages') }}</x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
            <a href="{{ route('messages.create') }}" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">{{ __('New Message') }}</a>
            <form method="post" action="{{ route('messages.markAllRead') }}" id="mark-all-read-form">
                @csrf
                <button type="button" id="mark-all-read-btn" class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm font-medium rounded hover:bg-gray-500">{{ __('Mark all read') }}</button>
            </form>
        </div>
        <div class="text-sm text-gray-400 flex items-center gap-4">
            <div>
                {{ __('Total') }}: <span class="font-medium text-gray-200">{{ $totalMessages ?? 0 }}</span>
                &nbsp;|&nbsp;
                {{ __('Unread') }}: <span class="font-medium text-yellow-300">{{ $unreadMessages ?? 0 }}</span>
            </div>
            <form method="get" action="" class="flex items-center gap-2">
                <label for="per_page" class="text-xs text-gray-400">{{ __('Per page') }}</label>
                <select id="per_page" name="per_page" onchange="this.form.submit()" class="bg-gray-800 text-white text-xs rounded px-2 py-1 appearance-none" style="background-image: none;">
                    @foreach([10,20,50,100] as $n)
                        <option value="{{ $n }}" {{ (isset($perPage) && $perPage == $n) ? 'selected' : '' }} class="bg-gray-800 text-white" style="background-color: #0f172a; color: #ffffff;">{{ $n }}</option>
                    @endforeach
                </select>
                {{-- preserve other query params (sort/direction) when changing per_page --}} 
                @foreach(request()->except('per_page') as $k => $v)
                    @if(is_array($v))
                        @foreach($v as $item)
                            <input type="hidden" name="{{ $k }}[]" value="{{ $item }}" />
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}" />
                    @endif
                @endforeach
            </form>
        </div>
        </div>

        
    </div>

        <div class="mt-4">
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-sm">
                    <thead>
                        <tr class="text-left">
                            @php
                                $currentSort = request()->query('sort');
                                $currentDirection = strtolower(request()->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
                            @endphp
                            <th class="px-2 py-1">
                                <a class="inline-flex items-center gap-1 text-sm font-medium {{ $currentSort === 'from' ? 'text-yellow-300' : 'text-blue-400 hover:text-blue-600' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'from', 'direction' => ($currentSort === 'from' && $currentDirection === 'asc') ? 'desc' : 'asc']) }}">{{ __('From') }}{{ $currentSort === 'from' ? ($currentDirection === 'asc' ? ' ▲' : ' ▼') : '' }}</a>
                            </th>
                            <th class="px-2 py-1">
                                <a class="inline-flex items-center gap-1 text-sm font-medium {{ $currentSort === 'to' ? 'text-yellow-300' : 'text-blue-400 hover:text-blue-600' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'to', 'direction' => ($currentSort === 'to' && $currentDirection === 'asc') ? 'desc' : 'asc']) }}">{{ __('To') }}{{ $currentSort === 'to' ? ($currentDirection === 'asc' ? ' ▲' : ' ▼') : '' }}</a>
                            </th>
                            <th class="px-2 py-1">
                                <a class="inline-flex items-center gap-1 text-sm font-medium {{ $currentSort === 'subject' ? 'text-yellow-300' : 'text-blue-400 hover:text-blue-600' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'subject', 'direction' => ($currentSort === 'subject' && $currentDirection === 'asc') ? 'desc' : 'asc']) }}">{{ __('Subject') }}{{ $currentSort === 'subject' ? ($currentDirection === 'asc' ? ' ▲' : ' ▼') : '' }}</a>
                            </th>
                            <th class="px-2 py-1">{{ __('Preview') }}</th>
                            <th class="px-2 py-1">
                                <a class="inline-flex items-center gap-1 text-sm font-medium {{ $currentSort === 'date' ? 'text-yellow-300' : 'text-blue-400 hover:text-blue-600' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'date', 'direction' => ($currentSort === 'date' && $currentDirection === 'asc') ? 'desc' : 'asc']) }}">{{ __('Date') }}{{ $currentSort === 'date' ? ($currentDirection === 'asc' ? ' ▲' : ' ▼') : '' }}</a>
                            </th>
                            <th class="px-2 py-1">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $m)
                            @php $isRead = $read->contains($m->id); @endphp
                            @php $isSent = ($m->sender === auth()->user()->username); @endphp
                            <tr class="border-t {{ $isRead ? '' : 'bg-gray-800' }} {{ $isSent ? 'bg-gray-800/40' : '' }}">
                                <td class="px-2 py-2">
                                    @php $senderUser = $senders[$m->sender] ?? null; @endphp
                                    @if($senderUser)
                                        <div class="flex items-center gap-2">
                                            @if(!empty($senderUser->profile_photo_url) && ($senderUser->name ?? '') !== 'DeepskyLog' && strtolower($senderUser->username ?? '') !== 'admin')
                                                <img src="{{ $senderUser->profile_photo_url }}" alt="{{ $senderUser->name }}" class="h-8 w-8 rounded-full object-cover"/>
                                            @else
                                                <div class="h-8 w-8 rounded-full bg-gray-700 text-xs flex items-center justify-center text-white">
                                                    @if(($senderUser->name ?? '') === 'DeepskyLog' || strtolower($senderUser->username ?? '') === 'admin')
                                                        DSL
                                                    @else
                                                        {{ strtoupper(mb_substr($senderUser->name, 0, 1)) }}
                                                    @endif
                                                </div>
                                            @endif
                                            @php
                                                // unified color for sender names
                                                $nameColor = 'text-blue-300';
                                            @endphp
                                            @php
                                                // If this particular message was a broadcast (receiver == 'all')
                                                // show the branded DeepskyLog name. Otherwise show the actual
                                                // user's full name (linked to their profile).
                                                $displayAsBrand = ($m->receiver ?? '') === 'all';
                                            @endphp
                                            @if($displayAsBrand || strtolower($senderUser->username ?? '') === 'admin')
                                                <span class="text-sm {{ $nameColor }}">{{ $displayAsBrand ? 'DeepskyLog' : $senderUser->name }}</span>
                                            @else
                                                <a href="/observers/{{ $senderUser->slug ?? $senderUser->username }}" class="text-sm font-semibold {{ $nameColor }} hover:text-blue-500">{{ $senderUser->name }}</a>
                                            @endif
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            @if(in_array(strtolower($m->sender), ['admin', 'deepskylog']))
                                                <div class="h-8 w-8 rounded-full bg-gray-700 text-xs flex items-center justify-center text-white">DSL</div>
                                            @else
                                                <div class="h-8 w-8 rounded-full bg-gray-700 text-xs flex items-center justify-center text-white">{{ strtoupper(mb_substr($m->sender, 0, 1)) }}</div>
                                            @endif
                                            <span class="text-sm text-blue-300">{{ ($m->receiver ?? '') === 'all' ? 'DeepskyLog' : (in_array(strtolower($m->sender), ['admin', 'deepskylog']) ? 'DeepskyLog' : $m->sender) }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-2 py-2">
                                    @php $receiverUser = $senders[$m->receiver] ?? null; @endphp
                                    @if(($m->receiver ?? '') === 'all')
                                        <span class="text-sm text-gray-300">{{ __('All') }}</span>
                                    @elseif($receiverUser)
                                        {{-- Show full name and link to profile if available --}}
                                        <a href="/observers/{{ $receiverUser->slug ?? $receiverUser->username }}" class="text-sm font-medium text-blue-300 hover:text-blue-500">{{ $receiverUser->name }}</a>
                                    @elseif(! empty($m->receiver))
                                        <span class="text-sm text-gray-300">{{ $m->receiver }}</span>
                                    @else
                                        <span class="text-sm text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 font-medium">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('messages.show', $m->id) }}">{!! $m->safe_subject ?: '<span class="text-gray-400">'.__('(no subject)').'</span>' !!}</a>
                                        @if (! $isRead && ! $isSent)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-600 text-white shadow-sm ring-2 ring-red-300 animate-pulse uppercase tracking-wide" title="{{ __('New message') }}" aria-label="{{ __('New message') }}">{{ __('New') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 py-2 text-gray-400">{{ $m->safe_preview }}</td>
                                <td class="px-2 py-2 text-xs text-gray-400">{{ $m->formatted_date }}</td>
                                <td class="px-2 py-2">
                                    <div class="flex items-center gap-2">
                                        @php $isGroup = isset($m->_is_group) && $m->_is_group; @endphp
                                        <form method="post" action="{{ $isGroup ? route('messages.destroyGroup', $m->id) : route('messages.destroy', $m->id) }}" class="message-delete-form">
                                            @csrf
                                            <input type="hidden" name="page" value="{{ request()->query('page', 1) }}" />
                                            <button type="submit" class="text-xs bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded">{{  __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $messages->links() }}
            </div>
                </div>

                <!-- Confirmation modal -->
                <div id="markAllReadModal" class="fixed inset-0 z-50 bg-black bg-opacity-60 hidden items-center justify-center">
                    <div class="bg-gray-900 rounded-lg p-6 max-w-md w-full mx-4 shadow-lg max-h-[90vh] overflow-auto">
                        <h3 class="text-lg font-semibold text-white">{{ __('Confirm') }}</h3>
                        <p class="mt-2 text-sm text-gray-300">{{ __('Are you sure you want to mark all messages as read? This cannot be undone.') }}</p>
                        <div class="mt-4 flex justify-end gap-2">
                            <button id="markAllReadCancel" class="px-3 py-2 bg-gray-700 text-white rounded">{{ __('Cancel') }}</button>
                            <button id="markAllReadConfirm" class="px-3 py-2 bg-red-600 text-white rounded">{{ __('Confirm') }}</button>
                        </div>
                    </div>
                </div>

                <script>
                    // confirmation handler for delete forms (localized message)
                    (function(){
                        var deleteMsg = <?php echo json_encode(__('Are you sure you want to delete this message?')); ?>;
                        document.querySelectorAll('.message-delete-form').forEach(function(f){
                            f.addEventListener('submit', function(e){ if (!confirm(deleteMsg)) { e.preventDefault(); } });
                        });
                    })();
                    (function(){
                        var btn = document.getElementById('mark-all-read-btn');
                        var modal = document.getElementById('markAllReadModal');
                        var cancel = document.getElementById('markAllReadCancel');
                        var confirm = document.getElementById('markAllReadConfirm');
                        var form = document.getElementById('mark-all-read-form');

                        if(btn && modal && cancel && confirm && form) {
                            btn.addEventListener('click', function(){ document.body.classList.add('overflow-hidden'); modal.classList.remove('hidden'); modal.classList.add('flex'); });
                            cancel.addEventListener('click', function(){ document.body.classList.remove('overflow-hidden'); modal.classList.add('hidden'); modal.classList.remove('flex'); });
                            confirm.addEventListener('click', function(){ document.body.classList.remove('overflow-hidden'); form.submit(); });
                        }
                    })();
                </script>
            </div>
        </div>
    </div>
</x-app-layout>
