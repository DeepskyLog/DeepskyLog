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
/*$(function(){

    // First register any plugins
    $.fn.filepond.registerPlugin(FilePondPluginFileValidateType);
    $.fn.filepond.registerPlugin(FilePondPluginImageExifOrientation);
    $.fn.filepond.registerPlugin(FilePondPluginImagePreview);
    $.fn.filepond.registerPlugin(FilePondPluginImageCrop);
    $.fn.filepond.registerPlugin(FilePondPluginImageResize);
    $.fn.filepond.registerPlugin(FilePondPluginImageTransform);

    $.fn.filepond.setDefaults({
        acceptedFileTypes: ['image/*']
    });

    // Turn input element into a pond
    $('.filepond').filepond({
        server:{
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

    // Manually add a file using the addfile method
    //$('.filepond').first().filepond('addFile', '/user/getImage');
});
*/

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
