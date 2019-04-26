<li>
    <form role="form" action="/lang" method="POST">
        {{  csrf_field() }}
        <select class="form-control selection" name="language" onchange="this.form.submit()">
            @foreach(Config::get('laravel-gettext.supported-locales') as $locale)
                @php
                    $localeText = ucwords(Locale::getDisplayLanguage($locale, $locale));
                @endphp
                <option value="{{ $locale }}" @if ($locale == LaravelGettext::getLocale())
                    selected="selected"
                @endif>{{ $localeText }}</option>
            @endforeach
        </select>
    </form>
</li>
<br />
