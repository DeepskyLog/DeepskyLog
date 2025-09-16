@props([
    "team",
    "component" => "dropdown.item",
])

<form method="POST" action="{{ route("current-team.update") }}" x-data>
    @method("PUT")
    @csrf

    <!-- Hidden Team ID -->
    <input type="hidden" name="team_id" value="{{ $team->id }}" />

    @php
        if (Auth::user()->isCurrentTeam($team)) {
            $icon = "check-badge";
        } else {
            $icon = "";
        }
    @endphp

    <button type="submit" class="flex items-center w-full text-left px-3 py-2 text-sm text-gray-200 hover:bg-gray-700">
        @if ($icon)
            <x-icon :name="$icon" class="h-4 w-4 mr-2 text-gray-300" />
        @endif

        {{ $team->name }}
    </button>
</form>
