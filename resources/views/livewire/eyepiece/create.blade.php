<div>
    <form role="form" wire:submit.prevent="save">

        @if(!$update)
        {{-- The selection of an existing eyepiece --}}
        <div class="form-group">
            <label for="catalog">{{ _i("Select an existing eyepiece") }}</label>

            @php
            $allEyepieces = [0 => ''] + \App\Models\Eyepiece::all()->unique('name')->pluck('name', 'id')->toArray();
            @endphp
            <div class="form">
                <div x-data=''>
                    <x-input.select-live-wire-collection wire:model="sel_eyepiece" prettyname="myeyepiece"
                        :options="$allEyepieces" selected="('sel_eyepiece')" />
                </div>
            </div>
        </div>

        {{ _i("or specify your eyepiece details manually") }}
        <br /><br />
        @endif

        {{-- The name of the eyepiece --}}
        <div class="form-group name">
            <label for="name">{{ _i("Name") }}</label>
            <input wire:model="name" type="text" required class="form-control @error('name') is-invalid @enderror"
                maxlength="64" name="name" size="30" placeholder="Televue 31mm Nagler"
                value="@if ($eyepiece->name){{ $eyepiece->name }}@else{{ old('name') }}@endif" />
            @error('name') <span class="small text-error">{{ $message }}<br /></span> @enderror

            <span class="help-block">{{ _i("e.g. Televue 13mm Ethos") }}</span>
        </div>

        {{-- The generic name of the eyepiece --}}
        <div class="form-group">
            <label for="name">{{ _i("Generic name") }}</label>
            <input wire:model="genericName" type="text" class="form-control" readonly maxlength="64" name="genericname"
                size="30" />
        </div>

        {{-- The focal length of the eyepiece --}}
        <div class="form-group focalLength">
            <label for="name">{{ _i("Focal length") }}</label>
            <div class="input-group mb-3">
                <input wire:model="focalLength" type="number" placeholder="31" required max="99.9" min="1.0" step="0.1"
                    class="form-control @error('focalLength') is-invalid @enderror" maxlength="5" name="focalLength"
                    size="30"
                    value="@if ($eyepiece->focalLength){{ $eyepiece->focalLength }}@else{{ old('focalLength') }}@endif" />
                <div class="input-group-append">
                    <span class="input-group-text">mm</span>
                </div>
            </div>
            @error('focalLength') <span class="small text-error">{{ $message }}<br /></span> @enderror
        </div>

        {{-- The apparent Field of View --}}
        <div class="form-group apparentFOV">
            <label for="name">{{ _i("Apparent Field of View") }}</label>
            <div class="input-group mb-3">
                <input wire:model="apparentFov" type="number" placeholder="82" required max="150" min="20"
                    class="form-control @error('apparentFov') is-invalid @enderror" maxlength="5" name="apparentFOV"
                    size="30"
                    value="@if ($eyepiece->apparentFOV){{ $eyepiece->apparentFOV }}@else{{ old('apparentFOV') }}@endif" />
                <div class="input-group-append">
                    <span class="input-group-text">&deg;</span>
                </div>
            </div>
            @error('apparentFov') <span class="small text-error">{{ $message }}<br /></span> @enderror
        </div>

        {{-- The maximum focal length for zoom eyepieces --}}
        <div class="form-group maxFocalLength">
            <label for="name">{{ _i("Maximum Focal length") }}</label>
            <div class="input-group mb-3">
                <input type="number" wire:model="maxFocalLength" placeholder="31 - {{ _i("Only for zoom eyepieces") }}"
                    max="99" min="1" class="form-control @error('maxFocalLength') is-invalid @enderror" maxlength="5"
                    name="maxFocalLength" size="30"
                    value="@if ($eyepiece->maxFocalLength){{ $eyepiece->maxFocalLength }}@else{{ old('maxFocalLength') }}@endif" />
                <div class="input-group-append">
                    <span class="input-group-text" id="name-addon">mm</span>
                </div>
            </div>
            @error('maxFocalLength') <span class="small text-error">{{ $message }}<br /></span> @enderror
        </div>

        {{-- The brand of the eyepiece --}}
        <div class="form-group brandInput">
            <label for="brandInput">{{ _i("Select brand") }}</label>
            @php
            $allBrands = [0 => ''] + \App\Models\EyepieceBrand::all()->pluck('brand', 'brand')->toArray();
            @endphp

            <div x-data=''>
                <x-input.select-live-wire-collection :first="$brand" wire:model="brand" prettyname="mybrand"
                    :options="$allBrands" selected="('brand')" />
            </div>
        </div>
        <div class="form-group">
            <label for="brandInput">{{ _i("or add a new brand") }}</label>
            <input type="text" wire:model="newBrand" placeholder="{{ _i("Add a new brand for the eyepiece") }}"
                class="form-control @error('newBrand') is-invalid @enderror" name="newBrand" size="30" />
            @error('newBrand') <span class="small text-error">{{ $message }}<br /></span> @enderror
            <p hidden>{{ $brand }}</p>
        </div>

        {{-- The type of the eyepiece --}}
        <div class="form-group">
            <label for="typeInput">{{ _i("Type") }}</label>
            <select wire:model="type" class="form-control">
                <option value=""></option>
                @foreach ($allTypes as $typeOption)
                <option value="{{ $typeOption }}">{{ $typeOption }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="typeInput">{{ _i("or add a new type") }}</label>
            <input type="text" wire:model="newType" placeholder="{{ _i("Add a new type for the eyepiece") }}"
                class="form-control @error('newType') is-invalid @enderror" name="newType" size="30" />
            @error('newType') <span class="small text-error">{{ $message }}<br /></span> @enderror
            <p hidden>{{ $type }}</p>
        </div>

        {{-- Eyepiece picture --}}
        <div class="form-group">

            <div class="card mb-3">
                <div class="row no-gutters">
                    <div class="col-2" id="card-bg">
                        @if ($photo)
                        <img class="card-img-top" style="border-radius: 20%" src="{{ $photo->temporaryUrl() }}">
                        @endif
                    </div>
                    <div class="col-10" id="card-bg">
                        <div class="card-body">
                            <h5 class="card-title">{{ _i('Upload a picture of your eyepiece.') . ' (max 10 Mb)' }}</h5>

                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('photo') is-invalid @enderror"
                                    wire:model="photo">
                                <label class="custom-file-label">{{ _i('Choose file') }}</label>
                            </div>
                            <div wire:loading wire:target="photo" class="text-sm text-gray-500 italic">
                                {{ _i('Uploading...') }}
                            </div>
                            @error('photo') <br /><span class="small text-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="submit" class="btn btn-success" name="add"
            value="@if ($update){{ _i("Change eyepiece") }}@else{{ _i("Add eyepiece") }}@endif" />

    </form>
</div>
