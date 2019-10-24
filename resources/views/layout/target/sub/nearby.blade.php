<h4>
    {{ $target->name }}
@php
    $zoom = 30;
    if (isset($_GET['zoom'])) {
        $zoom = $_GET['zoom'];
    }
    $targets = $target->getNearbyObjects($zoom)->get();
@endphp
    {{ count($targets) > 2
        ? _i(' and ') . (count($targets) - 1) . _i(' nearby objects')
        : (count($targets) > 1
        ? _i(' and ') . '1' . _i(' nearby object') :
        _i(" - there are no other objects within the specified distance"))
    }}

    <span class="float-right">
        <form class="form-inline" action="/target/{{ $target->name }}" method="get">
            <div>
                {{ _i("up to about ") }}
                <select class="form-control" name="zoom" onchange="submit();">
                    <option value="180" {{ $zoom == 180 ? 'selected=selected' : '' }}>3x3&deg;</option>
                    <option value="120" {{ $zoom == 120 ? 'selected=selected' : '' }}>2x2&deg;</option>
                    <option value="60" {{ $zoom == 60 ? 'selected=selected' : '' }}>1x1&deg;</option>
                    <option value="30" {{ $zoom == 30 ? 'selected=selected' : '' }}>30x30'</option>
                    <option value="15" {{ $zoom == 15 ? 'selected=selected' : '' }}>15x15'</option>
                    <option value="10" {{ $zoom == 10 ? 'selected=selected' : '' }}>10x10'</option>
                    <option value="5" {{ $zoom == 5 ? 'selected=selected' : '' }}>5x5'</option>
                </select>
            </div>
        </form>
    </span>
</h4>

<br /><hr />
{!! $dataTable->table(['class' => 'table table-sm table-striped table-hover']) !!}
<hr />
