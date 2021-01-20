<div>
    @if ($changeTitle)
    <label for="name">{{ _i('Name') }}</label>
    <div class="form-group">
        <input type="text" class="form-control" id="name" wire:model="title">
        <p class="text-center {{ strlen($title) >= 85 ? 'text-danger' : '' }}">
            <small>
                {{ strlen($title) . '/100' }}
            </small>
        </p>

    </div>
    <button wire:click='hideTitle' class="btn btn-primary mb-2">

        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-check-circle-fill inline" viewBox="0 0 16 16">
            <path
                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
        </svg>
        {{ _i('Change name') }}
        <br />
    </button>
    @else
    <h2>
        {{ _i('Equipment set: ') . $set->name }}
        <svg xmlns="http://www.w3.org/2000/svg" wire:click='adaptTitle' width="16" height="16" fill="currentColor"
            class="inline bi bi-pencil-fill" viewBox="0 0 16 16">
            <path
                d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z" />
        </svg>
    </h2>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="trix-content">{!! $set->description !!}</div>
        </div>
    </div>

    <br />
    <h4>
        {{ _i('Instruments') }}

        @if ($showInstruments)
        <svg xmlns="http://www.w3.org/2000/svg" wire:click='hideInstruments' width="16" height="16" fill="currentColor"
            class="bi bi-check-circle-fill inline" viewBox="0 0 16 16">
            <path
                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
        </svg>
        @else
        <svg xmlns="http://www.w3.org/2000/svg" wire:click='showInstruments' width="16" height="16" fill="currentColor"
            class="bi bi-plus-circle-fill inline" viewBox="0 0 16 16">
            <path
                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z" />
        </svg>
        @endif
    </h4>

    @if ($showInstruments)
    {{ _i('Select instruments to add to the equipment set') }}

    <br />
    <form>
        @foreach (\App\Models\Instrument::where('user_id', Auth::id())->orderBy('diameter', 'desc')->get() as
        $instrument)
        <div class="form-group form-check">
            <input type="checkbox" wire:model='addInstrument' class="form-check-input" value="{{ $instrument->id }}">
            <label class="form-check-label" for="{{ $instrument->id }}">{{ $instrument->name }}</label>
        </div>
    </form>
    @endforeach
    {{-- {{ $addInstrument }} --}}
    @else
    {{-- Show the selected instruments in the equipment set --}}

    @endif

    {{-- TODO: Add possibility to change the description --}}
    {{-- TODO: Use + button to add new instruments. Show the list with instruments in the set and with the instruments not in the set. --}}
    {{-- TODO: Show the number of instruments in the equipment set? --}}
    {{-- TODO: Show the current eyepieces --}}
    {{-- TODO: Add filters, lenses, eyepieces,  --}}
    <br />
    <h4>
        {{ _i('Eyepieces') }} <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-plus-circle-fill inline" viewBox="0 0 16 16">
            <path
                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z" />
        </svg>
    </h4>
    <br />
    <h4>
        {{ _i('Lenses') }} <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-plus-circle-fill inline" viewBox="0 0 16 16">
            <path
                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z" />
        </svg>
    </h4>
    <br />
    <h4>
        {{ _i('Filters') }} <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-plus-circle-fill inline" viewBox="0 0 16 16">
            <path
                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z" />
        </svg>
    </h4>
</div>