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

            <br />
            <label class="col-form-label"> {{ _i("Change profile picture") }}</label>
            <input type="file" id="filepond" class="filepond">

            <form role="form" action="/user/settings/{{ $user->id }}" method="POST">
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

                <div class="form-group form-check sendMail">
                    <input type="checkbox" class="form-check-input {{ $errors->has('sendMail') ? 'is-invalid' : '' }}" name="sendMail" @if ($user->sendMail)
                        checked
                    @endif />
                    <label class="form-check-label" for="name">{{ _i("Send emails") }}</label>
                </div>

                <div class="form-group fstOffset">
                    <label for="fstOffset">{{ _i("fstOffset") }}</label>
                    <input type="number" min="-5.0" max="5.0" step="0.1" class="form-control {{ $errors->has('fstOffset') ? 'is-invalid' : '' }}" maxlength="4" name="fstOffset" size="4" value="{{ $user->fstOffset }}" />
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
                    <select name="cclicense selection" id="cclicense" onchange="enableDisableCopyright();" class="form-control">
                        <option value="0" @if ($copval == 0) selected="cclicense"@endif>Attribution CC BY</option>
                        <option value="1" @if ($copval == 1) selected="cclicense"@endif>Attribution-ShareAlike CC BY-SA</option>
                        <option value="2" @if ($copval == 2) selected="cclicense"@endif>Attribution-NoDerivs CC BY-ND</option>
                        <option value="3" @if ($copval == 3) selected="cclicense"@endif>Attribution-NonCommercial CC BY-NC</option>
                        <option value="4" @if ($copval == 4) selected="cclicense"@endif>Attribution-NonCommercial-ShareAlike CC BY-NC-SA</option>
                        <option value="5" @if ($copval == 5) selected="cclicense"@endif>Attribution-NonCommercial-NoDerivs CC BY-NC-ND</option>
                        <option value="6" @if ($copval == 6) selected="cclicense"@endif>{{ _i("No license (Not recommended!)") }}</option>
                        <option value="7" @if ($copval == 7) selected="cclicense"@endif>{{ _i("Enter your own copyright text") }}</option>
                    </select>
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
            Test observing
        </div>

        <!-- Atlasses tab -->
        <div class="tab-pane" id="atlases">
            <br />
            Test atlases
        </div>

        <div class="tab-pane" id="languages">
            <br />
            Test languages
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

    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginImageExifOrientation,
        FilePondPluginImagePreview,
        FilePondPluginImageCrop,
        FilePondPluginImageResize,
        FilePondPluginImageTransform
    );

    FilePond.setOptions({
        acceptedFileTypes: ['image/*'],
        server: {
            url: '/user/upload',
            process: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            },
            revert: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }

        }
    });
    const inputElement = document.querySelector('input[type="file"]');
    const pond = FilePond.create( inputElement, { files: [
        {
            // the server file reference
            source: '/user/getImage',
        }
    ] } );
</script>
@endpush
