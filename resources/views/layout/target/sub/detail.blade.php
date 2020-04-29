<div class="container">
    <div class="row">
        <div class="col-sm">
            <table class="table-sm table-striped table-bordered">
                <tr>
                    <td>{{ _i("Total number of observations") }}</td>
                    <td>TODO</td>
                </tr>
                <tr>
                    <td>{{ _i("Total number of drawings") }}</td>
                    <td>TODO</td>
                </tr>
                <tr>
                    <td>{{ _i("Number of own observations") }}</td>
                    <td>TODO</td>
                </tr>
                <tr>
                    <td>{{ _i("Number of own drawings") }}</td>
                    <td>TODO</td>
                </tr>
            </table>
        </div>
        <div class="col-sm">
            <br />
            <a class="btn btn-success" href="/observation/{{ $target->name }}" role="button">{{ _i("Add new observation of %s", $target->name) }}</a>
        </div>
    </div>
</div>

<br />

<br /><br />
<table class="table table-sm table-striped table-hover">
    <tr>
        <td colspan="3">{{ _i("Name") }}</td>
        <td colspan="3">{{ _i($target->name) }}</td>
        <td colspan="3"><span class="float-right">{{ _i('Type') }}</span></td>
        <td colspan="3">{{ $target->observationType }}</td>
    </tr>

    @if ($target->isNonSolarSystem())
        <tr>
            <td colspan="3">{{ _i('Right Ascension') }}</td>
            <td colspan="3">{{ $target->ra() }}</td>
            <td colspan="3"><span class="float-right">{{ _i('Declination') }}</span></td>
            <td colspan="3">{{ $target->declination() }}</td>
        </tr>

        <tr>
            <td colspan="3">{{ _i('Constellation') }}</td>
            <td colspan="3">{{ _i($target->constellation()->first()['name']) }}</td>
            @auth
            <td colspan="3"><span class="float-right">
                @if ($target->ra != null)
                    {{ App\Atlas::where('code', Auth::user()->standardAtlasCode)->first()['name'] }}
                    {{ _i(" page") }}</span></td>
                @endif
            <td colspan="3">{{ $target->atlaspage(Auth::user()->standardAtlasCode) }}</td>
            @endauth
        </tr>

        <tr>
            <td colspan="3">{{ _i('Magnitude') }}</td>
            <td colspan="3">@if ($target->mag == null)
                    -
                @else
                {{ number_format($target->mag, 1) }}
            @endif
            </td>
            <td colspan="3"><span class="float-right">{{ _i('Surface brightness') }}</span></td>
            <td colspan="3">@if ($target->subr == null)
                -
            @else
            {{ number_format($target->subr, 1) }}
            @endif
            </td>
        </tr>

        <tr>
            <td colspan="3">{{ _i("Size") }}</td>
            <td colspan="3">{{ $target->size() }}</td>
            <td colspan="3"><span class="float-right">{{ _i("Position angle") }}</span></td>
            <td colspan="3">@if ($target->pa > 900)
                    -
                @else
                {{ $target->pa }}&deg;
                @endif
            </td>
        </tr>

        @auth
        @if (Auth::user()->stdlocation != 0 && Auth::user()->stdtelescope != 0)
        <tr>
            <td colspan="3">{{ _i("Contrast reserve") }}</td>
            <td colspan="3">
                <span class="{{ $target->contrast_type }}" data-toggle="tooltip" data-placement="bottom" title="{{ $target->contrast_popup }}">{{ $target->contrast }}</span>
            </td>
            <td colspan="3">
                <span class="float-right">
                    {{ _i("Optimum detection magnification") }}
                </span>
            </td>
            <td colspan="3">{{ $target->prefMag }}</td>
        </tr>
        @endif
        @endauth

        @if (\App\TargetName::hasAlternativeNames($target->name))
        <tr>
            <td colspan="3">{{ _i("Alternative name") }}</td>
            <td colspan="9">{{ \App\TargetName::getAlternativeNames($target->name) }}</td>
        </tr>
        @endif

        @if (\App\TargetPartOf::isPartOf($target->name) || \App\TargetPartOf::contains($target->name))
            <tr>
            <td colspan="3"> {{ _i("(Contains)/Part of") }}</td>
            <td colspan="9">{!! \App\TargetPartOf::partOfContains($target->name) !!}</td>
            </tr>
        @endif

        {{-- TODO: Make it possible to show description added to the list and to change it
        if ($listname && ($objList->checkObjectInMyActiveList ( $object ))) {
            echo "<tr>";
            echo "<td colspan=\"3\">" . _("List description") . ' (' . "<a href=\"" . _("https://github.com/DeepskyLog/DeepskyLog/wiki/Dreyer-Descriptions") . "\" rel=\"external\">" . _("NGC/IC, Dreyer codes") . "</a>)" . "</td>";
            if ($myList) {
                echo "<td colspan=\"9\">" . "<textarea maxlength=\"1024\" name=\"description\" class=\"form-control\" onchange=\"submit()\">" . $objList->getListObjectDescription ( $object ) . "</textarea>" . "</td>";
            } else {
                echo "<td colspan=\"9\">" . $objList->getListObjectDescription ( $object ) . "</td>";
            }
            echo "</tr>";
        } else { --}}
        <tr>
            <td colspan="3">{{ _i("Description") }} (<a href="https://github.com/DeepskyLog/DeepskyLog/wiki/Dreyer-Descriptions" rel="_blank">{{ _i("NGC/IC, Dreyer codes") }}</a>)</td>
            <td colspan="9">{{ $target->description }}</td>
        </tr>
    @endif

    {{-- TODO Check if this object appears in a list, only show the following table line if this is the case. --}}
    <tr>
        <td colspan="3">{{ _i('In my lists') }}</td>
        <td colspan="9">TODO</td>
    </tr>

    @auth
        @if ($target->isNonSolarSystem())
        <tr>
            <td>{{ _i('Date') }}</td>
            <td>@php echo session('date') @endphp</td>
            <td>{{ _i("Rise") }}</td>
            <td>
                <span data-toggle="tooltip" data-placement="bottom" title="{{ $target->rise_popup }}">{{ $target->rise }}</span>
            </td>
            <td>{{ _i("Transit") }}</td>
            <td>
                <span data-toggle="tooltip" data-placement="bottom" title="{{ $target->transit_popup }}">{{ $target->transit }}</span>
            </td>
            <td>{{ _i("Set") }}</td>
            <td>
                <span data-toggle="tooltip" data-placement="bottom" title="{{ $target->set_popup }}">{{ $target->set }}</span>
            </td>
            <td>{{ _i('Best Time') }}</td>
            <td>{{ $target->BestTime }}</td>
            <td>{{ _i("Max Alt") }}</td>
            <td>
                <span data-toggle="tooltip" data-placement="bottom" title="{!! $target->maxAlt_popup !!}">{!! $target->maxAlt !!}</span>
            </td>
        </tr>

        <tr>
            <td>{{ _i('Highest From') }}</td>
            <td colspan="3">{{ $target->highest_from }}</td>
            <td>{{ _i('Highest Around') }}</td>
            <td colspan="3">{{ $target->highest_around }}</td>
            <td>{{ _i('Highest To') }}</td>
            <td colspan="3">{{ $target->highest_to }}</td>
        </tr>
        @endif
    @endauth

    @if ($target->ra != null && $target->decl != null)
    <tr>
        <td colspan="3">Aladin</td>
        <td colspan="100">
            <div id="aladin-lite-div" style="width:600px;height:400px;"></div>
            <link rel="stylesheet" href="https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/aladin.min.css" />
            <script type="text/javascript" src="https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/aladin.min.js" charset="utf-8"></script>
            <script type="text/javascript">
                var aladin = A.aladin('#aladin-lite-div', {survey: "P/DSS2/color", fov:{{ $target->getFov() }}, target: "{{ $target->raDecToAladin() }}"});
            </script>
        </td>
    </tr>
    @endif

</table>

<hr />
