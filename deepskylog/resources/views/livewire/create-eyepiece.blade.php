<div>
    <div>
        <div
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
                                    wire:model="eyepiece_new_make"
                                    class="mt-1 block w-full"
                                    value=""
                                    x-on:selected="$wire.updateMake"
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

                            {{ $eyepiece_new_make }}

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
                            <x-input
                                name="eyepiece_new_type"
                                label="{!! __('Only add a type for the eyepiece here if the correct type is not available in the dropdown above.') !!}"
                                type="text"
                                wire:model.live="eyepiece_new_type"
                                class="mt-1 block w-full"
                                value=""
                                id="eyepiece_new_type"
                            />

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
                                required
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
                                required
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
                        </div>
                    </form>
                </x-card>
            </div>
        </div>
    </div>
</div>
