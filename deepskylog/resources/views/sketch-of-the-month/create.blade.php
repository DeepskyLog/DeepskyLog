<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("Add DeepskyLog Sketch of the month") }}
            </h2>
            <div class="mt-2">
                <x-card>
                    <div class="mb-4 flex flex-wrap">
                        {{ __("At this moment, this form is very basic.  If a comet sketch is the sketch of the month, enter the negative ID.  In the future, the option to add a DeepskyLog sketch of the month will be included in the details of an observation.") }}
                    </div>
                    <form
                        role="form"
                        action="{{ route("sketch-of-the-month") }}"
                        method="POST"
                    >
                        @csrf
                        <div class="col-span-6 sm:col-span-5">
                            <x-input
                                name="observation_id"
                                label="{{ __('Observation ID') }}"
                                type="number"
                                class="mt-1 block w-full"
                                required
                                value="{{ old('observation_id') }}"
                            />
                            <x-input-error for="error" />
                            @error("error")
                                <p class="mt-1 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <div class="col-span-4 mb-4 mt-4 sm:col-span-3">
                            <x-datetime-picker
                                without-time="true"
                                name="date"
                                label="{{ __('Date') }}"
                                required
                                value="{{old('date') }}"
                            />
                        </div>

                        <x-button
                            type="submit"
                            secondary
                            label="{{ __('Add sketch of the month') }}"
                        />
                    </form>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
