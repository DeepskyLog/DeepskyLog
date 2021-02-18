<div>
    <div>
        @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
        @endif
    </div>

    <form method="POST" action="/target">
        @csrf
        @if ($numberOfNames)
        @for ($cnt=0;$cnt < $numberOfNames;$cnt++) <div class="form-group row">
            @if ($cnt==0) <div class="col-sm-2 col-form-label">
                {{ _i('Object name') }}
            </div>
            @else
            <div class="col-sm-2 col-form-label">
                {{ _i('or object name') }}
            </div>
            @endif
            <div class="col-sm-4">
                <div x-data='' wire:ignore>
                    <x-input.select id="catalog{{ $cnt }}" :options="$allCatalogs" name="catalog{{ $cnt }}" />
                </div>
            </div>
            <div class="col-sm-3">
                <input type="text" placeholder="{{ _i('Enter number in catalog') }}"
                    class="form-control form-control-lg" name="number{{ $cnt }}">
            </div>
</div>
@endfor
@endif

@if ($numberOfConstellations)
@for ($cnt=0;$cnt < $numberOfConstellations;$cnt++) <div class="form-group row">
    @if ($cnt == 0)
    <div class="col-sm-2 col-form-label">{{ _i('In constellation') }}</div>
    @else
    <div class="col-sm-2 col-form-label">{{ _i('or in constellation') }}</div>
    @endif
    <div class="col-sm-4">
        <div x-data='' wire:ignore>
            <x-input.select id="constellation{{ $cnt }}" :options="$constellations" name="constellation{{ $cnt }}" />
        </div>
    </div>
    </div>
    @endfor
    @endif

    @if (!$addExtraSearchParameter)
    {{ _i('Add extra search criteria') }}&nbsp;

    <svg xmlns="http://www.w3.org/2000/svg" wire:click='addSearch' width="16" height="16" fill="currentColor"
        class="bi bi-plus-circle-fill inline" viewBox="0 0 16 16">
        <path
            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z" />
    </svg>
    @endif

    @if ($addExtraSearchParameter)
    {{ _i('Select search criteria to add:') }}
    <div x-data=''>
        <x-input.select-live-wire wire:model="criteria" prettyname="mycriteria" :options="$searchCriteria"
            selected="('criteria')" />
    </div>
    <br />
    @endif

    <br /><br />
    <div class="form-group row">
        <div class="col-sm-10">
            <button type="submit" class="btn btn-primary">{{ _i('Search') }}</button>
            <button type="" class="btn btn-danger" wire:click='clearFields'>{{ _i('Clear fields') }}</button>
        </div>
    </div>
    </form>
    </div>
