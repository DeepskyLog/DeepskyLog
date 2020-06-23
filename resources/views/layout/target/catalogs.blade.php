@extends("layout.master")

@section('title')
    {{ _i('Object catalogs') }}
@endsection

@section('content')
<h4>
    {{ _i('Object catalogs') }}
</h4>

<select class="form-control selection" onchange="view_catalog(this.value);">
    <option value=""></option>
    @foreach (\App\TargetName::getCatalogs() as $catalog)
        <option value="{{ $catalog->catalog }}">{{ $catalog->catalog }}</option>
    @endforeach
</select>

<br /><br />
<div id="count" class="count">
    {{ _i('Select a list to view its details') }}
</div>
<div id="constellations" class="constellations"></div>
<div id="types" class="types"></div>
<div id="targets" class="targets"></div>
@endsection

@push('scripts')
<script>
    function view_catalog(thecatalog) {
        document.getElementById('count').innerHTML='{{ _i('Getting data for ') }}' + thecatalog;
        var jsonhttp;
        if (window.XMLHttpRequest) {
            jsonhttp = new XMLHttpRequest();
        } else if (window.activeXObject) {
            jsonhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } else {
            alert('{{ _i("Catalog pages are not supported on non-xmlhttp machines") }}');
        }
        jsonhttp.onreadystatechange = function() {
            if (jsonhttp.readyState == 4) {
                show_catalog(
                    eval(
                        jsonhttp.responseText
                    ),
                    thecatalog
                );
            }
        };
        var url='/getCatalogData/'+thecatalog;
        jsonhttp.open("GET",url,true);
        jsonhttp.send(null);
    }

    function show_catalog($catalogdata, thecatalog) {
        var thecount = Object.keys($catalogdata).length;
        var text = '{{ _i("Number of objects") }}' + ": ";
        text += thecount + "<br />";
        document.getElementById('count').innerHTML = text;

        // Show the table with the constellations
        $.getJSON('/getConstellationInfo/' + thecatalog, function(data) {
            context = '<div class="table-responsive"><table class="table table-sm table-bordered table-hover">';

            for ($j = 0;$j < data.length;$j++) {
                if (($j % 3) == 0) {
                    context += '<tr>';
                }
                context += '<td class="td33pct">'
                    + data[$j].con + ': ' + data[$j].count + "</td>";
	            if(($j % 3) == 2) {
                    context += '</tr>';
                }
            }
            context += '</table></div>';

            document.getElementById('constellations').innerHTML = context;
        });

        // Show the table with the types
        $.getJSON('/getTypeInfo/' + thecatalog, function(data) {
            typetext = '<div class="table-responsive"><table class="table table-sm table-bordered table-hover">';

            for ($j = 0;$j < data.length;$j++) {
                if (($j % 3) == 0) {
                    typetext += '<tr>';
                }
                typetext += '<td class="td33pct">'
                    + data[$j].type + ': ' + data[$j].count + "</td>";
	            if(($j % 3) == 2) {
                    typetext += '</tr>';
                }
            }
            typetext += '</table></div>';

            document.getElementById('types').innerHTML = typetext;
        });

        // Show the table with the objects
        text = '<div class="table-responsive"><table class="table table-sm table-bordered table-hover">';
        for ($j = 0;$j < $catalogdata.length;$j++) {
            if(($j % 3) == 0) {
                text += '<tr>';
            }
	        text += '<td class="td33pct"><a href="/target/' + ($catalogdata[$j]['altname']) + '">' + $catalogdata[$j]['altname'] + ($catalogdata[$j]['altname'] != $catalogdata[$j]['target_name'] ? "&nbsp;(" + $catalogdata[$j]['target_name'] + ")" : "") + "</a></td>";
	        if (($j % 3) == 2) {
                text += '</tr>';
            }
        }

        if (($j % 3) != 0) {
            while ((($j++) % 3 != 2)) {
                text += '<td class="td33pct">&nbsp;</td>';
            }
	        text += '</tr>';
        }
        text += '</table></div>';

        document.getElementById('targets').innerHTML = text;
    }
</script>
@endpush
