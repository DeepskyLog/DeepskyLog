<div>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("Create a new instrument") }}
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
                                wire:model.live="instrument_make"
                                x-on:selected="updateMake($event.detail.value)"
                                :async-data="route('instrument_makes.api')"
                                option-label="name"
                                option-value="id"
                            />

                            {{-- Or create a new make --}}
                            <x-input
                                name="instrument_new_make"
                                label="{!! __('Only add a make for the instrument here if the correct make is not available in the dropdown above.') !!}"
                                type="text"
                                wire:model.live="instrument_new_make"
                                class="mt-1 block w-full"
                                value=""
                                id="instrument_new_make"
                            />

                            {{-- Enter the name of the instrument --}}
                            <x-input
                                name="name"
                                label="{!! __('Name of the instrument') !!}"
                                type="text"
                                wire:model.live="name"
                                class="mt-1 block w-full"
                                value="{{ old('name') }}"/>

                            {{-- Enter the type of the instrument --}}
                            <x-select class="mt-2"
                                      label="{{ __('Select the type of the instrument.') }}"
                                      wire:model.live="instrument_type_id"
                                      x-on:selected="$wire.updateFlipFlop"
                                      :async-data="route('instrument_types.api')"
                                      option-label="name"
                                      option-value="id"
                                      id="instrument_type_id"
                            />

                            {{-- Add the aperture of the instrument --}}
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
                                min="0.0"
                                required
                                value="{{ old('aperture') }}"
                            />

                            {{-- Add the focal length of the instrument --}}
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

                            {{-- Add the F/D of the instrument --}}
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

                            {{-- Add the fixed magnification of the instrument --}}
                            <x-input
                                name="fixed_mag"
                                label="{!! __('Fixed magnification of the instrument (for examples for finderscopes / binoculars)') !!}"
                                type="number"
                                wire:model.live="fixed_mag"
                                class="mt-1 block w-full"
                                value="{{ old('fixed_mag') }}"
                            />

                            {{-- Add the obstruction of the instrument --}}
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

                            {{-- Add the mount type of the instrument --}}
                            <x-select class="mt-2"
                                      label="{!! __('Select the mount type of the instrument') !!}"
                                      wire:model.live="mount_type_id"
                                      :async-data="route('mount_types.api')"
                                      option-label="name"
                                      option-value="id"
                            />

                            {{-- Add a toggle to set if the image is flipped --}}
                            <x-toggle class="mt-2"
                                      name="flipped_image"
                                      label="{!! __('Is the image flipped (mirrored top-bottom)?') !!}"
                                      class="mt-1 block w-full"
                                      wire:model="flipped_image"
                                      id="flipped_image"
                            />

                            {{-- Add a toggle to set if the image is flopped --}}
                            <x-toggle class="mt-2"
                                      name="flopped_image"
                                      label="{!! __('Is the image flopped (mirrored left-right)?') !!}"
                                      class="mt-1 block w-full"
                                      wire:model="flopped_image"
                                      id="flopped_image"
                            />
                        </div>

                        <div class="mt-5">
                            <x-input type="file"
                                     label="{!! __('Upload image') !!}"
                                     wire:model="photo"/>

                            @error('photo') <span class="error">{{ $message }}</span> @enderror

                            @if ($photo)
                                <img
                                    alt="{{ __('Picture of instrument') }}"
                                    class="mt-2 h-40 w-40 object-cover"
                                    src="{{ $photo->temporaryUrl() }}">
                            @endif
                        </div>

                        <br/>

                        <x-button class="mt-5"
                                  type="submit"
                                  secondary
                                  label="{{ __('Add new instrument') }}"
                        />
                    </form>
                </x-card>
            </div>
        </div>
    </div>
</div>
