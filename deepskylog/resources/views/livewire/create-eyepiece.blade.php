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
                    {{ __("Create a new eyepiece") }}
                @endif
            </h2>
            <div class="mt-2">
                <x-card>
                    <form
                        role="form"
                        action="{{ route("eyepiece.store") }}"
                        method="POST"
                        wire:submit="save"
                    >
                        @csrf

                        <div class="col-span-6 sm:col-span-5">
                            <x-select
                                label="{{ __('Select the make of the eyepiece, if the make is not in the list, add a new make in the next field.') }}"
                                wire:model="eyepiece_make"
                                x-on:selected="$wire.updateMake"
                                :async-data="route('eyepiece_makes.api')"
                                option-label="name"
                                option-value="id"
                            />

                            {{-- Or create a new make--}}
                            @if ($eyepiece_make == null || $eyepiece_make == 1)
                                <x-input
                                    name="eyepiece_new_make"
                                    label="{!! __('Only add a make for the eyepiece here if the correct make is not available in the dropdown above.') !!}"
                                    type="text"
                                    wire:model.live="eyepiece_new_make"
                                    class="mt-1 block w-full"
                                    value=""
                                    x-on:input="$wire.updateMake"
                                    id="eyepiece_new_make"
                                    name="eyepiece_new_make"
                                />
                            @else
                                <x-input
                                    name="eyepiece_new_make"
                                    label="{!! __('Only add a make for the eyepiece here if the correct make is not available in the dropdown above.') !!}"
                                    type="text"
                                    wire:model="eyepiece_new_make"
                                    class="mt-1 block w-full"
                                    value=""
                                    id="eyepiece_new_make"
                                    name="eyepiece_new_make"
                                    readonly
                                />
                            @endif

                            <br/>
                            <x-select
                                label="{{ __('Select the type of the eyepiece, if the make is not in the list, add a new type in the next field.') }}"
                                wire:model="eyepiece_type"
                                x-on:selected="$wire.updateType"
                                :async-data="route('eyepiece_types.api', ['make' => $eyepiece_make])"
                                option-label="name"
                                option-value="id"
                            />

                            {{-- Or create a new type--}}
                            @if ($eyepiece_type == null || $eyepiece_type == 1)
                                <x-input
                                    name="eyepiece_new_type"
                                    label="{!! __('Only add a type for the eyepiece here if the correct type is not available in the dropdown above.') !!}"
                                    type="text"
                                    wire:model.live="eyepiece_new_type"
                                    x-on:input="$wire.updateMake"
                                    class="mt-1 block w-full"
                                    value=""
                                    id="eyepiece_new_type"
                                />
                            @else
                                <x-input
                                    name="eyepiece_new_type"
                                    label="{!! __('Only add a type for the eyepiece here if the correct type is not available in the dropdown above.') !!}"
                                    type="text"
                                    wire:model.live="eyepiece_new_type"
                                    class="mt-1 block w-full"
                                    value=""
                                    id="eyepiece_new_type"
                                    readonly
                                />
                            @endif
                            <x-input
                                name="focal_length_mm"
                                label="{!! __('Focal Length of the eyepiece (in mm)') !!}"
                                type="number"
                                class="mt-1 block w-full"
                                wire:model.live="focal_length_mm"
                                x-on:input="$wire.updateFocalLength"
                                step="0.1"
                                min="0.1"
                                required
                                value="{{ old('focal_length_mm') }}"
                            />

                            <x-input
                                name="max_focal_length_mm"
                                label="{!! __('Maximal focal Length of the eyepiece (in mm) - only for zoom eyepieces') !!}"
                                type="number"
                                class="mt-1 block w-full"
                                wire:model.live="max_focal_length_mm"
                                x-on:input="$wire.updateFocalLength"
                                step="0.1"
                                min="0.1"
                                value="{{ old('max_focal_length_mm') }}"
                            />

                            <x-input
                                name="apparentFOV"
                                label="{!! __('Apparent Field Of View (in degrees)') !!}"
                                type="number"
                                class="mt-1 block w-full"
                                wire:model.live="apparentFOV"
                                step="1"
                                min="0"
                                max="150"
                                required
                                value="{{ old('apparentFOV') }}"
                            />

                            <x-input
                                name="field_stop_mm"
                                label="{!! __('Field stop (in mm)') !!}"
                                type="number"
                                class="mt-1 block w-full"
                                wire:model.live="field_stop_mm"
                                step="0.1"
                                min="0"
                                max="99.9"
                                value="{{ old('field_stop_mm') }}"
                            />

                            {{-- Enter the name of the eyepiece--}}
                            <x-input
                                name="name"
                                label="{!! __('Name of the eyepiece - proposal: ') . $proposed_name !!}"
                                type="text"
                                wire:model.live="name"
                                class="mt-1 block w-full"
                                value="{{ old('name') }}"/>

                            <div class="col-span-6 text-sm mt-4 text-gray-400 sm:col-span-5">
                                {{ __("Tell something about your eyepiece") }}
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
                                @if($eyepiece->picture)
                                    <img
                                        alt="{{ __('Picture of eyepiece') }}"
                                        class="mt-2 h-40 w-40 object-cover"
                                        src="{{ '/storage/'.asset($eyepiece->picture) }}">

                                @endif
                            @endif
                            @if ($photo)
                                <img
                                    alt="{{ __('Picture of eyepiece') }}"
                                    class="mt-2 h-40 w-40 object-cover"
                                    src="{{ $photo->temporaryUrl() }}">
                            @endif
                        </div>

                        <br/>

                        @if($update)

                            <x-button class="mt-5"
                                      type="submit"
                                      secondary
                                      label="{{ __('Update eyepiece') }}"
                            />

                        @else

                            <x-button class="mt-5"
                                      type="submit"
                                      secondary
                                      label="{{ __('Add new eyepiece') }}"
                            />
                        @endif
                    </form>
                </x-card>
            </div>
        </div>
    </div>
</div>
