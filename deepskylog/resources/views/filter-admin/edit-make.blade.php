<x-app-layout>
    <div>
        <div>
            <div
                class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
            >
                <h2 class="text-xl font-semibold leading-tight">
                    {{ __("Update ") . $make->name }}
                </h2>
                <div class="mt-2">
                    <x-card>
                        <form
                            role="form"
                            action="{{ route("filtermake.store") }}"
                            method="POST"
                        >
                            @csrf
                            <div class="col-span-6 sm:col-span-5">
                                <input name="id"
                                       type="hidden"
                                       value="{{ $make->id }}"
                                />
                                <x-input
                                    name="filter_make"
                                    label="{!! __('Make') !!}"
                                    type="text"
                                    class="mt-1 block w-full"
                                    value="{{ $make->name }}"
                                    id="filter_make"
                                />
                                <x-button class="mt-5"
                                          type="submit"
                                          secondary
                                          name="update"
                                          label="{{ __('Update make') }}"
                                />
                            </div>
                        </form>
                        <br/>
                        <hr/>
                        <br/>
                        <form
                            role="form"
                            action="{{ route("filtermake.destroy") }}"
                            method="POST"
                        >
                            @csrf
                            <div class="col-span-6 sm:col-span-5">
                                <input name="id"
                                       type="hidden"
                                       value="{{ $make->id }}"
                                />
                                <x-select class="mt-2"
                                          label="{{ __('Move all filters with the make to an existing make.') }}"
                                          :async-data="route('filter_makes.api')"
                                          option-label="name"
                                          option-value="id"
                                          id="new_make"
                                          name="new_make"
                                />

                                <x-button class="mt-5"
                                          type="submit"
                                          secondary
                                          name="delete"
                                          label="{{ __('Delete make and move filters to the selected make') }}"
                                />

                            </div>

                        </form>
                    </x-card>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
