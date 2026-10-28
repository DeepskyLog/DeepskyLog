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
                    <p class="mb-4">
                        {{ __("The sketch of the month is shown from the 8th of the month on, and is chosen from the DeepskyLog sketches of the week of the month before.") }}
                    </p>
                    @foreach (array_merge($errors->get('date'), $errors->get('observation_id')) as $message)
                        <p class="mb-2 text-sm text-red-500">{{ $message }}</p>
                    @endforeach
                    <livewire:choose-sketch-of-the-month :selected="old('date')" />
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
