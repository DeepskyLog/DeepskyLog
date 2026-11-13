<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("Delete users from DeepskyLog") }}
            </h2>
            <p class="mt-2 text-sm text-gray-400">
                {{ __("Deleting an observer also deletes everything they recorded: observations with their drawings, sessions, instruments, eyepieces, filters, lenses, locations and observing lists. For an observer with data you are asked to type the username. Administrators and database experts can only be deleted after taking them out of that team.") }}
            </p>
        </div>
    </div>
    <!-- Team Member List -->
    <div class="mt-2">
        <x-card>
            <div>
                {{-- Powergrid with all the users --}}
                <livewire:remove-user-table/>
            </div>
        </x-card>
    </div>
</x-app-layout>
