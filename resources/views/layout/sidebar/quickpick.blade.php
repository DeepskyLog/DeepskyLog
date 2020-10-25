<li>
    <form role="form" action="" method="get">
        <h4>
            {{ _i('Quick Search') }}
        </h4>
        <input type="hidden" name="indexAction" value="quickpick" />
        <input type="hidden" name="titleobjectaction" value="search" />
        <input type="hidden" name="source" value="quickpick" />
        <input type="hidden" name="myLanguages" value="true" />

        @php
        $objects = '<option value="M 1">M 1</option>
        <option value="M 2">M 2</option>
        <option value="M 3">M 3</option>
        <option value="M 4">M 4</option>
        <option value="M 5">M 5</option>
        <option value="M 6">M 6</option>
        <option value="Halley">Halley</option>
        <option value="Copernicus">Copernicus</option>
        <option value="Mercury">Mercury</option>
        <option value="Venus">Venus</option>
        <option value="Mars">Mars</option>
        <option value="Jupiter">Jupiter</option>
        <option value="Saturn">Saturn</option>
        <option value="Uranus">Uranus</option>
        <option value="Neptune">Neptune</option>';
        @endphp
        <div x-data=''>
            <x-input.select id="quickpickobject" :options="$objects" />
        </div>

        <br /><br />
        <div class="form group">
            <input class="btn btn-outline-secondary btn-block btn-sm" type="submit"
                name="searchObjectQuickPickQuickPick" value=" {{ _i("Search Object") }}" />
        </div>
        {{--        <div class="form group">
            <input class="btn btn-outline-secondary btn-block btn-sm" type="submit" name="searchObservationsQuickPick" value=" {{ _i("Search Observations") }}"
        />
        </div>

        <div class="form group">
            <input class="btn btn-outline-secondary btn-block btn-sm" type="submit" name="newObservationQuickPick"
                value=" {{ _i("New Observation") }}" />
        </div>
        --}}
    </form>
</li>
