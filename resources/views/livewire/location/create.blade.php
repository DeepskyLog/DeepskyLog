<div>
    <form role="form" wire:submit.prevent="save">

        <input type="hidden" name="latitude" id="latitude" />
        <input type="hidden" name="longitude" id="longitude" />
        <input type="hidden" name="country" id="country" />
        <input type="hidden" name="elevation" id="elevation" />
        <input type="hidden" name="timezone" id="timezone" />

        <input type="submit" class="btn btn-success float-right" name="add"
            value="@if ($update){{ _i("Change location") }}@else{{ _i("Add location") }}@endif">
        <div>
            <li>
                {!! _i("Define your own name for this location, eventually add a naked eye limiting magnitude (or
                SQM value) and press the %s button.",
                '<strong>"' . _i("Add location") . '"</strong>') !!}

                <br />
                <br />
            </li>

            {{-- Location name --}}
            <div>
                <div class="form-inline">
                    <input wire:model="name" type="text" required placeholder="{{ _i('Location name') }}"
                        class="form-control @error('name') is-invalid @enderror" maxlength="64" name="name" size="30"
                        value="@if ($location->name){{ $location->name }}@else{{ old('name') }}@endif" />
                </div>
            </div>
            @error('name') <span class="small text-error">{{ $message }}<br /></span> @enderror

            <table class="table">
                <tr>
                    <th>{{ _i('Longitude') }}</th>
                    <th>{{ _i('Latitude') }}</th>
                    <th>{{ _i('Country') }}</th>
                    <th>{{ _i('Elevation') }}</th>
                    <th>{{ _i('Timezone') }}</th>
                </tr>
                <tr>
                    <td>
                        {{ (new \deepskylog\AstronomyLibrary\Coordinates\Coordinate($longitude, -180, 180))->convertToDegrees() }}
                    </td>
                    <td>
                        {{ (new \deepskylog\AstronomyLibrary\Coordinates\Coordinate($latitude, -90, 90))->convertToDegrees() }}
                    </td>
                    <td>
                        {{  Countries::getOne($country, LaravelGettext::getLocaleLanguage()) }}
                    </td>
                    <td>
                        {{ round($elevation) }}
                    </td>
                    <td>
                        {{ $timezone }}
                    </td>
                </tr>
            </table>
            <br />

            {{-- Limiting magnitude / SQM / bortle --}}
            <div class="row">
                <div class="col">
                    {{ _i("Typical naked eye limiting magnitude") }}
                    <input wire:model="limitingMagnitude" type="number" min="0" max="8.0" step="0.01"
                        class="form-control @error('limitingMagnitude') is-invalid @enderror" maxlength="5" id="lm"
                        name="lm" size="5"
                        value="@if ($location->limitingMagnitude){{ $location->limitingMagnitude - Auth::user()->fstOffset }}@else{{ old('lm') }}@endif" />
                    @error('limitingMagnitude') <span class="small text-error">{{ $message }}<br /></span>
                    @enderror
                </div>
                <div class="col">
                    {{ _i("Sky Quality Meter (SQM) value") }}
                    <input wire:model='skyBackground' type="number" min="10.0" max="25.0" step="0.01"
                        class="form-control @error('skyBackground') is-invalid @enderror" maxlength="5" id="sqm"
                        name="sb" size="5"
                        value="@if ($location->skyBackground){{ $location->skyBackground }}@else{{ old('sb') }}@endif" />
                    @error('skyBackground') <span class="small text-error">{{ $message }}<br /></span>
                    @enderror
                </div>
                <div class="col">
                    {{ _i("Bortle Scale") }}
                    @php
                    $bortleOptions = '
                    <option value=""></option>
                    <option' . (($bortle==1) ? ' selected="selected" ' : '' ) . ' value="1">1 - ' . _i("Excellent
                        dark-sky site") . '</option>
                    <option' . (($bortle==2) ? ' selected="selected" ' : '' ) . ' value="2">2 - ' . _i("Typical truly
                        dark site") . '</option>
                    <option' . (($bortle==3) ? ' selected="selected" ' : '' ) . ' value="3">3 - ' . _i("Rural sky") . '</option>
                    <option' . (($bortle==4) ? ' selected="selected" ' : '' ) . ' value="4">4 - ' . _i("Rural/suburban
                        transition") . '</option>
                    <option' . (($bortle==5) ? ' selected="selected" ' : '' ) . ' value="5">5 - ' . _i("Suburban sky")
                        . '</option>
                    <option' . (($bortle==6) ? ' selected="selected" ' : '' ) . ' value="6">6 - ' . _i("Bright suburban
                        sky") . '</option>
                    <option' . (($bortle==7) ? ' selected="selected" ' : '' ) . ' value="7">7 - ' . _i("Suburban/urban
                        transition") . '</option>
                    <option' . (($bortle==8) ? ' selected="selected" ' : '' ) . ' value="8">8 - ' . _i("City sky") . '</option>
                    <option' . (($bortle==9) ? ' selected="selected" ' : '' ) . ' value="9">9 - ' . _i("Inner-city
                        sky") . '</option>' ; @endphp <div x-data=''>
                        <x-input.select-live-wire wire:model="bortle" prettyname="mybortle" :options="$bortleOptions"
                            selected="('bortle')" />
                </div>
            </div>
        </div>
        <a class='btn btn-primary' wire:click="lightpollutioninfo" role='button' id='lightpollutioninfo'>
            {{ _i("Use value from lightpollutionmap.info") }}
        </a>

        <br />
        <br />

        {{-- Location picture --}}
        {{ _i('Upload a picture of your location.') . ' (max 10 Mb)' }}

        <x-media-library-attachment rules="max:10240" name="media" />
</div>

<br />

<input type="submit" class="btn btn-success" name="add"
    value="@if ($update){{ _i("Change location") }}@else{{ _i("Add location") }}@endif" />

</form>
</div>
