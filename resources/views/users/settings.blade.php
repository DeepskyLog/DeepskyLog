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
        <input type="file" class="filepond">
        Test Personal
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
            url: '/upload',
            process: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }
        }
    });
    const inputElement = document.querySelector('input[type="file"]');
    const pond = FilePond.create( inputElement );
</script>
@endpush
