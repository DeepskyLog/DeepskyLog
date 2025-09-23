@props([
    "team",
    "component" => "dropdown.item",
])
@php
    if (Auth::user()->isCurrentTeam($team)) {
        $icon = "check-badge";
    } else {
        $icon = "";
    }
@endphp

@if ($component === 'link' || $component === 'a')
    {{-- Render a plain link that triggers a full navigation so the header/menu is re-rendered server-side --}}
    <a href="{{ route('current-team.switch', $team->id) }}" class="dsl-switch-team flex items-center w-full text-left px-3 py-2 text-sm text-gray-200 hover:bg-gray-700">
        @if ($icon)
            <x-icon :name="$icon" class="h-4 w-4 mr-2 text-gray-300" />
        @endif

        {{ $team->name }}
    </a>
@else
    {{-- Fallback: preserve the original form-based submission for contexts that expect a form. --}}
    <form method="POST" action="{{ route('current-team.update') }}" x-data>
        @method('PUT')
        @csrf

        <!-- Hidden Team ID -->
        <input type="hidden" name="team_id" value="{{ $team->id }}" />

        <button type="submit" class="flex items-center w-full text-left px-3 py-2 text-sm text-gray-200 hover:bg-gray-700">
            @if ($icon)
                <x-icon :name="$icon" class="h-4 w-4 mr-2 text-gray-300" />
            @endif

            {{ $team->name }}
        </button>
    </form>
@endif
