<div>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                @if ($update)
                    {{ __("Update ") . $name }}
                @else
                    {{ __("Create a new lens") }}
                @endif
            </h2>
            <div class="mt-2">
                <x-card>
                    <form
                        role="form"
                        action="{{ route("lens.store") }}"
                        method="POST"
                        wire:submit="save"
                    >
                        @csrf
                        <div class="col-span-6 sm:col-span-5">
                            <x-select
                                label="{{ __('Select the make of the lens, if the make is not in the list, add a new make in the next field.') }}"
                                wire:model="lens_make"
                                x-on:selected="updateMake($event.detail.value)"
                                :async-data="route('lens_makes.api')"
                                option-label="name"
                                option-value="id"
                            />

                            {{-- Or create a new make--}}
                            <x-input
                                name="lens_new_make"
                                label="{!! __('Only add a make for the lens here if the correct make is not available in the dropdown above.') !!}"
                                type="text"
                                wire:model.live="lens_new_make"
                                class="mt-1 block w-full"
                                value=""
                                id="lens_new_make"
                            />

                            {{-- Enter the name of the lens--}}
                            <x-input
                                name="name"
                                label="{!! __('Name of the lens') !!}"
                                type="text"
                                wire:model.live="name"
                                class="mt-1 block w-full"
                                value="{{ old('name') }}"/>

                            <x-input
                                name="factor"
                                label="{{ __('Factor of the lens') }}"
                                type="number"
                                class="mt-1 block w-full"
                                wire:model.live="factor"
                                step="0.01"
                                min="0.1"
                                required
                                value="{{ old('factor') }}"
                            />

                            {{-- Description of the lens --}}
                            <div class="mt-4">
                                <x-label for="description" :value="__('Description')" />
                                <textarea
                                    id="description"
                                    name="description"
                                    rows="3"
                                    class="mt-1 block w-full"
                                    placeholder="{{ __('Enter description') }}"
                                    wire:model.live="description"
                                ></textarea>
                            </div>

                        </div>

                        <div class="mt-5">
                            <x-input type="file"
                                     label="{!! __('Upload image') !!}"
                                     wire:model="photo"/>

                            @error('photo') <span class="error">{{ $message }}</span> @enderror

                            @if ($update)
                                @if($lens->picture)
                                    <img
                                        alt="{{ __('Picture of lens') }}"
                                        class="mt-2 h-40 w-40 object-cover"
                                        src="{{ '/storage/'.asset($lens->picture) }}">

                                @endif
                            @endif
                            @if ($photo)
                                <img
                                    alt="{{ __('Picture of lens') }}"
                                    class="mt-2 h-40 w-40 object-cover"
                                    src="{{ $photo->temporaryUrl() }}">
                            @endif
                        </div>

                        <br/>

                        @if($update)

                            <x-button class="mt-5"
                                      type="submit"
                                      secondary
                                      label="{{ __('Update lens') }}"
                            />

                        @else

                            <x-button class="mt-5"
                                      type="submit"
                                      secondary
                                      label="{{ __('Add new lens') }}"
                            />
                        @endif
                    </form>
                </x-card>
            </div>
        </div>
    </div>
</div>
