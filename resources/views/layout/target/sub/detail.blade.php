<table class="table table-sm table-striped table-hover">
    <tr>
        <td colspan="3">{{ _i("Name") }}</td>
        <td colspan="3">{{ _i($target->name) }}</td>
        <td colspan="3"><span class="float-right">{{ _i('Type') }}</span></td>
        <td colspan="3">{{ _i($target->type()->first()->observationType()->first()['name']) }} / {{ _i($target->type()->first()['type']) }}</td>
    </tr>

    @if ($target->type()->first()->observationType()->first()['type'] == 'ds' ||
        $target->type()->first()->observationType()->first()['type'] == 'double')
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
                {{ App\Atlases::where('code', Auth::user()->standardAtlasCode)->first()['name'] }}
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
        @php
            $contrast = new \App\Contrast($target)
        @endphp

        <tr>
            <td colspan="3">{{ _i("Contrast reserve") }}</td>
            <td colspan="3">
                <span class="{{ $contrast->contype }}" data-toggle="tooltip" data-placement="bottom" title="{{ $contrast->popup }}">{{ $contrast->contrast }}</span>
            </td>
            <td colspan="3">
                <span class="float-right">
                    {{ _i("Optimum detection magnification") }}
                </span>
            </td>
            <td colspan="3">{{ $contrast->prefMag }}</td>
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
    @if (Auth::user()->stdlocation != 0 && Auth::user()->stdtelescope != 0)
        @if ($target->type()->first()->observationType()->first()['type'] == 'ds' ||
            $target->type()->first()->observationType()->first()['type'] == 'double')

@php
$datestr = Session::get('date');
$date = DateTime::createFromFormat('d/m/Y', $datestr);

$location = \App\Location::where('id', Auth::user()->stdlocation)->first();
$objAstroCalc = new \App\Libraries\AstroCalc(
    $date, $location->latitude, $location->longitude,
    $location->timezone
);

$ristraset = $objAstroCalc->calculateRiseTransitSettingTime($target->ra, $target->decl, $objAstroCalc->jd);

if ($ristraset[0] == "-" && strncmp($ristraset[3], "-", 1 ) == 0) {
	$popup1 = sprintf(_i('%s does not rise above horizon'), $target->name);
} else if ($ristraset[0] == "-") {
	$popup1 = sprintf(_i('%s is circumpolar'), $target->name);
} else {
	$popup1 = sprintf(_i('%s rises at %s on %s in %s'), $target->name, $ristraset[0], $datestr, $location->name);
}
$popup2 = sprintf(_i('%s transits at %s on %s in %s'), $target->name, $ristraset[1], $datestr, $location->name);
if ($ristraset[2] == "-" && strncmp($ristraset[3], "-", 1 ) == 0) {
	$popup3 = sprintf(_i('%s does not rise above horizon'), $target->name);
} else if ($ristraset[2] == "-") {
				$popup3 = sprintf(_i('%s is circumpolar'), $target->name);
} else {
	$popup3 = sprintf(_i('%s sets at %s on %s in %s'), $target->name, $ristraset[2], $datestr, $location->name);
}
if ($ristraset[3] == "-") {
	$popup4 = sprintf(_i('%s does not rise above horizon'), $target->name);
} else {
	$popup4 = sprintf(_('%s reaches an altitude of %s in %s'), $target->name, $ristraset[3], $location->name);
}

@endphp
            <tr>
                <td>{{ _i('Date') }}</td>
                <td>@php echo session('date') @endphp</td>
                <td>{{ _i("Rise") }}</td>
                <td>
                    <span data-toggle="tooltip" data-placement="bottom" title="{{ $popup1 }}">{{ $ristraset[0] }}</span>
                </td>
                <td>{{ _i("Transit") }}</td>
                <td>
                    <span data-toggle="tooltip" data-placement="bottom" title="{{ $popup2 }}">{{ $ristraset[1] }}</span>
                </td>
                <td>{{ _i("Set") }}</td>
                <td>
                    <span data-toggle="tooltip" data-placement="bottom" title="{{ $popup3 }}">{{ $ristraset[2] }}</span>
                </td>
                <td>{{ _i('Best Time') }}</td>
                <td>{{ $ristraset[4] }}</td>
                <td>{{ _i("Max Alt") }}</td>
                <td>
                    <span data-toggle="tooltip" data-placement="bottom" title="{!! $popup4 !!}">{!! $ristraset[3] !!}</span>
                </td>
            </tr>

        @endif
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
