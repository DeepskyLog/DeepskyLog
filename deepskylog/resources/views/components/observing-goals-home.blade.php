@props (["goals"])

{{-- The observer's own observing goals on the home page (App\Models\ObservingGoal::forHomePage()), with their progress --}}
<x-home-section :title="__('Observing goals')" {{ $attributes }}>
    <ul class="divide-y divide-gray-700 px-2 text-sm">
        @foreach ($goals as $goal)
            <li class="py-2">
                <x-observing-goal :goal="$goal" />
            </li>
        @endforeach
    </ul>
    <x-slot name="footer">
        <a
            href="{{ route("observer.show", Auth::user()->slug) }}#observing-goals"
            class="text-blue-400 hover:underline"
            >{{ __("All my observing goals") }} &rarr;</a
        >
    </x-slot>
</x-home-section>
