@extends("layout.master")

@section('title')
{{ _i("Image Catalogs") }}
@endsection

@section('content')
<h4>{{ _i("Image Catalogs") }}</h4>

<div id="catalogCarousel" class="carousel slide" data-ride="carousel" data-interval="10000">
    <!-- Indicators -->
    <ol class="carousel-indicators">
        <li data-target="#catalogCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#catalogCarousel" data-slide-to="1"></li>
    </ol>
    <!-- Wrapper for slides -->
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="/images/AbellExample.png" alt="...">
            <div class="carousel-caption" style="background: gray;">
                <p>
                    {{ _i('DeepskyLog is a very powerful tool, where you can create personalized atlases and image catalogs.') }}
                </p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="/images/AbellExample2.png" alt="...">
            <div class="carousel-caption" style="background: gray;">
                <p>
                    {{ _i('However, making your own image catalog is time consuming. To help you, we created some interesting image catalogs and made them available for download.') }}
                    <br />
                    {{_i('They are very useful for telescopes with a goto system, where the goto system guides you to the neighbourhood of the object. Using the images, it is very easy to find the final object.')}}
                </p>
            </div>
        </div>
    </div>
    <a class="carousel-control-prev" href="#catalogCarousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#catalogCarousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>
<br />
<table class="table">
    <tr>
        <th>
            {{ _i("Catalogs sorted by name") }}
        </th>
    </tr>
    <tr>
        <td>
            <a href="{{ env('CATALOG_DOWNLOAD_URL', 'https://www.deepskylog.org/DSL/astroimagecatalogs/') }}/Abell.pdf">
                {{ _i("The Abell Planetary Nebula Catalog") }}
            </a>
        </td>
    </tr>
</table>

{{-- Add catalogs for constellations --}}
<br />
<table class="table">
    <tr>
        <th colspan="3">
            {{ _i("Catalogs sorted by constellation") }}
        </th>
    </tr>
    {{-- We have to add these manually, because we don't have all the files --}}
    @php
    $constellations = Array("AND", "ANT", "AQL", "AQR", "ARI", "AUR", "BOO", "CAE", "CAM", "CAP",
    "CAS", "CEN", "CEP", "CET", "CMA", "CMI", "CNC", "COL", "COM", "CRA",
    "CRB", "CRT", "CRV", "CVN", "CYG", "DEL", "DRA", "EQU", "ERI", "FOR",
    "GEM", "GRU", "HER", "HOR", "HYA", "LAC", "LEO", "LEP", "LIB", "LMI",
    "LUP", "LYN", "LYR", "MIC", "MON", "OPH", "ORI", "PEG", "PER", "PHE",
    "PSA", "PSC", "PUP", "PYX", "SCL", "SCO", "SCT", "SER", "SGE", "SGR",
    "TAU", "TRI", "UMA", "UMI", "VIR", "VUL");

    foreach ($constellations as $key=>$value)
    {
    $cons[$value] = \App\Models\Constellation::where('id', $value)->pluck('name')->toArray()[0];
    }
    asort($cons);
    reset($cons);
    $count = 0;
    @endphp

    @foreach($cons as $key=>$value)
    @if ($count % 3 == 0)
    </tr>
    <tr>
        @endif
        <td>
            <a
                href="{{ env('CATALOG_DOWNLOAD_URL', 'https://www.deepskylog.org/DSL/astroimagecatalogs/') }}/constellations/{{ $key }}.pdf">{{ $value }}
            </a>
        </td>
        @php
        $count++;
        @endphp
        @endforeach
    </tr>
</table>
@endsection
