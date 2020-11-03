@extends("layout.master")

@section('title')
@if ($update)
{{ $eyepiece->name }}
@else
{{ _i("Add a new eyepiece") }}
@endif
@endsection

@section('content')

<h4>
    @if ($update)
    {{ $eyepiece->name }}
    @else
    {{ _i("Add a new eyepiece") }}
    @endif
</h4>

<livewire:eyepiece.create :eyepiece="$eyepiece" />

@endsection

@push('scripts')

<script>
    $("#picture").fileinput(
        {
            theme: "fas",
            allowedFileTypes: ['image'],    // allow only images
            'showUpload': false,
            maxFileSize: 10000,
            @if ($eyepiece->id != null && $eyepiece->getFirstMedia('eyepiece') != null)
            initialPreview: [
                '<img class="file-preview-image kv-preview-data" src="/eyepiece/{{ $eyepiece->id }}/getImage">'
            ],
            initialPreviewConfig: [
                {caption: "{{ $eyepiece->getFirstMedia('eyepiece')->file_name }}", size: {{ $eyepiece->getFirstMedia('eyepiece')->size }}, url: "/eyepiece/{{ $eyepiece->id }}/deleteImage", key: 1},
            ],
            @endif
        }
    );
</script>
@endpush
