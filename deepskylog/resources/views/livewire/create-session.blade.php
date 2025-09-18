<div>
    <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
        <h2 class="text-xl font-semibold leading-tight">
            @if ($update)
                {{ __("Update ") . $name }}
            @else
                {{ __("Create observation session") }}
            @endif
        </h2>

        <div class="mt-2">
            <x-card>

                <form role="form" wire:submit.prevent="save">
                    @csrf

                    <div class="mb-4">
                        <x-input
                            name="name"
                            label="{{ __('Session name') }}"
                            wire:model.live="name"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>

                    {{-- Observer is handled server-side (default current user); field removed from form intentionally --}}

                    <div class="mb-4">
                        @php
                            $usersAsyncRoute = ! empty($otherObservers) ? route('users.select.api', ['selected' => $otherObservers]) : route('users.select.api');
                            $selectedUsers = ! empty($otherObservers) ? \App\Models\User::whereIn('username', $otherObservers)->get() : collect();
                        @endphp
                        <x-select
                            label="{{ __('Other observers') }}"
                            async-data="{{ $usersAsyncRoute }}"
                            multiselect="true"
                            option-label="name"
                            option-value="id"
                            wire:model="otherObservers"
                            multiple
                        >
                            @foreach($selectedUsers as $su)
                                <x-select.option label="{{ $su->name }}" value="{{ $su->username }}" />
                            @endforeach
                        </x-select>
                        <div class="text-xs text-gray-400 mt-1">{{ __('Select additional observers who participated in this session (optional).') }}</div>
                    </div>

                    <div class="mb-4">
                        <x-select
                            label="{{ __('Location') }}"
                            :async-data="route('locations.api')"
                            option-label="name"
                            option-value="id"
                            wire:model="locationid"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-datetime-picker
                                without-time="true"
                                without-timezone="true"
                                name="begindate"
                                label="{{ __('Begin date') }}"
                                wire:model.live="begindate"
                                value="{{ $begindate ?? '' }}"
                            />
                        </div>
                        <div>
                            <x-datetime-picker
                                without-time="true"
                                without-timezone="true"
                                name="enddate"
                                label="{{ __('End date') }}"
                                wire:model.live="enddate"
                                value="{{ $enddate ?? '' }}"
                            />
                        </div>
                    </div>

                    <div class="mb-4 mt-4">
                        <div class="col-span-6 text-sm text-gray-400 mt-1 sm:col-span-5">{{ __("Weather") }}</div>
                        <div wire:ignore>
                            <textarea wire:model.live="weather" id="weather" class="h-40 min-h-fit mt-1 block w-full" name="weather">{{ old('weather') }}</textarea>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="col-span-6 text-sm text-gray-400 mt-1 sm:col-span-5">{{ __("Equipment") }}</div>
                        <div wire:ignore>
                            <textarea wire:model.live="equipment" id="equipment" class="h-32 min-h-fit mt-1 block w-full" name="equipment">{{ old('equipment') }}</textarea>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="col-span-6 text-sm text-gray-400 mt-1 sm:col-span-5">{{ __("Comments") }}</div>
                        <div wire:ignore>
                            {{-- make comments larger for easier editing --}}
                            <textarea wire:model.live="comments" id="comments" class="h-64 min-h-fit mt-1 block w-full" name="comments">{{ old('comments') }}</textarea>
                        </div>
                    </div>

                    <div class="mb-4">
                        <x-input label="{{ __('Image (optional)') }}" type="file" wire:model="photo" />
                        @if (! empty($photo) && is_object($photo))
                            <div class="mt-2">
                                <img src="{{ $photo->temporaryUrl() }}" class="max-h-48 object-contain" alt="Preview" />
                            </div>
                        @elseif (! empty($sessionPreview))
                            <div class="mt-2">
                                <img src="{{ $sessionPreview }}" class="max-h-48 object-contain" alt="Session image" />
                            </div>
                        @elseif (! empty($session) && ! empty($session->picture))
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.ltrim($session->picture, '/')) }}" class="max-h-48 object-contain" alt="Session image" />
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end">
                        <div>
                            <x-button type="submit" class="inline-flex items-center px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white" label="{{ $update ? __('Update session') : __('Create session') }}" />
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
    @if (! $update && isset($inactiveSessions) && $inactiveSessions->isNotEmpty())
        <div class="max-w-screen mx-auto px-2 py-6 sm:px-6 lg:px-8">
            <h3 class="text-lg font-semibold mb-2">{{ __('Draft sessions') }}</h3>
            <x-card>
                <div class="space-y-4">
                    @foreach($inactiveSessions as $s)
                        <div class="flex items-center justify-between p-3 bg-gray-800 rounded">
                            <div>
                                <div class="font-medium text-white">{{ $s->name ?: __('(no name)') }}</div>
                                <div class="text-xs text-gray-400">@if($s->begindate){{ \Carbon\Carbon::parse($s->begindate)->toDateString() }}@endif @if($s->enddate) - {{ \Carbon\Carbon::parse($s->enddate)->toDateString() }}@endif</div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('session.adapt', $s->id) }}" class="inline-flex items-center p-2 rounded bg-yellow-600 hover:bg-yellow-700 text-white" aria-label="{{ __('Adapt this session') }}">{{ __('Use') }}</a>

                                <form method="POST" action="{{ route('session.destroy', $s->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this session?') }}');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center p-2 rounded bg-red-600 hover:bg-red-700 text-white">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    @endif

    @push('scripts')
    {{-- tinyMCE: use the bundled/local copy (avoid CDN); license_key 'gpl' configured in init below --}}
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function initializeTinyMCE() {
                    if (typeof tinymce === 'undefined') return;

                    ['weather', 'equipment', 'comments'].forEach(function (id) {
                        var el = document.getElementById(id);
                        if (!el) return;
                        // Only initialize TinyMCE on actual textarea elements to avoid attaching to other inputs/components
                        if (el.tagName && el.tagName.toLowerCase() !== 'textarea') return;

                        if (tinymce.get(id)) {
                            tinymce.get(id).remove();
                        }

                        var height = 150;
                        if (id === 'equipment') height = 200;
                        if (id === 'comments') height = 400;

                        tinymce.init({
                            selector: '#' + id,
                            menubar: false,
                            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent',
                            license_key: 'gpl',
                            height: height,
                            skin: 'oxide-dark',
                            content_css: 'dark',
                            setup: function (editor) {
                                editor.on('init', function () {
                                    editor.save();
                                    try { @this.set(id, editor.getContent()); } catch (err) { if (typeof Livewire !== 'undefined') Livewire.emit('set'+id.charAt(0).toUpperCase()+id.slice(1), editor.getContent()); }
                                });
                                editor.on('change', function () {
                                    editor.save();
                                    try { @this.set(id, editor.getContent()); } catch (err) { if (typeof Livewire !== 'undefined') Livewire.emit('set'+id.charAt(0).toUpperCase()+id.slice(1), editor.getContent()); }
                                });
                            }
                        });
                    });
                }

                if (typeof tinymce !== 'undefined') {
                    initializeTinyMCE();
                } else {
                    var retryCount = 0;
                    var retryMax = 20;
                    var retryInterval = setInterval(function () {
                        retryCount++;
                        if (typeof tinymce !== 'undefined') {
                            initializeTinyMCE();
                            clearInterval(retryInterval);
                        } else if (retryCount >= retryMax) {
                            clearInterval(retryInterval);
                        }
                    }, 250);
                }

                document.addEventListener('livewire:load', function () {
                    initializeTinyMCE();
                });

                if (typeof Livewire !== 'undefined' && Livewire.hook) {
                    Livewire.hook('message.processed', function () {
                        initializeTinyMCE();
                    });
                } else {
                    window.addEventListener('livewire:load', function () {
                        if (typeof Livewire !== 'undefined' && Livewire.hook) {
                            Livewire.hook('message.processed', function () {
                                initializeTinyMCE();
                            });
                        }
                    });
                }
            });
        </script>
    @endpush
</div>
