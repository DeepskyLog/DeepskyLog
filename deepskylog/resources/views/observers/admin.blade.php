<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("Delete users from DeepskyLog") }}
            </h2>
        </div>
    </div>
    <!-- Team Member List -->
    <div class="mt-2">
        <x-card>
            <div class="flex flex-wrap px-5">
                {{-- Powergrid with all the users --}}
                <livewire:remove-user-table />
            </div>
        </x-card>
    </div>
</x-app-layout>
