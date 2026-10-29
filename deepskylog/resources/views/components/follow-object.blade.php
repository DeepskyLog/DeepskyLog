@props(["target" => null])

{{-- Follow or unfollow an object: its new observations go into the observer's digest and personal RSS feed --}}
@auth
    @if (! empty($target))
        @php
            $isFollowingObject = \App\Models\ObjectFollow::isFollowedBy(auth()->user(), $target["table"], $target["key"]);
        @endphp

        <form
            method="POST"
            action="{{ route($isFollowingObject ? "object.unfollow" : "object.follow") }}"
            title="{{ __("The new observations of an object you follow appear in the observations of what you follow, your personal RSS feed and your daily message.") }}"
        >
            @csrf
            <input type="hidden" name="table" value="{{ $target["table"] }}" />
            <input type="hidden" name="key" value="{{ $target["key"] }}" />
            @if ($isFollowingObject)
                <x-button type="submit" xs gray icon="bell-slash" label="{{ __('Unfollow') }}" />
            @else
                <x-button type="submit" xs primary icon="bell" label="{{ __('Follow') }}" />
            @endif
        </form>
    @endif
@endauth
