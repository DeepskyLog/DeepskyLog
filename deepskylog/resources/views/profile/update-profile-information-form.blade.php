<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profile Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your account\'s profile information and email address.') }}
    </x-slot>

    @if (auth()->user()->country == '')
        @push('scripts')
            <script>
                window.onload = function() {
                    var title = '{{ __('Missing information!') }}';
                    var description = '{{ __('Your country of residence is not set. Please set it on this page!') }}';
                    window.$wireui.notify({
                        title: title,
                        description: description,
                        icon: 'warning'
                    })
                }
            </script>
        @endpush
    @elseif (auth()->user()->about == '')
        @push('scripts')
            <script>
                window.onload = function() {
                    var title = '{{ __('Missing information!') }}';
                    var description = '{{ __('Please provide some information about your astronomical interests!') }}';
                    window.$wireui.notify({
                        title: title,
                        description: description,
                        icon: 'warning'
                    })
                }
            </script>
        @endpush
    @endif

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{ photoName: null, photoPreview: null }" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input type="file" class="hidden" wire:model="photo" x-ref="photo"
                    x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                <x-label for="photo" value="{{ __('Photo') }}" />

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}"
                        class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                        x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <br />
                <x-button type="submit" label="{{ __('Select A New Photo') }}"
                    x-on:click.prevent="$refs.photo.click()" />

                @if ($this->user->profile_photo_path)
                    <x-button type="submit" label="{{ __('Remove Photo') }}" wire:click="deleteProfilePhoto" />
                @endif

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-input id="name" label="{{ __('Name') }}" type="text" class="mt-1 block w-full"
                wire:model.defer="state.name" autocomplete="name" />
            <x-input-error for="name" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-input id="email" label="{{ __('Email') }}" type="email" class="mt-1 block w-full"
                wire:model.defer="state.email" />
            <x-input-error for="email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) &&
                    !$this->user->hasVerifiedEmail())
                <p class="text-sm mt-2">
                    {{ __('Your email address is unverified.') }}

                    <button type="button" class="underline text-sm text-gray-400 hover:text-gray-500"
                        wire:click.prevent="sendEmailVerification">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p v-show="verificationLinkSent" class="mt-2 font-medium text-sm text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            @endif
        </div>

        <!-- Username -->
        <div class="col-span-6 sm:col-span-4">
            <x-input id="username" label="{{ __('Username') }}" disabled type="text" class="mt-1 block w-full"
                value="{{ $this->user->username }}" />
        </div>

        <!-- Send messages as emails -->
        <div class="col-span-6 sm:col-span-4">

            @if ($this->user->sendMail)
                <x-toggle label="{{ __('Send messages as email') }}" name="sendMail" id="sendMail"
                    wire:model.defer="state.sendMail" checked />
            @else
                <x-toggle label="{{ __('Send messages as email') }}" name="sendMail" id="sendMail"
                    wire:model.defer="state.sendMail" />
            @endif
            &nbsp;

        </div>

        {{-- Country of residence --}}
        <div class="col-span-6 sm:col-span-4">
            <x-select label="{{ __('Country of residence') }}" wire:model.defer="state.country" :async-data="route('countries.index')"
                option-label="name" option-value="id" />
        </div>

        {{-- About --}}
        <div class="col-span-6 sm:col-span-5 text-sm text-gray-400">
            {{ __('Tell something about your astronomical interests') }}
        </div>
        <div class="col-span-6 sm:col-span-5" wire:ignore>
            <textarea wire:model="state.about" class="min-h-fit h-48 " name="message" id="message"></textarea>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button type="submit" secondary label="{{ __('Save') }}" wire:loading.attr="disabled"
            wire:target="photo" />
    </x-slot>
</x-form-section>

@push('scripts')
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#message', // Replace this CSS selector to match the placeholder element for TinyMCE
            plugins: 'lists emoticons quickbars wordcount',
            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | emoticons | wordcount',
            menubar: false,
            quickbars_insert_toolbar: false,
            quickbars_image_toolbar: false,
            quickbars_selection_toolbar: 'bold italic',
            skin: "oxide-dark",
            content_css: "dark",
            forced_root_block: false,
            setup: function(editor) {
                editor.on('init change', function() {
                    editor.save();
                });
                editor.on('change', function(e) {
                    @this.set("state.about", editor.getContent());
                });
            }
        });
    </script>
@endpush
