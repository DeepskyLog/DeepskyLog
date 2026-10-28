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
                    <form method="post" action="{{ route('messages.deleteAll') }}" id="delete-all-form" class="ml-2">
                        @csrf
                        <button type="button" id="delete-all-btn" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700">{{ __('Delete all') }}</button>
                    </form>
        </div>
        <div class="text-sm text-gray-400 flex items-center gap-4">
            <div>
                {{ __('Total') }}: <span class="font-medium text-gray-200">{{ $mergedTotal ?? ($totalMessages ?? 0) }}</span>
                &nbsp;|&nbsp;
                {{ __('Unread') }}: <span class="font-medium text-yellow-300">{{ $unreadMessages ?? 0 }}</span>
                &nbsp;|&nbsp;
                {{ __('Received') }}: <span class="font-medium text-gray-200">{{ $receivedCount ?? 0 }}</span>
                &nbsp;|&nbsp;
                {{ __('Sent') }}: <span class="font-medium text-gray-200">{{ $sentGroupsCount ?? 0 }}</span>
            </div>
            <form method="get" action="" class="flex flex-wrap items-center gap-2">
                <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Search messages') }}" aria-label="{{ __('Search messages') }}"
                       class="bg-gray-800 text-white text-xs rounded px-2 py-1 border border-gray-700 w-40" />
                <select name="box" onchange="this.form.submit()" aria-label="{{ __('Show') }}" class="bg-gray-800 text-white text-xs rounded px-2 py-1 appearance-none" style="background-image: none;">
                    <option value="" {{ $box === null ? 'selected' : '' }}>{{ __('Received and sent') }}</option>
                    <option value="received" {{ $box === 'received' ? 'selected' : '' }}>{{ __('Received') }}</option>
                    <option value="sent" {{ $box === 'sent' ? 'selected' : '' }}>{{ __('Sent') }}</option>
                </select>
                <label class="text-xs text-gray-400 flex items-center gap-2">
                    <input type="checkbox" name="unread" value="1" onchange="this.form.submit()" {{ request()->query('unread') == '1' ? 'checked' : '' }} class="h-4 w-4" />
                    <span>{{ __('Only unread') }}</span>
                </label>
                <label for="per_page" class="text-xs text-gray-400">{{ __('Per page') }}</label>
                <select id="per_page" name="per_page" onchange="this.form.submit()" class="bg-gray-800 text-white text-xs rounded px-2 py-1 appearance-none" style="background-image: none;">
                    @foreach([10,20,50,100] as $n)
                        <option value="{{ $n }}" {{ (isset($perPage) && $perPage == $n) ? 'selected' : '' }} class="bg-gray-800 text-white" style="background-color: #0f172a; color: #ffffff;">{{ $n }}</option>
                    @endforeach
                </select>
                {{-- preserve the sort when filtering; the filters themselves are the fields above,
                     and a new filter starts at the first page --}}
                @foreach(request()->except(['per_page', 'unread', 'q', 'box', 'page']) as $k => $v)
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
            @if ($search !== '' && $messages->total() === 0)
                <p class="text-sm text-gray-400 mb-2">{{ __('No messages found for ":search".', ['search' => $search]) }}</p>
            @endif
            {{-- The checkboxes of the rows belong to this form through their form attribute,
                 as the rows hold delete forms of their own. --}}
            <form method="post" action="{{ route('messages.bulk') }}" id="bulk-form" class="flex flex-wrap items-center gap-2 mb-2">
                @csrf
                <span class="text-xs text-gray-400">{{ __('Selected messages') }}:</span>
                <button type="submit" name="action" value="read" class="text-xs bg-gray-600 hover:bg-gray-500 text-white px-2 py-1 rounded">{{ __('Mark read') }}</button>
                <button type="submit" name="action" value="unread" class="text-xs bg-gray-600 hover:bg-gray-500 text-white px-2 py-1 rounded">{{ __('Mark unread') }}</button>
                <button type="submit" name="action" value="delete" id="bulk-delete-btn" class="text-xs bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded">{{ __('Delete') }}</button>
            </form>
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-sm">
                    <thead>
                        <tr class="text-left">
                            <th class="px-2 py-1"><input type="checkbox" id="select-all" class="h-4 w-4" aria-label="{{ __('Select all') }}" /></th>
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
                                <td class="px-2 py-2"><input type="checkbox" name="ids[]" value="{{ $m->id }}" form="bulk-form" class="message-select h-4 w-4" aria-label="{{ __('Select') }}" /></td>
                                <td class="px-2 py-2">
                                    @php $senderUser = $senders[$m->sender] ?? null; @endphp
                                    @php
                                        // If this particular message was a broadcast (receiver == 'all')
                                        // show the branded DeepskyLog name and badge. Otherwise show the
                                        // actual user's full name (linked to their profile).
                                        $displayAsBrand = ($m->receiver ?? '') === 'all';
                                    @endphp
                                    @if($senderUser)
                                        <div class="flex items-center gap-2">
                                            @if(! $displayAsBrand && !empty($senderUser->profile_photo_url) && ($senderUser->name ?? '') !== 'DeepskyLog' && strtolower($senderUser->username ?? '') !== 'admin')
                                                <img src="{{ $senderUser->profile_photo_url }}" alt="{{ $senderUser->name }}" class="h-8 w-8 rounded-full object-cover"/>
                                            @else
                                                <div class="h-8 w-8 rounded-full bg-gray-700 text-xs flex items-center justify-center text-white">
                                                    @if($displayAsBrand || ($senderUser->name ?? '') === 'DeepskyLog' || strtolower($senderUser->username ?? '') === 'admin')
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
                                                <div class="h-8 w-8 rounded-full bg-gray-700 text-xs flex items-center justify-center text-white">{{ mb_strtoupper(mb_substr(html_entity_decode($m->sender, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 0, 1)) }}</div>
                                            @endif
                                            <span class="text-sm text-blue-300">{{ ($m->receiver ?? '') === 'all' ? 'DeepskyLog' : (in_array(strtolower($m->sender), ['admin', 'deepskylog']) ? 'DeepskyLog' : html_entity_decode($m->sender, ENT_QUOTES | ENT_HTML5, 'UTF-8')) }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-2 py-2">
                                    @php
                                        $receiverUser = $senders[$m->receiver] ?? null;
                                        $receivers = $m->_receivers ?? [];
                                    @endphp
                                    @if(count($receivers) > 1)
                                        {{-- A message sent to several observers: an icon with all names as its tooltip,
                                             then the first names and the number of others --}}
                                        @php $allNames = collect($receivers)->map(fn ($r) => $senders[$r]->name ?? html_entity_decode($r, ENT_QUOTES | ENT_HTML5, 'UTF-8'))->join(', '); @endphp
                                        <span class="text-sm text-blue-300 inline-flex flex-wrap items-center gap-x-1" title="{{ __('Sent to :count observers', ['count' => count($receivers)]) }}: {{ $allNames }}">
                                            <x-icon name="user-group" class="h-4 w-4 text-gray-400" />
                                            <span class="sr-only">{{ __('Sent to :count observers', ['count' => count($receivers)]) }}:</span>
                                            @foreach(array_slice($receivers, 0, 3) as $r)
                                                {{ $senders[$r]->name ?? html_entity_decode($r, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}@if(! $loop->last), @endif
                                            @endforeach
                                            @if(count($receivers) > 3)
                                                <span class="text-gray-400">{{ __('+:count more', ['count' => count($receivers) - 3]) }}</span>
                                            @endif
                                        </span>
                                    @elseif(($m->receiver ?? '') === 'all')
                                        <span class="text-sm text-gray-300">{{ __('All') }}</span>
                                    @elseif($receiverUser)
                                        {{-- Show full name and link to profile if available --}}
                                        <a href="/observers/{{ $receiverUser->slug ?? $receiverUser->username }}" class="text-sm font-medium text-blue-300 hover:text-blue-500">{{ $receiverUser->name }}</a>
                                    @elseif(! empty($m->receiver))
                                        <span class="text-sm text-gray-300">{{ html_entity_decode($m->receiver, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</span>
                                    @else
                                        <span class="text-sm text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 font-medium">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('messages.show', $m->id) }}">@if ($m->safe_subject !== ''){{ $m->safe_subject }}@else<span class="text-gray-400">{{ __('(no subject)') }}</span>@endif</a>
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

            @if ($blocked->isNotEmpty())
                <div class="mt-6 border-t border-gray-700 pt-4">
                    <h3 class="text-sm font-semibold text-gray-200">{{ __('Blocked observers') }}</h3>
                    <p class="text-xs text-gray-400 mt-1">{{ __('These observers can not send you messages.') }}</p>
                    <ul class="mt-2 space-y-1">
                        @foreach ($blocked as $block)
                            <li class="flex items-center gap-2 text-sm">
                                <a href="/observers/{{ $block->blockedUser->slug ?? $block->blockedUser->username }}" class="text-blue-300 hover:text-blue-500">{{ $block->blockedUser->name }}</a>
                                <form method="post" action="{{ route('messages.unblock', $block->blocked_user_id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs bg-gray-600 hover:bg-gray-500 text-white px-2 py-1 rounded">{{ __('Unblock') }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
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

                <!-- Delete all confirmation modal -->
                <div id="deleteAllModal" class="fixed inset-0 z-50 bg-black bg-opacity-60 hidden items-center justify-center">
                    <div class="bg-gray-900 rounded-lg p-6 max-w-md w-full mx-4 shadow-lg max-h-[90vh] overflow-auto">
                        <h3 class="text-lg font-semibold text-white">{{ __('Confirm') }}</h3>
                        <p class="mt-2 text-sm text-gray-300">{{ __('Are you sure you want to delete ALL messages? This will hide them from your inbox and cannot be undone.') }}</p>
                        <div class="mt-4 flex justify-end gap-2">
                            <button id="deleteAllCancel" class="px-3 py-2 bg-gray-700 text-white rounded">{{ __('Cancel') }}</button>
                            <button id="deleteAllConfirm" class="px-3 py-2 bg-red-600 text-white rounded">{{ __('Confirm') }}</button>
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
                        var all = document.getElementById('select-all');
                        if (all) {
                            all.addEventListener('change', function(){
                                document.querySelectorAll('.message-select').forEach(function(c){ c.checked = all.checked; });
                            });
                        }
                        var form = document.getElementById('bulk-form');
                        var noneMsg = <?php echo json_encode(__('Select one or more messages first.')); ?>;
                        var deleteMsg = <?php echo json_encode(__('Are you sure you want to delete the selected messages?')); ?>;
                        if (form) {
                            form.addEventListener('submit', function(e){
                                if (! document.querySelector('.message-select:checked')) { e.preventDefault(); alert(noneMsg); return; }
                                if (e.submitter && e.submitter.value === 'delete' && ! confirm(deleteMsg)) { e.preventDefault(); }
                            });
                        }
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
                    (function(){
                        var btn = document.getElementById('delete-all-btn');
                        var modal = document.getElementById('deleteAllModal');
                        var cancel = document.getElementById('deleteAllCancel');
                        var confirm = document.getElementById('deleteAllConfirm');
                        var form = document.getElementById('delete-all-form');

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
