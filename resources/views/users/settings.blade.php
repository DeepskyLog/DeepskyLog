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
        <livewire:user-settings :user="$user" />
    </div>

    <!-- Observing tab -->
    <div class="tab-pane" id="observingDetails">
        <livewire:user-observing-settings :user="$user" />
    </div>

    <!-- Atlasses tab -->
    <div class="tab-pane" id="atlases">
        <br />
        <form role="form" action="/users/{{ $user->id }}/settings" method="POST">
            @csrf
            @method('PATCH')

            {{ _i('Atlas standard object FoVs:') }}
            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>{{ _i('Overview') }}</label>
                        <input type="number" min="1" max="3600" class="inputfield centered form-control"
                            name="overviewFoV" value="{{ $user->overviewFoV }}" />
                    </div>
                    <div class="col">
                        <label>{{ _i('Lookup') }}</label>
                        <input type="number" min="1" max="3600" class="inputfield centered form-control"
                            name="lookupFoV" value="{{ $user->lookupFoV }}" />
                    </div>
                    <div class="col">
                        <label>{{ _i('Detail') }}</label>
                        <input type="number" min="1" max="3600" class="inputfield centered form-control"
                            name="detailFoV" value="{{ $user->detailFoV }}" />
                    </div>
                </div>
            </div>

            {{ _i('Atlas standard object magnitudes:') }}
            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>{{ _i('Overview') }}</label>
                        <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control"
                            name="overviewdsos" value="{{ $user->overviewdsos }}" />
                    </div>
                    <div class="col">
                        <label>{{ _i('Lookup') }}</label>
                        <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control"
                            name="lookupdsos" value="{{ $user->lookupdsos }}" />
                    </div>
                    <div class="col">
                        <label>{{ _i('Detail') }}</label>
                        <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control"
                            name="detaildsos" value="{{ $user->detaildsos }}" />
                    </div>
                </div>
            </div>

            {{ _i('Atlas standard star magnitudes:') }}
            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>{{ _i('Overview') }}</label>
                        <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control"
                            name="overviewstars" value="{{ $user->overviewstars }}" />
                    </div>
                    <div class="col">
                        <label>{{ _i('Lookup') }}</label>
                        <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control"
                            name="lookupstars" value="{{ $user->lookupstars }}" />
                    </div>
                    <div class="col">
                        <label>{{ _i('Detail') }}</label>
                        <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control"
                            name="detailstars" value="{{ $user->detailstars }}" />
                    </div>
                </div>
            </div>

            {{ _i('Standard size of photos:') }}
            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <label>{{ _i('Photo 1') }}</label>
                        <input type="number" min="1" max="3600" class="inputfield centered form-control"
                            name="photosize1" value="{{ $user->photosize1 }}" />
                    </div>
                    <div class="col">
                        <label>{{ _i('Photo 2') }}</label>
                        <input type="number" min="1" max="3600" class="inputfield centered form-control"
                            name="photosize2" value="{{ $user->photosize2 }}" />
                    </div>
                </div>
            </div>

            {{ _i('Font size printed atlas pages (6..9)') }}
            <div class="form-group">
                <div class="row">
                    <input type="number" min="6" max="9" class="inputfield centered form-control" maxlength="1"
                        name="atlaspagefont" size="5" value="{{ $user->atlaspagefont }}" />
                </div>
            </div>

            <input type="submit" class="btn btn-success" name="add" value="{{ _i('Update') }}" />
        </form>
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
