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

    <x-dropdown.item
        href="#"
        icon="{{ $icon }}"
        x-on:click.prevent="$root.submit();"
        label="{{ $team->name }}"
    />
</form>
