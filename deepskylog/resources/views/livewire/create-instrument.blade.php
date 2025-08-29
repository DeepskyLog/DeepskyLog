<div>
    <div>
        <div

@push('scripts')
    <style>
        /* Make common dropdown/listbox popovers appear above TinyMCE toolbar/overlays */
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

        /* Reduce TinyMCE root stacking so popovers can appear above it.
           We keep values conservative but use !important to override vendor styles. */
        .tox,
        .tox-tinymce,
        .tox-editor-container,
        .tox-toolbar,
        .tox-toolbar__primary {
            z-index: 1000 !important;
        }

        /* Target WireUI popover root and options container specifically and
           raise them above TinyMCE's auxiliary/blocker z-index (TinyMCE may
           use extremely large z-index values). Use a slightly larger value
           than TinyMCE's internal 'blocker' to ensure stacking correctness. */
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
                                // Prefer direct @this.set to synchronously update the Livewire property
                                @this.set('description', editor.getContent());
                            } catch (err) {
                                // Fallback to emit if @this isn't available
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

            // Try to initialize immediately if possible.
            if (typeof tinymce !== 'undefined' && document.querySelector('#description')) {
                initializeTinyMCE();
            } else {
                // Retry for a short period in case assets are still loading or Livewire re-renders.
                var retryCount = 0;
                var retryMax = 20; // ~5 seconds
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

            // Initialize on Livewire load and after messages processed to survive re-renders.
            document.addEventListener('livewire:load', function () {
                initializeTinyMCE();
            });

            // Attach hook when Livewire becomes available.
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
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                @if ($update)
                    {{ __("Update ") . $name }}
                @else
                    {{ __("Create a new instrument") }}
                @endif
            </h2>
            <div class="mt-2">
                <x-card>
                    <form
                        role="form"
                        action="{{ route("instrument.store") }}"
                        method="POST"
                        wire:submit="save"
                    >
                        @csrf
                        <div class="col-span-6 sm:col-span-5">
                            <x-select
                                label="{{ __('Select the make of the instrument, if the make is not in the list, add a new make in the next field.') }}"
                                wire:model="instrument_make"
                                x-on:selected="updateMake($event.detail.value)"
                                :async-data="route('instrument_makes.api')"
                                option-label="name"
                                option-value="id"
                            />

                            {{-- Or create a new make--}}
                            <x-input
                                name="instrument_new_make"
                                label="{!! __('Only add a make for the instrument here if the correct make is not available in the dropdown above.') !!}"
                                type="text"
                                wire:model.live="instrument_new_make"
                                class="mt-1 block w-full"
                                value=""
                                id="instrument_new_make"
                            />

                            {{-- Enter the name of the instrument--}}
                            <x-input
                                name="name"
                                label="{!! __('Name of the instrument') !!}"
                                type="text"
                                wire:model.live="name"
                                class="mt-1 block w-full"
                                value="{{ old('name') }}"/>

                            {{-- Enter the type of the instrument--}}
                            <x-select class="mt-2"
                                      label="{{ __('Select the type of the instrument.') }}"
                                      wire:model="instrument_type_id"
                                      x-on:selected="$wire.updateFlipFlop"
                                      :async-data="route('instrument_types.api')"
                                      option-label="name"
                                      option-value="id"
                                      id="instrument_type_id"
                                      name="instrument_type_id"
                            />

                            {{-- Add the aperture of the instrument--}}
                            @php if (auth()->user()->showInches) {
                                  $mm_or_inch = __('Aperture of the instrument') . __(' (in inch).');
                            } else {
                                  $mm_or_inch = __('Aperture of the instrument') . __(' (in mm).');
                            }
                            @endphp

                            <x-input
                                name="aperture"
                                label="{{ $mm_or_inch }}"
                                type="number"
                                class="mt-1 block w-full"
                                wire:model.live="aperture_mm"
                                x-on:input="$wire.updateAperture"
                                step="0.1"
                                min="0.1"
                                required
                                value="{{ old('aperture') }}"
                            />

                            {{-- Add the focal length of the instrument--}}
                            @php if (auth()->user()->showInches) {
                                  $mm_or_inch = __('Focal length of the instrument') . __(' (in inch).');
                            } else {
                                  $mm_or_inch = __('Focal length of the instrument') . __(' (in mm).');
                            }
                            @endphp

                            <x-input
                                name="focal_length"
                                label="{{ $mm_or_inch }}"
                                type="number"
                                class="mt-1 block w-full"
                                step="0.1"
                                min="0.0"
                                wire:model.live="focal_length_mm"
                                x-on:input="$wire.updateFd"
                                value="{{ old('focal_length') }}"/>

                            {{-- Add the F/D of the instrument--}}
                            <x-input
                                name="f_d"
                                label="{!! __('F/D of the instrument') !!}"
                                type="number"
                                class="mt-1 block w-full"
                                wire:model.live="f_d"
                                step="0.1"
                                min="0.0"
                                x-on:input="$wire.updateFocal"
                                value="{{ old('f_d') }}"
                            />

                            {{-- Add the fixed magnification of the instrument--}}
                            <x-input
                                name="fixedMagnification"
                                label="{!! __('Fixed magnification of the instrument (for example for finderscopes / binoculars)') !!}"
                                type="number"
                                wire:model.live="fixedMagnification"
                                class="mt-1 block w-full"
                                value="{{ old('fixedMagnification') }}"
                            />

                            {{-- Add the obstruction of the instrument--}}
                            <x-input
                                name="obstruction_perc"
                                label="{!! __('Obstruction of the instrument (in %)') !!}"
                                type="number"
                                class="mt-1 block w-full"
                                wire:model.live="obstruction_perc"
                                step="0.1"
                                min="0.0"
                                value="{{ old('obstruction_perc') }}"
                            />

                            {{-- Add the mount type of the instrument--}}
                            <x-select class="mt-2"
                                      label="{!! __('Select the mount type of the instrument') !!}"
                                      wire:model="mount_type_id"
                                      :async-data="route('mount_types.api')"
                                      option-label="name"
                                      option-value="id"
                            />

                            {{-- Add a toggle to set if the image is flipped--}}
                            <div class="mt-4">
                                <x-toggle name="flipped_image"
                                        label="{!! __('Is the image flipped (mirrored top-bottom)?') !!}"
                                        class="mt-1 block w-full"
                                        wire:model="flipped_image"
                                        id="flipped_image"
                                />

                                {{-- Add a toggle to set if the image is flopped--}}
                                <x-toggle class="mt-2"
                                        name="flopped_image"
                                        label="{!! __('Is the image flopped (mirrored left-right)?') !!}"
                                        class="mt-1 block w-full"
                                        wire:model="flopped_image"
                                        id="flopped_image"
                                />
                            </div>

                            {{-- Enter the description of the instrument --}}
                            <div class="col-span-6 text-sm text-gray-400 mt-4 sm:col-span-5">
                                {{ __("Tell something about your instrument") }}
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

                            @error('photo') <span class="error">{{ $message }}</span> @enderror

                            @if ($update)
                                @if($instrument->picture)
                                    <img
                                        alt="{{ __('Picture of instrument') }}"
                                        class="mt-2 h-40 w-40 object-cover"
                                        src="{{ '/storage/'.asset($instrument->picture) }}">

                                @endif
                            @endif
                            @if ($photo)
                                <img
                                    alt="{{ __('Picture of instrument') }}"
                                    class="mt-2 h-40 w-40 object-cover"
                                    src="{{ $photo->temporaryUrl() }}">
                            @endif
                        </div>

                        <br/>

                        @if($update)

                            <x-button class="mt-5"
                                      type="submit"
                                      secondary
                                      label="{{ __('Update instrument') }}"
                            />

                        @else

                            <x-button class="mt-5"
                                      type="submit"
                                      secondary
                                      label="{{ __('Add new instrument') }}"
                            />
                        @endif
                    </form>
                </x-card>
            </div>
        </div>
    </div>
</div>
