<li>
    <h4>
        {{ _i('Quick Search') }}
    </h4>
    <form method="POST" action="/target">
        @csrf
        <div class="form-group">
            <div>
                <input type="text" placeholder="{{ _i('Enter object name') }}" class="form-control" name="quickpick">
            </div>
        </div>
        <div class="form group">
            <input class="btn btn-outline-secondary btn-block btn-sm" type="submit"
                name="searchObjectQuickPickQuickPick" value=" {{ _i("Search Object") }}" />
        </div>
    </form>

    {{--        <div class="form group">
            <input class="btn btn-outline-secondary btn-block btn-sm" type="submit" name="searchObservationsQuickPick" value=" {{ _i("Search Observations") }}"
    />
    </div>

    <div class="form group">
        <input class="btn btn-outline-secondary btn-block btn-sm" type="submit" name="newObservationQuickPick"
            value=" {{ _i("New Observation") }}" />
    </div>
    --}}
</li>
