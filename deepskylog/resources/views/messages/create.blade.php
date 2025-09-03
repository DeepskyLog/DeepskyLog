<x-app-layout>
    <x-slot name="header">{{ __('New message') }}</x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-4">
                <div class="max-w-2xl mx-auto">
                    <div class="bg-gray-900 p-6 rounded-lg shadow">
        @php
            $usersAsyncRoute = request()->query('to') ? route('users.select.api', ['selected' => request()->query('to')]) : route('users.select.api');
            $selectedUser = null;
            if (request()->query('to')) {
                $selectedUser = \App\Models\User::where('username', request()->query('to'))->first();
            }

            // No inline Blade conditionals inside the component tag — use safe Blade expressions in attributes instead.
        @endphp

        <form method="POST" action="{{ route('messages.store') }}">
            @csrf

            <div class="mb-2">
                <x-select
                    label="{{ __('Receiver') }}"
                    async-data="{{ $usersAsyncRoute }}"
                    value="{{ $selectedUser->username ?? '' }}"
                    placeholder="{{ $selectedUser->name ?? '' }}"
                    option-label="name"
                    option-value="id"
                    class="w-full"
                    x-on:selected="document.getElementById('receiver_hidden').value = $event.detail.value"
                >
                    @if($selectedUser)
                        <x-select.option
                            label="{{ $selectedUser->name }}"
                            value="{{ $selectedUser->username }}"
                        />
                    @endif
                </x-select>

                {{-- Hidden input that will actually be submitted with the form. It is kept in sync with the x-select. --}}
                <input type="hidden" id="receiver_hidden" name="receiver" value="{{ old('receiver', request()->query('to', '')) }}" />
            </div>

            <div class="mb-2">
                <x-input
                    name="subject"
                    label="{{ __('Subject') }}"
                    class="w-full"
                    value="{{ old('subject', request()->query('subject', '')) }}"
                />
            </div>

            <div class="mb-2">
                <label for="message" class="block text-sm font-medium text-gray-300">{{ __('Message') }}</label>
                <div class="mt-1">
                    <textarea id="message" name="message" class="w-full h-48 mt-1 block p-2 bg-gray-800 text-gray-100 rounded">{{ old('message', request()->query('message', '')) }}</textarea>
                </div>
            </div>

            <div>
                <x-button type="submit" primary label="{{ __('Send') }}" />
            </div>
        </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function(){
            // If reply_to is present in query params, fetch the original message
            var params = new URLSearchParams(window.location.search);
            var replyTo = params.get('reply_to') || params.get('to');
            if (!replyTo) return;

            // If reply_to looks numeric, treat as message id and fetch reply-data
            if (/^\d+$/.test(replyTo)) {
                var headers = { 'X-Requested-With': 'XMLHttpRequest' };
                var tokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');

                fetch('/messages/' + replyTo + '/reply-data', { headers: headers })
                    .then(function(res){ if (!res.ok) throw new Error('Network error'); return res.json(); })
                    .then(function(json){
                        // set receiver if not already present
                        var recv = document.querySelector('input[name="receiver"]');
                        if (recv && !recv.value) recv.value = json.sender || params.get('to') || '';

                        var subj = document.querySelector('input[name="subject"]');
                        if (subj && (!subj.value || subj.value.trim() === '')) subj.value = json.subject || '';

                        var msg = document.querySelector('textarea[name="message"]');
                        var header = json.header || '';
                        var body = json.message || '';
                        if (msg && (!msg.value || msg.value.trim() === '')) {
                            // For the plain textarea (fallback), set plain quoted text.
                            msg.value = (header ? header + "\n\n" : '') + (body || '') + "\n";

                            // Build HTML prefill for TinyMCE: prefer sanitized HTML from server if available
                            var htmlBody = json.message_html || null;
                            var esc = function (s) { return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); };
                            var headerHtml = header ? '<p>' + esc(header).replace(/\n/g, '<br />') + '<br></p>' : '';
                            var bodyHtml = htmlBody ? htmlBody : (body ? '<p>' + esc(body).replace(/\n/g, '<br />') + '</p>' : '');

                            // Store prefill HTML globally so we can apply it when TinyMCE initializes later.
                            window.replyPrefillHtml = headerHtml + bodyHtml;

                            // If TinyMCE is already initialized, apply immediately.
                            if (typeof tinymce !== 'undefined' && tinymce.get && tinymce.get('message')) {
                                try {
                                    tinymce.get('message').setContent(window.replyPrefillHtml);
                                } catch (e) {
                                    // ignore
                                }
                            }
                        }
                    }).catch(function(e){
                        console.error('Failed to fetch reply data', e);
                    });
            } else {
                // If reply_to is not numeric treat as a username and fill receiver only
                var recv = document.querySelector('input[name="receiver"]');
                if (recv && !recv.value) recv.value = replyTo;
            }
            // If the page was loaded with a `to` query param (username), ensure the hidden input is populated
            var params = new URLSearchParams(window.location.search);
            var toParam = params.get('to');
            if (toParam) {
                var hidden = document.getElementById('receiver_hidden');
                if (hidden && !hidden.value) hidden.value = toParam;
            }

            // Initialize TinyMCE for the #message textarea (retry until tinymce is available)
            function initializeMessageTinyMCE() {
                if (typeof tinymce === 'undefined') return;
                var el = document.querySelector('#message');
                if (!el) return;

                if (tinymce.get('message')) {
                    tinymce.get('message').remove();
                }

                tinymce.init({
                    selector: '#message',
                    plugins: 'lists emoticons quickbars wordcount',
                    toolbar: 'undo redo | bold italic | bullist numlist | emoticons | wordcount',
                    menubar: false,
                    license_key: 'gpl',
                    quickbars_insert_toolbar: false,
                    quickbars_image_toolbar: false,
                    quickbars_selection_toolbar: 'bold italic',
                    skin: 'oxide-dark',
                    content_css: 'dark',
                    setup: function (editor) {
                        editor.on('init', function () {
                            // If a prefill HTML was stored earlier, apply it now so formatting is preserved
                            if (window.replyPrefillHtml) {
                                try { editor.setContent(window.replyPrefillHtml); } catch (e) { /* ignore */ }
                            }
                            editor.save();
                        });
                        editor.on('change', function () {
                            editor.save();
                        });
                    }
                });
            }

            if (typeof tinymce !== 'undefined' && document.querySelector('#message')) {
                initializeMessageTinyMCE();
            } else {
                var retryCountMsg = 0;
                var retryMaxMsg = 20;
                var retryIntervalMsg = setInterval(function () {
                    retryCountMsg++;
                    if (typeof tinymce !== 'undefined' && document.querySelector('#message')) {
                        initializeMessageTinyMCE();
                        clearInterval(retryIntervalMsg);
                    } else if (retryCountMsg >= retryMaxMsg) {
                        clearInterval(retryIntervalMsg);
                    }
                }, 250);
            }
        })();
    </script>
</x-app-layout>
