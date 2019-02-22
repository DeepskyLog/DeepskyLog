<li>
	<p>
        <br />
        <h4>
	        {{ _i("Moon / Sun") }}
        </h4>
        <span style="font-weight:normal;">
            {{ _i("on") }} 28/02&gt;&lt;01/03/2019
        </span>
	</p>
	<table class="table table-sm">
	    <tr>
            <td> {{ _i("Moon") }} </td>
            <td>12:01</td>
            <td>22:24</td>
	    </tr>
	    <tr>
            <td>{{ _i("Sun") }}</td>
            <td>16:43</td>
            <td>08:42</td>
	    </tr>
	    <tr>
            <td>{{ _i("Naut.") }}</td>
            <td>18:05</td>
            <td>07:21</td>
	    </tr>
	    <tr>
            <td>{{ _i("Astro.") }}</td>
            <td>18:45</td>
            <td>06:41</td>
	    </tr>
	</table>

    <p>
        <img src="{{ asset('img/moon/m7.gif') }}" title="45%" height="100px" width="100px" alt="45%" />
    </p>

    @php
        setlocale(LC_TIME, LaravelGettext::getLocale());
        $date = DateTime::createFromFormat('j/n/Y', '3/1/2019');
        $newMoonDate = strftime("%e %b", $date->getTimestamp());
    @endphp
    {{ _i("New moon") }}: {{ $newMoonDate }}
</li>
