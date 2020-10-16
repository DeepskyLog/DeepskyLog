@extends("layout.master")

@section('title', _i('Settings'))

@section('content')

<h3>Settings for {{ $user->name }}</h3>
<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <li class="active nav-item">
        <a class="nav-link active" href="#info" data-toggle="tab">
            {{ _i('Personal') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#observingDetails" data-toggle="tab">
            {{ _i('Observing') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#atlases" data-toggle="tab">
            {{ _i('Atlases') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#languages" data-toggle="tab">
            {{ _i('Languages') }}
        </a>
    </li>
</ul>

<div id="my-tab-content" class="tab-content">
    <!-- Personal tab -->
    <div class="tab-pane active" id="info">
        <livewire:user.user-settings :user="$user" />
    </div>

    <!-- Observing tab -->
    <div class="tab-pane" id="observingDetails">
        <livewire:user.user-observing-settings :user="$user" />
    </div>

    <!-- Atlasses tab -->
    <div class="tab-pane" id="atlases">
        <livewire:user.user-atlas-settings :user="$user" />
    </div>

    <div class="tab-pane" id="languages">
        <br />
        <form role="form" action="/users/{{ $user->id }}/settings" method="POST">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="language">{{ _i('Language for user interface') }}</label>

                <div class="form">
                    <select class="selection" style="width: 100%" id="language" name="language">
                        @foreach (Config::get('laravel-gettext.supported-locales') as $locale)
                        @php
                        $localeText = ucwords(Locale::getDisplayLanguage($locale, LaravelGettext::getLocale()));
                        @endphp
                        <option value="{{ $locale }}" @if ($locale==$user->language)
                            selected="selected"@endif>{{ $localeText }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="observationlanguage">{{ _i('Standard language for observations') }}</label>

                <div class="form">
                    <select class="selection" style="width: 100%" id="observationlanguage" name="observationlanguage">
                        @foreach (Languages::lookup('major', LaravelGettext::getLocaleLanguage()) as $code => $language)
                        <option value="{{ $code }}" @if ($code==$user->observationlanguage) selected="selected"
                            @endif>{{ ucfirst($language) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <input type="submit" class="btn btn-success" name="add" value="{{ _i('Update') }}" />
        </form>
    </div>
</div>

@endsection
