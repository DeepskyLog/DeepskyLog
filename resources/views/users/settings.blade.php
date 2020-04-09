@extends("layout.master")

@section('title', _i('Settings'))

@section('content')

<h3>Settings for {{ $user->name }}</h3>
    <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
        <li class="active nav-item">
            <a class="nav-link active" href="#info" data-toggle="tab">
                {{ _i("Personal") }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#observingDetails" data-toggle="tab">
                {{ _i("Observing")  }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#atlases" data-toggle="tab">
                {{ _i("Atlases")  }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#languages" data-toggle="tab">
                {{ _i("Languages") }}
            </a>
        </li>
    </ul>

    <div id="my-tab-content" class="tab-content">
        <!-- Personal tab -->
        <div class="tab-pane active" id="info">

            <form role="form" action="/users/{{ $user->id }}/settings" method="POST" enctype="multipart/form-data">
                <br />
                <label class="col-form-label"> {{ _i("Change profile picture") . ' (max 10 Mb)' }}</label>
                <input id="picture" name="picture" type="file">

                @csrf
                @method('PATCH')

                <div class="form-group username">
                    <label for="name">{{ _i("Username") }}</label>
                    <input readonly type="text" required class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}" maxlength="64" name="username" size="30" value="{{ $user->username }}"/>
                </div>

                <div class="form-group email">
                    <label for="name">{{ _i("Email") }}</label>
                    <input type="text" required class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" maxlength="64" name="email" size="30" value="{{ $user->email }}"/>
                </div>

                <div class="form-group name">
                    <label for="name">{{ _i("Name") }}</label>
                    <input type="text" required class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" maxlength="64" name="name" size="30" value="{{ $user->name }}" />
                </div>

                <div class="form-group" name="country" id="country">
                    <label for="country">{{ _i('Country of residence') }}</label>
                    <div class="form">
                        <select class="selection" style="width: 100%" id="country" name="country">
                            <option value="">&nbsp;</option>
                            @foreach (Countries::getList(LaravelGettext::getLocaleLanguage()) as $code=>$country)
                                <option @if ($code == $user->country) selected="selected"@endif value="{{ $code }}">{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group form-check sendMail">
                    <input type="checkbox" class="form-check-input {{ $errors->has('sendMail') ? 'is-invalid' : '' }}" name="sendMail" @if ($user->sendMail)
                        checked
                    @endif />
                    <label class="form-check-label" for="name">{{ _i("Send emails") }}</label>
                </div>

                <div class="form-group fstOffset">
                    <label for="fstOffset">{{ _i("fstOffset") }}</label>
                    <input type="number" min="-5.0" max="5.0" step="0.01" class="form-control {{ $errors->has('fstOffset') ? 'is-invalid' : '' }}" maxlength="4" name="fstOffset" size="4" value="{{ $user->fstOffset }}" />
                    <span class="help-block">{{ _i("Offset between measured SQM value and the faintest visible star.") }}</span>
                </div>

                @php
                    if ("Attribution CC BY" == $user->copyright) {
                        $copval = 0;
                    } else if ("Attribution-ShareAlike CC BY-SA" == $user->copyright) {
                        $copval = 1;
                    } else if ("Attribution-NoDerivs CC BY-ND" == $user->copyright) {
                        $copval = 2;
                    } else if ("Attribution-NonCommercial CC BY-NC" == $user->copyright) {
                        $copval = 3;
                    } else if ("Attribution-NonCommercial-ShareAlike CC BY-NC-SA" == $user->copyright) {
                        $copval = 4;
                    } else if ("Attribution-NonCommercial-NoDerivs CC BY-NC-ND" == $user->copyright) {
                        $copval = 5;
                    } else if ("" == $user->copyright) {
                        $copval = 6;
                    } else {
                        $copval = 7;
                    }
                @endphp
                <div class="form-group license" name="license" id="license">
                    <label for="cclicense">{{ _i("License for drawings") }}</label>
                    <div class="form">
                        <select name="cclicense" class="selection" style="width: 100%" id="cclicense" onchange="enableDisableCopyright();" class="form-control">
                            <option value="0" @if ($copval == 0) selected="cclicense"@endif>Attribution CC BY</option>
                            <option value="1" @if ($copval == 1) selected="cclicense"@endif>Attribution-ShareAlike CC BY-SA</option>
                            <option value="2" @if ($copval == 2) selected="cclicense"@endif>Attribution-NoDerivs CC BY-ND</option>
                            <option value="3" @if ($copval == 3) selected="cclicense"@endif>Attribution-NonCommercial CC BY-NC</option>
                            <option value="4" @if ($copval == 4) selected="cclicense"@endif>Attribution-NonCommercial-ShareAlike CC BY-NC-SA</option>
                            <option value="5" @if ($copval == 5) selected="cclicense"@endif>Attribution-NonCommercial-NoDerivs CC BY-NC-ND</option>
                            <option value="6" @if ($copval == 6) selected="cclicense"@endif>{{ _i("No license (Not recommended!)") }}</option>
                            <option value="7" @if ($copval == 7) selected="cclicense"@endif>{{ _i("Enter your own copyright text") }}</option>
                        </select>
                    </div>
                    <span class="help-block">
                        @php
                            // Use the correct language for the chooser tool
                            echo _i('It is important to select the correct license for your drawings!
                                            For help, see the %sCreative Commons license chooser%s.',
                                '<a href="http://creativecommons.org/choose/?lang=' . LaravelGettext::getLocale() . '">', '</a>');
                        @endphp
                    </span>
                </div>

                <div class="form-group">
                    <label for="copyright">{{ _i('Copyright notice') }}</label>
                    <input id="copyright" type="text" class="form-control" maxlength="128" name="copyright" value="{{ $user->copyright }}" >
                </div>

                <input type="submit" class="btn btn-success" name="add" value="{{ _i("Update") }}" />
            </form>
        </div>

        <!-- Observing tab -->
        <div class="tab-pane" id="observingDetails">
            <br />
            <form role="form" action="/users/{{ $user->id }}/settings" method="POST">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label for="stdlocation">{{ _i("Default observing site") }}</label>
                    <div class="form">
                        <select class="form-control selection" style="width: 100%" id="stdlocation" name="stdlocation">
                            {!! App\Location::getLocationOptions() !!}
                        </select>
                    </div>
                    <span class="help-block">
                       <a href="/location/create">{{ _i("Add new observing site") }}</a>
                    </span>
                </div>

                <div class="form-group">
                    <label for="stdinstrument">{{ _i("Default instrument") }}</label>
                    <div class="form">
                        <select class="form-control selection" style="width: 100%" id="stdinstrument" name="stdinstrument">
                            {!! App\Instrument::getInstrumentOptions() !!}
                        </select>
                    </div>
                    <span class="help-block">
                        <a href="/instrument/create"> {{ _i("Add instrument") }}</a>
                    </span>
                </div>


                <div class="form-group">
                    <label for="stdatlas">{{ _i("Default atlas") }}</label>
                    <div class="form">
                        <select class="form-control selection" style="width: 100%" id="standardAtlasCode" name="standardAtlasCode">
                            @foreach(\App\Atlas::All() as $atlas)
                                <option @if ($atlas->code == $user->standardAtlasCode) selected @endif value="{{ $atlas->code }}">{{ $atlas->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="showInches">{{ _i("Default units") }}</label>
                    <div class="form">
                        <select class="form-control selection" style="width: 100%" id="showInches" name="showInches">
                            <option @if (0 == $user->showInches) selected @endif value="0">{{ _i("Metric (mm)") }}</option>
                            <option @if (1 == $user->showInches) selected @endif value="1">{{ _i("Imperial (inches)") }}</option>
                        </select>
                    </div>
                </div>

                <input type="submit" class="btn btn-success" name="add" value="{{ _i("Update") }}" />
            </form>
        </div>

        <!-- Atlasses tab -->
        <div class="tab-pane" id="atlases">
            <br />
            <form role="form" action="/users/{{ $user->id }}/settings" method="POST">
                @csrf
                @method('PATCH')

                {{ _i("Atlas standard object FoVs:") }}
                <div class="form-group">
                    <div class="row">
                        <div class="col">
                            <label>{{  _i("Overview") }}</label>
                            <input type="number" min="1" max="3600" class="inputfield centered form-control" name="overviewFoV" value="{{ $user->overviewFoV }}"/>
                        </div>
                        <div class="col">
                            <label>{{  _i("Lookup") }}</label>
                            <input type="number" min="1" max="3600" class="inputfield centered form-control" name="lookupFoV" value="{{ $user->lookupFoV }}" />
                        </div>
                        <div class="col">
                            <label>{{  _i("Detail") }}</label>
                            <input type="number" min="1" max="3600" class="inputfield centered form-control" name="detailFoV" value="{{ $user->detailFoV }}" />
                        </div>
                    </div>
                </div>

                {{ _i("Atlas standard object magnitudes:") }}
                <div class="form-group">
                    <div class="row">
                        <div class="col">
                            <label>{{  _i("Overview") }}</label>
                            <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control" name="overviewdsos" value="{{ $user->overviewdsos }}"/>
                        </div>
                        <div class="col">
                            <label>{{  _i("Lookup") }}</label>
                            <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control" name="lookupdsos" value="{{ $user->lookupdsos }}" />
                        </div>
                        <div class="col">
                            <label>{{  _i("Detail") }}</label>
                            <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control" name="detaildsos" value="{{ $user->detaildsos }}" />
                        </div>
                    </div>
                </div>

                {{ _i("Atlas standard star magnitudes:") }}
                <div class="form-group">
                    <div class="row">
                        <div class="col">
                            <label>{{  _i("Overview") }}</label>
                            <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control" name="overviewstars" value="{{ $user->overviewstars }}"/>
                        </div>
                        <div class="col">
                            <label>{{  _i("Lookup") }}</label>
                            <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control" name="lookupstars" value="{{ $user->lookupstars }}" />
                        </div>
                        <div class="col">
                            <label>{{  _i("Detail") }}</label>
                            <input type="number" min="1.0" max="20.0" step="0.1" class="inputfield centered form-control" name="detailstars" value="{{ $user->detailstars }}" />
                        </div>
                    </div>
                </div>

                {{ _i("Standard size of photos:")  }}
                <div class="form-group">
                    <div class="row">
                        <div class="col">
                            <label>{{  _i("Photo 1") }}</label>
                            <input type="number" min="1" max="3600" class="inputfield centered form-control" name="photosize1" value="{{ $user->photosize1 }}"/>
                        </div>
                        <div class="col">
                            <label>{{  _i("Photo 2") }}</label>
                            <input type="number" min="1" max="3600" class="inputfield centered form-control" name="photosize2" value="{{ $user->photosize2 }}" />
                        </div>
                    </div>
                </div>

                {{ _i("Font size printed atlas pages (6..9)") }}
                <div class="form-group">
                    <div class="row">
                        <input type="number" min="6" max="9" class="inputfield centered form-control" maxlength="1" name="atlaspagefont" size="5" value="{{ $user->atlaspagefont }}" />
                    </div>
                </div>

                <input type="submit" class="btn btn-success" name="add" value="{{ _i("Update") }}" />
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
                            @foreach(Config::get('laravel-gettext.supported-locales') as $locale)
                                @php
                                    $localeText = ucwords(Locale::getDisplayLanguage($locale, LaravelGettext::getLocale()));
                                @endphp
                                <option value="{{ $locale }}"@if ($locale == $user->language) selected="selected"@endif>{{ $localeText }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observationlanguage">{{ _i('Standard language for observations') }}</label>

                    <div class="form">
                        <select class="selection" style="width: 100%" id="observationlanguage" name="observationlanguage">
                            @foreach (Languages::lookup('major', LaravelGettext::getLocaleLanguage()) as $code=>$language)
                                <option value="{{ $code }}"@if ($code == $user->observationlanguage) selected="selected"@endif>{{ ucfirst($language) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <input type="submit" class="btn btn-success" name="add" value="{{ _i("Update") }}" />
            </form>
        </div>
    </div>

@endsection


@push('scripts')
<script>
$(document).ready(function()  {
    // Also put the correct copyright in the copyright field
    e = document.getElementById("cclicense");

    if (e.selectedIndex == 6) {
        document.getElementById("copyright").readOnly=true;
        document.getElementById("copyright").value = '';
    } else if (e.selectedIndex != 7) {
        document.getElementById("copyright").readOnly=true;
        document.getElementById("copyright").value = e.options[e.selectedIndex].text;
    } else {
        document.getElementById("copyright").readOnly=false;
    }
} );

$('#password').password({
    shortPass: '<?php echo _i("The password is too short"); ?>',
    badPass: '<?php echo _i("Weak; try combining letters & numbers"); ?>',
    goodPass: '<?php echo _i("Medium; try using special characters"); ?>',
    strongPass: '<?php echo _i("Strong password"); ?>',
    containsUsername: '<?php echo _i("The password contains the username"); ?>',
    enterPass: '<?php echo _i("Type your password"); ?>',
    showText: true, // shows the text tips
    animate: true, // whether or not to animate the progress bar on input blur/focus
    animateSpeed: 'fast', // the above animation speed
    username: false, // select the username field (selector or jQuery instance) for better password checks
    usernamePartialMatch: true, // whether to check for username partials
    minimumLength: 6 // minimum password length (below this threshold, the score is 0)
  });

function enableDisableCopyright() {
    var selectBox = document.getElementById("cclicense");
    var selectedValue = selectBox.options[selectBox.selectedIndex].value;
    if (selectedValue == 7) {
        document.getElementById("copyright").readOnly=false;
        document.getElementById("copyright").value = '';
    } else if (selectedValue == 6) {
        document.getElementById("copyright").readOnly=true;
        document.getElementById("copyright").value = '';
    } else {
        document.getElementById("copyright").readOnly=true;
        // Use the old values to enable or disable the field at pageload
        e = document.getElementById("cclicense");
        document.getElementById("copyright").value = e.options[e.selectedIndex].text;
    }
}

$("#picture").fileinput(
    {
        theme: "fas",
        allowedFileTypes: ['image'],    // allow only images
        'showUpload': false,
        maxFileSize: 10000,
        @if ($user->id != null && $user->getFirstMedia('observer') != null)
        initialPreview: [
            '<img class="file-preview-image kv-preview-data" src="/users/{{ $user->id }}/getImage">'
        ],
        initialPreviewConfig: [
            {caption: "{{ $user->getFirstMedia('observer')->file_name }}", size: {{ $user->getFirstMedia('observer')->size }}, url: "/users/{{ $user->id }}/deleteImage", key: 1},
        ],
        @endif
    }
);
</script>
@endpush
