<div>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                @if ($update)
                    {{ __("Update ") . $name }}
                @else
                    {{ __("Create a new filter") }}
                @endif
            </h2>

            <div class="mt-2">
                <x-card>
                    <form
                        role="form"
                        action="{{ route("filter.store") }}"
                        method="POST"
                        wire:submit="save"
                    >
                        @csrf
                        <div class="col-span-6 sm:col-span-5">
                            <x-select
                                label="{{ __('Select the make of the filter, if the make is not in the list, add a new make in the next field.') }}"
                                wire:model="filter_make"
                                x-on:selected="updateMake($event.detail.value)"
                                :async-data="route('filter_makes.api')"
                                option-label="name"
                                option-value="id"
                            />

                            {{-- Or create a new make--}}
                            <x-input
                                name="filter_new_make"
                                label="{!! __('Only add a make for the filter here if the correct make is not available in the dropdown above.') !!}"
                                type="text"
                                wire:model.live="filter_new_make"
                                class="mt-1 block w-full"
                                value=""
                                id="filter_new_make"
                            />

                            {{-- Enter the name of the filter--}}
                            <x-input
                                name="name"
                                label="{!! __('Name of the filter') !!}"
                                type="text"
                                wire:model.live="name"
                                class="mt-1 block w-full"
                                value="{{ old('name') }}"/>

                            <div class="col-span-6 sm:col-span-5">
                                <x-select
                                    label="{{ __('Select the type of the filter.') }}"
                                    wire:model.live="type_id"
                                    :async-data="route('filter_types.api')"
                                    option-label="name"
                                    option-value="id"
                                />

                                @if($type_id == 7)
                                    {{-- If the type is color, also add selection of color, wratten and schott --}}
                                    <div class="col-span-6 sm:col-span-5">
                                        <x-select
                                            label="{{ __('Select the color of the filter.') }}"
                                            wire:model="color_id"
                                            x-on:selected="updateType($event.detail.value)"
                                            :async-data="route('filter_colors.api')"
                                            option-label="name"
                                            option-value="id"
                                        />

                                        <x-input
                                            name="wratten"
                                            label="{{ __('Wratten') }}"
                                            type="text"
                                            class="mt-1 block w-full"
                                            wire:model.live="wratten"
                                            value="{{ old('wratten') }}"
                                        />

                                        <x-input
                                            name="schott"
                                            label="{{ __('Schott') }}"
                                            type="text"
                                            class="mt-1 block w-full"
                                            wire:model.live="schott"
                                            value="{{ old('schott') }}"
                                        />
                                    </div>
                                @endif

                                <div class="mt-5">
                                    <x-input type="file"
                                             label="{!! __('Upload image') !!}"
                                             wire:model="photo"/>

                                    @error('photo') <span class="error">{{ $message }}</span> @enderror

                                    @if ($update)
                                        @if($filter->picture)
                                            <img
                                                alt="{{ __('Picture of filter') }}"
                                                class="mt-2 h-40 w-40 object-cover"
                                                src="{{ '/storage/'.asset($filter->picture) }}">

                                        @endif
                                    @endif
                                    @if ($photo)
                                        <img
                                            alt="{{ __('Picture of filter') }}"
                                            class="mt-2 h-40 w-40 object-cover"
                                            src="{{ $photo->temporaryUrl() }}">
                                    @endif
                                </div>

                                <br/>

                                @if($update)

                                    <x-button class="mt-5"
                                              type="submit"
                                              secondary
                                              label="{{ __('Update filter') }}"
                                    />

                                @else

                                    <x-button class="mt-5"
                                              type="submit"
                                              secondary
                                              label="{{ __('Add new filter') }}"
                                    />
                        @endif
                    </form>
                </x-card>
            </div>
        </div>
    </div>
</div>
