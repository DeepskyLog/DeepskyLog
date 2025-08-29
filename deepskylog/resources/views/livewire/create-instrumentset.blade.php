<div>
    @push('scripts')
        <style>
            [role="listbox"],
            .headlessui-listbox__options,
            .listbox__options,
            .select-dropdown,
            [data-listbox],
            .choices__list,
            .dropdown-menu {
                position: relative;
                z-index: 99999 !important;
            }

            .tox,
            .tox-tinymce,
            .tox-editor-container,
            .tox-toolbar,
            .tox-toolbar__primary {
                z-index: 1000 !important;
            }

            [x-ref="popover"],
            [x-ref="optionsContainer"] {
                z-index: 1000000000000001 !important;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function initializeTinyMCE() {
                    if (typeof tinymce === 'undefined') return;
                    var el = document.querySelector('#description');
                    if (!el) return;

                    if (tinymce.get("description")) {
                        tinymce.get("description").remove();
                    }

                    tinymce.init({
                        selector: "#description",
                        plugins: "lists emoticons quickbars wordcount",
                        toolbar: "undo redo | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | emoticons | wordcount",
                        menubar: false,
                        license_key: 'gpl',
                        quickbars_insert_toolbar: false,
                        quickbars_image_toolbar: false,
                        quickbars_selection_toolbar: "bold italic",
                        skin: "oxide-dark",
                        content_css: "dark",
                        setup: function (editor) {
                            editor.on("init", function () {
                                editor.save();
                                try {
                                    @this.set('description', editor.getContent());
                                } catch (err) {
                                    if (typeof Livewire !== 'undefined') {
                                        Livewire.emit('setDescription', editor.getContent());
                                    }
                                }
                            });

                            editor.on("change", function () {
                                editor.save();
                                try {
                                    @this.set('description', editor.getContent());
                                } catch (err) {
                                    if (typeof Livewire !== 'undefined') {
                                        Livewire.emit('setDescription', editor.getContent());
                                    }
                                }
                            });
                        }
                    });
                }

                if (typeof tinymce !== 'undefined' && document.querySelector('#description')) {
                    initializeTinyMCE();
                } else {
                    var retryCount = 0;
                    var retryMax = 20;
                    var retryInterval = setInterval(function () {
                        retryCount++;
                        if (typeof tinymce !== 'undefined' && document.querySelector('#description')) {
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

    <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
        <h2 class="text-xl font-semibold leading-tight">
            @if ($update)
                {{ __("Update ") . $name }}
            @else
                {{ __("Create a new instrument set") }}
            @endif
        </h2>

        <div class="mt-2">
            <x-card>
                <form role="form" wire:submit="save">
                    @csrf
                    <div class="col-span-6 sm:col-span-5">

                        <x-input
                            name="name"
                            label="{!! __('Name of the instrument set') !!}"
                            type="text"
                            wire:model.live="name"
                            class="mt-1 block w-full"
                            value="{{ old('name') }}"/>

                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror

                    </div>

                    <div class="mt-4">
                        @if(isset($set))
                            <x-select
                                label="{{ __('Instruments in this set') }}"
                                wire:model.live="instruments"
                                :async-data="route('instrument.select.api', ['selected_ids' => implode(',', $set->instruments()->pluck('instruments.id')->toArray())])"
                                option-label="name"
                                option-sub-label="description"
                                option-value="id"
                                multiselect
                            />
                        @else
                            <x-select
                                label="{{ __('Instruments in this set') }}"
                                wire:model.live="instruments"
                                :async-data="route('instrument.select.api')"
                                option-label="name"
                                option-sub-label="description"
                                option-value="id"
                                multiselect
                            />
                        @endif

                        <x-select class="mt-2"
                            label="{{ __('Eyepieces in this set') }}"
                            wire:model.live="eyepieces"
                            :async-data="route('eyepiece.select.api')"
                            option-label="name"
                            option-value="id"
                            multiselect

                        />

                        <x-select class="mt-2"
                            label="{{ __('Lenses in this set') }}"
                            wire:model.live="lenses"
                            :async-data="route('lens.select.api')"
                            option-label="name"
                            option-value="id"
                            multiselect

                        />

                        <x-select class="mt-2"
                            label="{{ __('Filters in this set') }}"
                            wire:model.live="filters"
                            :async-data="route('filter.select.api')"
                            option-label="name"
                            option-value="id"
                            multiselect

                        />

                        <x-select class="mt-2"
                            label="{{ __('Locations to use with this set') }}"
                            wire:model.live="locations"
                            :async-data="route('location.select.api')"
                            option-label="name"
                            option-sub-label="description"
                            option-value="id"
                             multiselect

                        />
                        <div class="col-span-6 mt-4 text-sm text-gray-400 sm:col-span-5">
                            {{ __("Tell something about this instrument set") }}
                        </div>

                        <div class="col-span-6 sm:col-span-5" wire:ignore>
                            <textarea
                                wire:model.live="description"
                                class="h-48 min-h-fit mt-1 block w-full"
                                name="description"
                                id="description"
                            >{{ old('description') }}</textarea>
                        </div>

                    </div>

                    <div class="mt-5">
                        <x-input type="file"
                                 label="{!! __('Upload image') !!}"
                                 wire:model="photo"/>

                        @error('photo') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror

                        @if ($update)
                            @if($set->picture)
                                <img
                                    alt="{{ __('Picture of instrument set') }}"
                                    class="mt-2 h-40 w-40 object-cover"
                                    src="{{ '/storage/'.asset($set->picture) }}">

                            @endif
                        @endif
                        @if ($photo)
                            <img
                                alt="{{ __('Picture of instrument set') }}"
                                class="mt-2 h-40 w-40 object-cover"
                                src="{{ $photo->temporaryUrl() }}">
                        @endif
                    </div>

                    <br/>

                    @if($update)

                        <x-button class="mt-5"
                                  type="submit"
                                  secondary
                                  label="{{ __('Update instrument set') }}"
                        />

                    @else

                        <x-button class="mt-5"
                                  type="submit"
                                  secondary
                                  label="{{ __('Add new instrument set') }}"
                        />
                    @endif
                </form>
            </x-card>
        </div>
    </div>
</div>
